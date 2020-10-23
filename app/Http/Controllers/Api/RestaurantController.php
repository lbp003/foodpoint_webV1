<?php

/**
 * RestaurantController Controller
 *
 * @package    GoferEats
 * @subpackage Controller
 * @category   RestaurantController
 * @author     Trioangle Product Team
 * @version    1.2
 * @link       http://trioangle.com
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Currency;
use App\Models\DriverRequest;
use App\Models\IssueType;
use App\Models\MenuItem;
use App\Models\MenuItemModifierItem;
use App\Models\Order;
use App\Models\OrderCancelReason;
use App\Models\Payout;
use App\Models\Restaurant;
use App\Models\Review;
use App\Models\ReviewIssue;
use Illuminate\Http\Request;
use Validator;
use JWTAuth;
use DateTime;
use App;
class RestaurantController extends Controller {
	/**
	 * Construct function
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * To get the orders for the restaurant dashboard
	 *
	 * @return array Response for the restaurant dashboard
	 */
	public function orders()
	{
		$default_currency_code=DEFAULT_CURRENCY;
		$default_currency_symbol=Currency::where('code',DEFAULT_CURRENCY)->first()->symbol;		
		$default_currency_symbol=html_entity_decode($default_currency_symbol);
		$restaurant = Restaurant::authUser()->first();
		
		$pending_accepted_orders = Order::where('restaurant_id', $restaurant->id)->history(['pending', 'accepted'])->get();
		$pending_accepted_orders = $pending_accepted_orders->map(
			function ($order, $key) {
				$changeDate;
				$date = $this->dateFormat($order->updated_at, $order->schedule_time, $order->schedule_status);

				$date1 = date($order->schedule_time);
				$schedule_time_obj = \Carbon\Carbon::createFromTimestamp(strtotime($date1));
				
				$pre_time = explode(':',$order->est_preparation_time);
				$schedule_time_obj->subHours($pre_time[0])->subMinutes($pre_time[1])->subSeconds($pre_time[2]);
				
				// dd($schedule_time_obj);
				$time = \Carbon\Carbon::parse($schedule_time_obj)->format('h:i a');
				// dd($preparation_time);

					
					$changeDate = $date['date'];
				
				//form date and time
				$getTime = $time;
				
				return [

					'id' => $order->id,
					'delivery_mode' => $order->delivery_mode,
					'delivery_mode_text' => $order->delivery_mode_text,
					'order_item_count' => $order->order_item->count(),
					'user_name' => $order->user->name,
					'user_image' => $order->user->user_image_url,
					'driver_image' => isset($order->driver) ? $order->driver->user->user_image_url : '',
					'remaining_seconds' => $order->remaining_seconds,
					'total_seconds' => $order->total_seconds,
					'status_text' => $order->status_text,
					'order_delivery_status' => $order->order_delivery ? $order->order_delivery->status : '-1',
					'order_type' => $order->schedule_status,
					'order_date' => $changeDate,
					'order_time' => $getTime,
				];
			}
		)->reject(
			function ($order) {
				return false;
				return $order['remaining_seconds'] <= 0;
			}
		)->groupBy('status_text');

		if (isset($pending_accepted_orders['accepted'])) {
			$accepted_orders = $pending_accepted_orders['accepted']->where('order_type', 0)->values();
		}

		if (isset($pending_accepted_orders['accepted'])) {
			$scheduled_orders = $pending_accepted_orders['accepted']->where('order_type', 1)->values();
		}

		$delivery_completed_orders = Order::where('restaurant_id', $restaurant->id)->history(['delivery', 'completed'])->orderBy('id', 'desc')->get();
		$delivery_completed_orders = $delivery_completed_orders->map(
			function ($order, $key) {

				return [
					'id' => $order->id,
					'order_item_count' => $order->order_item->count(),
					'delivery_mode' => $order->delivery_mode,
					'delivery_mode_text' => $order->delivery_mode_text,
					'user_name' => $order->user->name,
					'user_image' => $order->user->user_image_url,
					'remaining_seconds' => $order->remaining_seconds,
					'total_seconds' => $order->total_seconds,
					'status_text' => $order->status_text,
					'order_delivery_status' => $order->order_delivery ? $order->order_delivery->status : '-1',
					'restaurant_to_driver_thumbs' => $order->restaurant_is_thumbs,
					'driver_image' => isset($order->driver) ? $order->driver->user->user_image_url : '',

				];
			}
		)->groupBy('status_text');

		$issue_restaurant_delivery = IssueType::TypeText('restaurant_delivery')->get();

		return response()->json(
			[
				'status_message' => "Success",
				'status_code' => '1',
				'restaurant_name' => $restaurant->name,
				'status' => $restaurant->status,
				'pending_orders' => isset($pending_accepted_orders['pending']) ? $pending_accepted_orders['pending'] : [],
				'current_orders' => isset($accepted_orders) ? $accepted_orders : [],
				'delivery_orders' => isset($delivery_completed_orders['delivery']) ? $delivery_completed_orders['delivery'] : [],
				'completed_orders' => isset($delivery_completed_orders['completed']) ? $delivery_completed_orders['completed'] : [],
				'restaurant_delivery' => $issue_restaurant_delivery ? $issue_restaurant_delivery : [],
				'schedule_order' => isset($scheduled_orders) ? $scheduled_orders : [],
				'default_currency_code'=>$default_currency_code,
				'default_currency_symbol'=>$default_currency_symbol,
			]
		);
	}

	/**
	 * API for getting order history
	 *
	 * @return Response Json response with status
	 */
	public function order_history() {

		$request = request();

		// $rules = [
		// 	'delivery_mode' => 'required|in:1,2',			
		// ];

		// $validator = Validator::make($request->all(), $rules);

		// if ($validator->fails()) {
		// 	return response()->json(
		// 		[
		// 			'status_code' => '0',
		// 			'status_message' => $validator->messages()->first(),					
		// 		]
		// 	);
		// }

		$restaurant = Restaurant::authUser()->first();

		$cancelled_completed_orders = Order::where('restaurant_id', $restaurant->id)
			// ->where('delivery_mode',$request->delivery_mode)
			->history(['cancelled', 'completed'])
			->orderBy('id', 'Desc')
			->get();

		$cancelled_completed_orders = $cancelled_completed_orders->map(

			function ($order, $key) {

				if ($order->status_text == "completed") {
					$order_time = $order->completed_at->format('Y-m-d h:i').trans('api_messages.monthandtime.'.$order->completed_at->format('a'));

				} else {
					$order_time = $order->updated_at->format('Y-m-d h:i').trans('api_messages.monthandtime.'.$order->updated_at->format('a'));
				}

				return [

					'id' => $order->id,
					'delivery_mode' => $order->delivery_mode ?? "",
					'delivery_mode_text'=> $order->delivery_mode_text ?? "",
					'order_item_count' => $order->order_item->count(),
					'user_name' => $order->user->name,
					'user_image' => $order->user->user_image_url,
					'status_text' => $order->status_text,
					'order_item_name' => count($order->order_item) > 0 ? $order->order_item->first()->modifier_name : '',
					'order_time' => $order_time,
					'order_price' => $order->subtotal,

				];
			}
		) /*->groupBy('status_text')*/;

		return response()->json(
			[
				'status_message' => "Order history listed successfully",
				'status_code' => '1',
				'order_history' => $cancelled_completed_orders,
				/*'cancelled_orders'  => isset($cancelled_completed_orders['cancelled']) ? $cancelled_completed_orders['cancelled'] : [],
                'completed_orders'  => isset($cancelled_completed_orders['completed']) ? $cancelled_completed_orders['completed'] : [],*/
			]
		);
	}

	/**
	 * API for accepting order
	 *
	 * @return Response Json response with status
	 */
	public function accept_order(Request $request)
	{
		$restaurant = Restaurant::authUser()->first();
		$order = new Order;

		$order = Order::where('id', $request->order_id)->first();
		$order->accept_order();

		$accepted_orders = Order::where('restaurant_id', $restaurant->id)->history(['accepted'])->get();
		$accepted_orders = $accepted_orders->map(
			function ($order, $key) {
				return [
					'id' => $order->id,
					'order_item_count' => $order->order_item->count(),
					'user_name' => $order->user->name,
					'user_image' => $order->user->user_image_url,
					'remaining_seconds' => $order->remaining_seconds,
					'total_seconds' => $order->total_seconds,
					'status_text' => $order->status_text,
					'order_type' => $order->schedule_status,
				];
			}
		);
		return response()->json([
			'status_code' => "1",
			'status_message' => 'Order accepted successfully',
			'accepted_orders' => $accepted_orders,
		]);
	}

	/**
	 * API for getting order details
	 *
	 * @return Response Json response with status
	 */
	public function order_details(Request $request)
	{
		$restaurant = Restaurant::authUser()->first();

		$rules = [
			'order_id' => 'required|exists:order,id,restaurant_id,' . $restaurant->id,
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails()) {
			return response()->json(
				[
					'status_code' => '0',
					'status_message' => $validator->messages()->first(),
				]
			);
		}

		$order = Order::where('id', $request->order_id)->first();

		$request = DriverRequest::where('order_id', $order->id)->get();
		$is_request = $request->count() > 0 ? 1 : 0;

		$date = $this->dateFormat($order->updated_at, $order->schedule_time, $order->schedule_status);

		$date1 = date($order->schedule_time);
		$schedule_time_obj = \Carbon\Carbon::createFromTimestamp(strtotime($date1));
		
		$pre_time = explode(':',$order->est_preparation_time);
		$schedule_time_obj->subHours($pre_time[0])->subMinutes($pre_time[1])->subSeconds($pre_time[2]);
		
		$time = \Carbon\Carbon::parse($schedule_time_obj)->format('h:i a');

		$changeDate = $date['date'];
		
		//form date and time
		$getTime = $time;
		if($order->delivery_at)
		$delivery_at=new DateTime($order->delivery_at);
		else
		$delivery_at='';
		if($order->accepted_at)
		$accepted_at=new DateTime($order->accepted_at);
		else
		$accepted_at='';
		if($order->completed_at)
		$completed_at=new DateTime($order->completed_at);
		else
		$completed_at='';
	
		$dateTrans = $date['date'];
		$order_details = [
			'order_id' 		=> $order->id,
			'status' 		=> $order->status_text,
			'order_notes' 	=> $order->notes,
			'delivery_mode' => $order->delivery_mode ?? "",
			'delivery_mode_text'=> $order->delivery_mode_text ?? "",
			'order_delivery_status' => $order->order_delivery ? $order->order_delivery->status : '-1',
			'restaurant_to_driver_thumbs' => $order->restaurant_is_thumbs,
			'order_type' 	=> $order->schedule_status,
			'order_date' 	=> $dateTrans,
			'order_time' 	=> $getTime,
			'status_times' 	=> [
				'accepted' 	=> $order->accepted_at ? $accepted_at->format('h:i').trans('api_messages.monthandtime.'.$accepted_at->format('a')) : '',
				'delivery' 	=> $order->delivery_at ? $delivery_at->format('h:i') .trans('api_messages.monthandtime.'.$delivery_at->format('a')) : '',
				'completed' => $order->completed_at ? $completed_at->format('h:i') .trans('api_messages.monthandtime.'.$completed_at->format('a')) : '',
			],
			'item_details' => $order->order_item->map(function ($order_item) {
					$result = array();
					 $order_item_modifier_item = $order_item->order_item_modifier->map(function ($menu) { 
						return $menu->order_item_modifier_item->map(function ($item) { 
							return[
									'id'	=> $item->id,			
									'count' => $item->count,
									'price' => (string) number_format($item->price * $item->count,'2'),
									'name'  => $item->modifier_item_name,
							];
						});
					})->toArray();
					 foreach ($order_item_modifier_item as $key => $value) {
				        if (is_array($value)) {
				            foreach($value as $keys =>$val) {
				           		$result[] = $val;
				            }
				        }
			    	}
					return [
						'name' => $order_item->menu_name ?? '',
						'notes' => (string) $order_item->notes,
						'price' => $order_item->total_amount,
						'offer_price' => $order_item->offer_price,
						'quantity' => $order_item->quantity,
						'modifiers' => @$result ? $result : [],
					];
				}
			)->toArray(),
			'subtotal' 	=> $order->subtotal,
			'tax' 		=> $order->tax,
			'restaurant_fee' => $order->restaurant_commision_fee,
			'total' => (string) numberFormat($order->restaurant_total - $order->restaurant_commision_fee),
			'user_image' => $order->user->user_image_url,
			'user_name' => $order->user->name,
			'user_phone' => $order->user->mobile_number,
			'user_address' => $order->user->user_address->address1 ?? '',

			'support_phone' => site_setting('site_support_phone'),
			'driver_name' => $order->driver ? $order->driver->user->name : "",
			'vechile_name' => $order->driver ? $order->driver->vehicle_name : "",
			'vechile_number' => $order->driver ? $order->driver->vehicle_number : "",
			'driver_number' => $order->driver ? $order->driver->user->mobile_number : "",
			'driver_image' => $order->driver ? $order->driver->user->user_image_url : "",
			'pickup_location' => $order->order_delivery ? $order->order_delivery->pickup_location : "",
			'pickup_latitude' => $order->order_delivery ? $order->order_delivery->pickup_latitude : "",
			'pickup_longitude' => $order->order_delivery ? $order->order_delivery->pickup_longitude : "",
			'drop_location' => $order->order_delivery ? $order->order_delivery->drop_location : "",
			'drop_latitude' => $order->order_delivery ? $order->order_delivery->drop_latitude : "",
			'drop_longitude' => $order->order_delivery ? $order->order_delivery->drop_longitude : "",
			'driver_latitude' => $order->driver ? $order->driver->latitude : "",
			'driver_longitude' => $order->driver ? $order->driver->longitude : "",
			'is_request' => $is_request,
			'res_penality' => $order->restaurant_penality,
			'applied_penality' => $order->res_applied_penality,
		];

		if ($order->status == $order->statusArray['cancelled']) {
			$order_details['completed_date_time'] = $order->cancelled_at ? $order->cancelled_at->format('d-m-Y h:i').trans('api_messages.monthandtime.'.$order->cancelled_at->format('a')) : '';
		}
		else if ($order->status == $order->statusArray['declined']) {
			$order_details['completed_date_time'] = $order->declined_at ? $order->declined_at->format('d-m-Y h:i').trans('api_messages.monthandtime.'.$order->declined_at->format('a')) : '';
		}
		else {
			$order_details['completed_date_time'] = $order->completed_at ? $order->completed_at->format('d-m-Y h:i').trans('api_messages.monthandtime.'.$order->completed_at->format('a')) : '';
		}

		return response()->json([
			'status_message' => 'Order details listed successfully',
			'status_code' => "1",
			'order_details' => $order_details,
		]);
	}

	/**
	 * API for delivery status
	 *
	 * @return Response Json response with status
	 */
	public function food_ready(Request $request)
	{
		$restaurant = Restaurant::authUser()->first();
		$order = new Order;

		$rules = [
			'order_id' => 'required|exists:order,id,restaurant_id,' . $restaurant->id . ',status,' . $order->statusArray['accepted'],
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails()) {
			return response()->json([
				'status_code' => '0',
				'status_message' => $validator->messages()->first(),
			]);
		}

		$order = Order::where('id', $request->order_id)->first();

		// $order->deliver_order();

		// Push Notification for Eater
		$order->status = $order->statusArray['delivery'];
		$order->delivery_at = date('Y-m-d H:i:s');
		$order->save();


		// Update Delivery Status
		$order->order_delivery->confirmed();

		$user = $order->user;
		$push_notification_title = trans('api_messages.orders.your_food_preparation_done') . $request->order_id;
		$push_notification_data = [
			'type' => 'order_delivery_started',
			'order_id' => $request->order_id,
		];

		push_notification($user->device_type, $push_notification_title, $push_notification_data, $user->type, $user->device_id);

		return response()->json([
			'status_code' => '1',
			'status_message' => 'Order out for delivery',
		]);
	}

	/**
	 * API for getting cancel reasons
	 *
	 * @return Response Json response with status
	 */
	public function get_cancel_reason(Request $request)
	{
		$restaurant = Restaurant::authUser()->first();
		$order = new Order;

		$rules = [
			'type' => 'required',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails()) {
			return response()->json([
				'status_code' => '0',
				'status_message' => $validator->messages()->first(),
			]);
		}
		//$this->view_data['cancel_reason'] = OrderCancelReason::where('status', 1)->get();
		$cancel_reason = OrderCancelReason::type($request->type)->status()->get()
		->map(
		
			function ($reason) {
				return [
					'id' => $reason->id,
					'reason' => $reason->add_name,
				];
			}
		);

		return response()->json([
			'status_code' => '1',
			'status_message' => 'Order cancel reasons listed successfully',
			'cancel_reason' => $cancel_reason,
		]);
	}

	/**
	 * API for cancel order
	 *
	 * @return Response Json response with status
	 */
	public function cancel_order(Request $request)
	{
		$restaurant = Restaurant::authUser()->first();

		$rules = [
			'order_id' => 'required|exists:order,id,restaurant_id,' . $restaurant->id,
			'cancel_reason' => 'required|exists:order_cancel_reason,id',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails()) {
			return response()->json([
				'status_code' => '0',
				'status_message' => $validator->messages()->first(),
			]);
		}

		$order = Order::where('id', $request->order_id)->first();
		$order->cancel_order("restaurant", $request->cancel_reason, $request->cancel_message);

		$PaymentController = resolve('App\Http\Controllers\Api\PaymentController');
		$PaymentController->refund($request, 'Cancelled',$order->user_id,'restaurant');

		$payout = Payout::where('order_id', $order->id)->where('user_id', $restaurant->user_id)->first();

		if ($payout) {
			$payout->delete();
		}

		return response()->json([
			'status_code' => '1',
			'status_message' => 'Order has been cancelled successfully',
		]);
	}

	/**
	 * API for delay order
	 *
	 * @return Response Json response with status
	 */
	public function delay_order(Request $request)
	{
		$restaurant = Restaurant::authUser()->first();
		$order = new Order;

		$rules = [
			'order_id' => 'required|exists:order,id,restaurant_id,' . $restaurant->id/* . ',status,' . $order->statusArray['accepted']*/,
			'delay_min' => 'required|integer',
			// 'delay_message' => 'required'
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails()) {
			return response()->json([
				'status_code' => '0',
				'status_message' => $validator->messages()->first(),
			]);
		}

		$delay_seconds = $request->delay_min * 60;

		$order = Order::where('id', $request->order_id)->first();
		$order->delay_order($delay_seconds, $request->delay_message);

		return response()->json([
			'status_code' => '1',
			'status_message' => 'Order has been delayed successfully',			
		]);
	}

	/**
	 * To get the menu details in array
	 *
	 * @return Response Json response
	 */
	public function menu(Request $request)
	{
		$restaurant = Restaurant::authUser()->menuRelations()->first();

		$restaurant_menu = $restaurant->restaurant_menu->map(
			function ($menu) {
				return [
					'id' => $menu->id,
					'name' => $menu->name,
					'menu_category' => $menu->menu_category->map(
						function ($category) {
							return [
								'id' => $category->id,
								'name' => $category->name,
								'menu_item' => $category->menu_item->map(
									function ($item) {
										return [
											'id' => $item->id,
											'name' => $item->name,
											'price' => $item->price,
											'image' => $item->menu_item_image,
											'description' => $item->description,
											'is_visible' => $item->is_visible,
											'modifier' => $item->menu_item_modifier->map(
												function ($modifier) {
													return [
														'id' => $modifier->id,
														'name' => $modifier->name,
														'modifier_item' => $modifier->menu_item_modifier_item->map(
															function ($modifier_item) {
																return [
																	'id' => $modifier_item->id,
																	'name' => $modifier_item->name,
																	'price' => $modifier_item->price,
																	'is_visible' => $modifier_item->is_visible,
																];
															}
														)->toArray(),
													];
												}
											)->toArray(),
										];
									}
								)->toArray(),
							];
						}
					)->toArray(),
				];
			}
		)->toArray();

		return response()->json([
			'status_message' => 'Restaurant menu details listed successfully',
			'status_code' => '1',
			'restaurant_menu' => $restaurant_menu,
		]);
	}

	/**
	 * Toggle the visible option of the menu item, menu modifier item
	 *
	 * @return Response Json response
	 */
	public function toggle_visible(Request $request)
	{
		$restaurant = Restaurant::authUser()->menuRelations()->first();

		$rules = [
			'type' => 'required|in:menu_item,modifier_item',
		];

		if ($request->type == "menu_item") {
			$rules['id'] = "required|exists:menu_item,id";
		}
		elseif ($request->type == "modifier_item") {
			$rules['id'] = "required|exists:menu_item_modifier_item,id";
		}

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails()) {
			return response()->json([
				'status_message' => $validator->messages()->first(),
				'status_code' => '0',
			]);
		}

		$data = null;
		if ($request->type == "menu_item") {
			$data = MenuItem::where('id', $request->id)->restaurant($restaurant->id)->first();
		}
		elseif ($request->type == "modifier_item") {
			$data = MenuItemModifierItem::where('id', $request->id)->restaurant($restaurant->id)->first();
		}

		if (!$data) {
			return response()->json([
				'status_message' => "You are not autheticated to change this data",
				'status_code' => '0',
			]);
		}

		$data->is_visible = $data->is_visible == 0 ? 1 : 0;
		$data->save();

		return response()->json([
			'status_message' => "Visible value successfully changed",
			'status_code' => '1',
		]);
	}

	public function restaurant_availabilty()
	{
		$request = request();
		$restaurant = Restaurant::authUser()->menuRelations()->first();

		$rules = [
			'status' => 'required|in:0,1',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails()) {
			return response()->json(
				[
					'status_message' => $validator->messages()->first(),
					'status_code' => '0',
				]
			);
		}

		$restaurant->status = $request->status;
		$restaurant->save();

		return response()->json(
			[
				'status_message' => "Restaurant status updated successfully",
				'status_code' => '1',
			]
		);
	}

	/**
	 * API for Review Restaurant to driver
	 *
	 * @return Response Json response with status
	 */

	public function review_restaurant_to_driver(Request $request)
	{
		$request = request();
		$restaurant = Restaurant::authUser()->first();
		$issue_type = new IssueType;

		$order = Order::where('id', $request->order_id)->first();
		$order_delivery_id = 0;
		if ($order && $order->order_delivery) {
			$order_delivery_id = $order->order_delivery->id;
		}

		$request_data = $request->all();
		$request_data['issues_array'] = explode(',', $request->issues);

		$rules = [
			'order_id' => 'required|exists:order,id',
			'is_thumbs' => 'required|in:0,1',
			// 'issues' => 'required_if:is_thumbs,0',
			// 'issues_array.*' => 'required_if:is_thumbs,0|exists:issue_type,id,type_id,'.$issue_type->typeArray['restaurant_delivery'],
		];

		// $messages = [
		//     'issues_array.*.exists' => 'The selected issue type :input is not belongs to the current review type',
		// ];

		$validator = Validator::make($request_data, $rules/*, $messages*/);

		if ($validator->fails()) {
			return response()->json(
				[
					'status_code' => '0',
					'status_message' => $validator->messages()->first(),
				]
			);
		}

		$review = new Review;
		$review->order_id = $order->id;
		$review->type = $review->typeArray['restaurant_delivery'];
		$review->reviewer_id = $order->driver_id;
		$review->reviewee_id = $order->restaurant_id;
		$review->is_thumbs = $request->is_thumbs;
		$review->comments = $request->comments ?: "";
		$review->save();

		if ($request->issues) {
			$issues = explode(',', $request->issues);
			if ($request->is_thumbs == 0 && count($issues)) {
				foreach ($issues as $issue_id) {
					$review_issue = new ReviewIssue;
					$review_issue->review_id = $review->id;
					$review_issue->issue_id = $issue_id;
					$review_issue->save();
				}
			}

		}

		return response()->json(
			[
				'status_message' => 'Order delivery to driver successfully',
				'status_code' => '1',
				'restaurant_to_driver_thumbs' => $review->is_thumbs,
			]
		);

	}

	/**
	 * API for Dateformat converted
	 *
	 * @return Response Json response with status
	 */

	public function dateFormat($updated_at, $delivery_time, $type)
	{

		$date = date('Y-m-d', strtotime($updated_at));
		;
		if ($type == 0) {
			$common = date('h:i', strtotime($delivery_time)).trans('api_messages.monthandtime.'.date('a', strtotime($delivery_time)));
			
			if ($date == date('Y-m-d')) {
				$date = 'Today' . ' ' . date('M d h:i', strtotime($updated_at)).trans('api_messages.monthandtime.'.date('a', strtotime($updated_at)));
				$day = trans('api_messages.orders.Today');
				$time = $common;
			}
			else if ($date == date('Y-m-d', strtotime("+1 days"))) {
				$date = 'Tomorrow' . ' ' . date('M d h:i', strtotime($updated_at)).trans('api_messages.monthandtime.'.date('a', strtotime($updated_at)));
				$day = trans('api_messages.orders.Today');
				$time = $common;
			}
			else {
				$date = date('l M d h:i', strtotime($updated_at)).trans('api_messages.monthandtime.'.date('a', strtotime($updated_at)));
				$day = trans('api_messages.weekday.'.date('l', strtotime($updated_at))).' '.trans('api_messages.monthandtime.'.date('M', strtotime($updated_at))).' '.date('d', strtotime($updated_at));
				$time = date('h:i', strtotime($updated_at)).trans('api_messages.monthandtime.'.date('a', strtotime($updated_at)));
			}
		}
		else {

			$schedule_time = date('Y-m-d', strtotime($delivery_time));
			$time_Stamp = strtotime($delivery_time) + 1.20;
			$del_time = date('h:i a', $time_Stamp);
			$common = date('h:i', strtotime($delivery_time)).trans('api_messages.monthandtime.'.date('a', strtotime($delivery_time)));

			if ($schedule_time == date('Y-m-d')) {
				$date = 'Today' . ' ' . $common;
				$day = trans('api_messages.orders.Today');
				$time = $common;
			}
			else if ($schedule_time == date('Y-m-d', strtotime("+1 days"))) {
				$date = 'Tomorrow' . ' ' . $common;
				$day = trans('api_messages.orders.Tomorrow');
				$time = $common;
			}
			else {
				$date = date('l M d h:i', strtotime($delivery_time)).trans('api_messages.monthandtime.'.date('a', strtotime($delivery_time)));
				
				$day = trans('api_messages.weekday.'.date('l', strtotime($delivery_time))).' '.trans('api_messages.monthandtime.'.date('M', strtotime($delivery_time))).' '.date('d', strtotime($delivery_time));
				$time = date('h:i', strtotime($delivery_time)).trans('api_messages.monthandtime.'.date('a', strtotime($delivery_time)));
			}

		}
		return ['date' => $day, 'time' => $time];
	}

	/**
	 * API for Review Restaurant to driver
	 *
	 * @return Response Json response with status
	 */
	public function remainScheduleOrder()
	{
		$order = Order::where('status', '3')->where('schedule_status', '1')->get();

		if ($order) {

			foreach ($order as $value) {

				date_default_timezone_set($value->restaurant->user_address->default_timezone);

				$data['prepartation'] = $value->est_preparation_time;
				$data['travel'] = $value->est_travel_time;

				$secs = strtotime($data['travel']) - strtotime("00:00:00");
				$data['total_time'] = date("H:i:s", strtotime($data['prepartation']) + $secs);

				$secs = strtotime($data['total_time']) - strtotime("00:00:00");
				$data['prepare'] = date("Y-m-d H:i", strtotime($value->schedule_time) - $secs);

				$changeDate;
				$date = date("H:i",strtotime($value->schedule_time));

				$date1 = date($value->schedule_time);
				$schedule_time_obj = \Carbon\Carbon::createFromTimestamp(strtotime($date1));
				
				$pre_time = explode(':',$value->est_preparation_time);
				$schedule_time_obj->subHours($pre_time[0])->subMinutes($pre_time[1])->subSeconds($pre_time[2]);
				
				$time = \Carbon\Carbon::parse($schedule_time_obj)->format('Y-m-d H:i');
				
				//form date and time
				if ($time <= date('Y-m-d H:i',time() + 300)) {
					$order = Order::find($value->id);
					$order->schedule_status = 0;
					$order->accepted_at = date('Y-m-d H:i:s');
					$secs = strtotime($data['total_time']) - strtotime("00:00:00");
					$est_time = date("H:i:s", time() + $secs);
					$order->est_delivery_time = $est_time;
					$order->save();

					$push_notification_title = "Schedule order timing start";

					$restaurant_user = $order->restaurant->user;

					$push_notification_data = [
						'type' => 'schedule_order',
						'order_id' => $order->id,
						'order_data' => [
							'id' => $order->id,
							'order_item_count' => $order->order_item->count(),
							'user_name' => $order->user->name,
							'user_image' => $order->user->user_image_url,
							'remaining_seconds' => $order->remaining_seconds,
							'total_seconds' => $order->total_seconds,
							'status_text' => $order->status_text,
						],
					];

					push_notification($restaurant_user->device_type, $push_notification_title, $push_notification_data, 1, $restaurant_user->device_id, true);

				}

			}

		}

	}

	/**
	 * API for Request before seven minutes to Driver
	 *
	 * @return Response Json response with status
	 */

	public function beforeSevenMin()
	{
		$order = Order::where('status', '3')->where('schedule_status', '0')->get();

		if ($order) {
			foreach ($order as $value) {
				date_default_timezone_set($value->restaurant->user_address->default_timezone);

				$data['travel'] = $value->est_travel_time;
				$secs = strtotime($data['travel']) - strtotime("00:00:00");

				$est_delivery_time = strtotime($value->est_delivery_time)-(420+$secs);
				$request_time = date('H:i',$est_delivery_time);
				$time = date('H:i', time());
				if (date('Y-m-d', strtotime($value->schedule_time)) == date('Y-m-d', time()) || $value->schedule_time==null) {
					if (strtotime($request_time) == strtotime($time)) {
						$order = Order::where('id', $value->id)->first();
						$order->deliver_order();
					}
				}
			}
		}

	}



	/**
	 * Restaurant to complete the order delivery
	 *
	 * @return Response Json response
	 */
	public function complete_order_delivery() {

		$request = request();
		$restaurant = Restaurant::authUser()->first();
		$order = new Order;

		// . ',delivery_mode,' . $order->modeArray['pickup']
		$rules = [
			'order_id' => 'required|exists:order,id,restaurant_id,' . $restaurant->id . ',status,' . $order->statusArray['delivery']
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails()) {
			return response()->json(['status_code' => '0','status_message' => $validator->messages()->first()]);
		}

		$order = Order::where('id', $request->order_id)->first();
		
		$order->order_delivery->completed();

		// $order->delivery_completed();

		return response()->json(
			[
				'status_message' => trans('api_messages.order.order_completed'),
				'status_code' => '1',
			]
		);
	}
}