<?php

/**
 * DriverController Controller
 *
 * @package    GoferEats
 * @subpackage Controller
 * @category   DriverController
 * @author     Trioangle Product Team
 * @version    1.2
 * @link       http://trioangle.com
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Driver;
use App\Models\DriverOweAmount;
use App\Models\DriverRequest;
use App\Models\IssueType;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\Payment;
use App\Models\Payout;
use App\Models\PayoutPreference;
use App\Models\Review;
use App\Models\ReviewIssue;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\UserPaymentMethod;
use App\Models\VehicleType;
use App\Traits\FileProcessing;
use DateTime;
use DB;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Storage;
use stripe;
use Validator;

class DriverController extends Controller
{
	use FileProcessing;

	/**
	 * To update the vehicle details
	 *
	 * @return Response Json response
	 */
	public function vehicle_details()
	{
		$request = request();
		$user = User::auth()->first();
		$driver = Driver::authUser()->first();
		$vehicle_type = new VehicleType;

		$rules = [
			'vehicle_type' => 'required|exists:vehicle_type,id,status,' . $vehicle_type->statusArray['active'],
			'vehicle_name' => 'required',
			'vehicle_number' => 'required',
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
		$user->status = 3;
		$user->save();
		if (!$driver) {
			$driver = new Driver;
			$driver->user_id = $user->id;
		}

		$driver->vehicle_type = $request->vehicle_type;
		$driver->vehicle_name = $request->vehicle_name;
		$driver->vehicle_number = $request->vehicle_number;
		$driver->save();

		return response()->json(
			[
				'status_message' => "Vehicle details updated successfully",
				'status_code' => '1',
			]
		);
	}

	/**
	 * To upload the documents
	 *
	 * @return Response Json response
	 */
	public function document_upload() {
		$request = request();
		$driver = Driver::authUser()->first();

		$rules = [
			'type' => 'required|in:licence_front,licence_back,registeration_certificate,insurance,motor_certiticate',
			'document' => 'required',
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

		$file = $request->file('document');
		$file_path = $this->fileUpload($file, 'public/images/driver');
		$this->fileSave('driver_' . $request->type, $driver->id, $file_path['file_name'], '1');
		$original_path = url(Storage::url($file_path['path']));

		if ($driver->documents->count() == 5) {
			if(env("APP_ENV")=='live'){
				$driver->user->status  = 1;
			}else{
				$driver->user->status = $driver->user->statusArray['waiting for approval'];
			}
			$driver->user->save();
		}

		return response()->json(
			[
				'status_message' => 'Document uploaded successfully',
				'status_code' => '1',
				'document_url' => $original_path,
			]
		);
	}

	/**
	 * To check the driver status
	 *
	 * @return Response Json response
	 */
	public function check_status() {

		$driver = Driver::authUser()->first();

		if ($driver->user->status_text == 'pending' || $driver->user->status_text == 'waiting for approval') {
			return response()->json(
				[
					'status_message' => trans('api_messages.driver.waiting_for_admin'),
					'status_code' => '0',
					'driver_status' => $driver->user->status_text,
				]
			);

		} else if ($driver->user->status_text == 'inactive') {

			return response()->json(
				[
					'status_message' => trans('api_messages.driver.inactive_contact_admin'),
					'status_code' => '0',
					'driver_status' => $driver->user->status_text,
				]
			);

		} else {

			return response()->json(
				[
					'status_message' => trans('api_messages.driver.user_status_sent'),
					'status_code' => '1',
					'driver_status' => $driver->user->status_text,
				]
			);
		}
	}

	/**
	 * To update the driver locaiton
	 *
	 * @return Response Json response
	 */
	public function update_driver_location()
	{
		$default_currency_code=DEFAULT_CURRENCY;
		$default_currency_symbol=Currency::where('code',DEFAULT_CURRENCY)->first()->symbol;	
		$default_currency_symbol=html_entity_decode($default_currency_symbol);
		$request = request();
		$driver = Driver::authUser()->first();

		$rules = [
			'latitude' => 'required',
			'longitude' => 'required',
			'status' => 'required|in:' . implode(',', array_values($driver->statusArray)),
		];

		$messages = array(
            'required'                => ':attribute '.trans('api_messages.register.field_is_required').'', 
            
       	);

		$attributes = array(
			'latitude' => trans('api_messages.driver.latitude'),
			'longitude' => trans('api_messages.driver.longitude'),
			'status' => trans('api_messages.driver.status'),
		
		);

		$validator = Validator::make($request->all(), $rules,$messages, $attributes);

		if ($validator->fails()) {
			return response()->json([
				'status_code' => '0',
				'status_message' => $validator->messages()->first(),
				'default_currency_code'=>$default_currency_code,
				'default_currency_symbol'=>$default_currency_symbol,
			]);
		}

		$driver->latitude = $request->latitude;
		$driver->longitude = $request->longitude;
		if($driver->timezone == '') {
			$timezone = DB::Table('timezone')->where('value',$request->timezone)->first();
			$driver->country_code = $timezone->name ?? "IN";
		}

		$driver->status = $request->status;

		if ($request->status == 2) {
			$distance = OrderDelivery::where('order_id', $request->order_id)->first();
			$pre_distance = 0;

			if (isset($distance->drop_distance)) {
				$pre_distance = $distance->drop_distance;
			}

			$distance->drop_distance = (float) $pre_distance + $request->total_km;
			$distance->save();
		}
		else {
			$timezone = DB::Table('timezone')->where('value',$request->timezone)->first();
			$driver->country_code = $timezone->name ?? "IN";
		}

		$driver->save();

		return response()->json([
			'status_code' => '1',
			'status_message' => trans('api_messages.driver.driver_location_updated'),
			'default_currency_code'=>$default_currency_code,
			'default_currency_symbol'=>$default_currency_symbol,
		]);
	}

	/**
	 * To get the driver profile details
	 *
	 * @return Response Json response
	 */
	public function get_driver_profile() {
		$request = request();
		$driver = Driver::authUser()->first();

		$user = collect($driver->user)->except(['user_image', 'eater_image']);

		$user_address = collect($driver->user_address)->except(
			['id', 'latitude', 'longitude', 'default', 'delivery_options', 'apartment', 'delivery_note', 'type', 'static_map', 'country_code']
		);

		if (!$user_address->count()) {
			$user_address = collect(
				[
					"user_id" => $driver->user_id,
					"street" => "",
					"area" => "",
					"city" => "",
					"state" => "",
					"postal_code" => "",
					"address" => "",
					"country" => "",
				]
			);
		}
		$driver_documents = $driver->documents->flatMap(
			function ($document) {
				return [
					$document->fileTypeArray->search($document->type) => $document->image_name,
				];
			}
		);
		// dd($driver_documents);
		$driver_details = collect($driver)->only(['vehicle_name', 'vehicle_number', 'vehicle_type_name', 'vehicle_image', 'owe_amount']);
		$driver_profile = $user->merge($user_address)->merge($driver_details)->merge($driver_documents);

		return response()->json(
			[
				'status_message' =>trans('api_messages.driver.driver_profile_details'),
				'status_code' => '1',
				'driver_profile' => $driver_profile,
			]
		);
	}

	/**
	 * To update the driver profile
	 *
	 * @return Response Json response
	 */
	public function update_driver_profile() {


		$request = request();
		$driver = Driver::authUser()->first();
		$user = $driver->user;
		$rules = [
			'first_name' => 'required',
			'last_name' => 'required',
			'email' => 'required|email',
			'country_code' => 'required|exists:country,phone_code',
			'mobile_number' => 'required',
			// 'street' => 'required',
			// 'area' => 'required',
			// 'city' => 'required',
			// 'state' => 'required',
			// 'postal_code' => 'required',
			'dob' => 'required',
		];
		$messages = array(
            'required'                => ':attribute '.trans('api_messages.register.field_is_required').'', 
            
       		 );

		$niceNames = array(
				'first_name' => trans('api_messages.driver.first_name'),
				'last_name' => trans('api_messages.driver.last_name'),
				'email' => trans('api_messages.driver.email'),
				'country_code' => trans('api_messages.driver.country_code'),
				'mobile_number' => trans('api_messages.driver.mobile_number'),
				'street' => trans('api_messages.driver.street'),
				'city' => trans('api_messages.driver.city'),
				'state' => trans('api_messages.driver.state'),
				'postal_code' => trans('api_messages.driver.postal_code'),
				'dob' => trans('api_messages.register.dob'),
			);

		$validator = Validator::make($request->all(), $rules,$messages);
		$validator->setAttributeNames($niceNames);
		if ($validator->fails()) {
			return response()->json(
				[
					'status_message' => $validator->messages()->first(),
					'status_code' => '0',
				]
			);
		}

		$user->name = $request->first_name . '~' . $request->last_name;
		$user->email = $request->email;
		$user->country_code = $request->country_code;
		$user->mobile_number = $request->mobile_number;
		$user->date_of_birth =date('Y-m-d',strtotime($request->dob));
		$user->save();

		$user_address = $user->user_address;
		if (!$user_address) {
			$user_address = new UserAddress;
			$user_address->user_id = $user->id;
		}

		$user_address->street = $request->street ?? '';
		$user_address->address = $request->first_address ?? '';
		$user_address->city = $request->city ?? '';
		$user_address->state = $request->state ?? '';
		$user_address->country = $request->country ?? '';
		$user_address->postal_code = $request->postal_code ?? '';
		$user_address->default = 1;
		$user_address->save();

		$user = collect($driver->user)->except(['user_image', 'eater_image']);

		$user_address = collect($driver->user_address)->except(
			['id', 'latitude', 'longitude', 'default', 'delivery_options', 'apartment', 'delivery_note', 'type', 'static_map', 'country']
		);

		if (!$user_address->count()) {
			$user_address = collect(
				[
					"user_id" => $driver->user_id,
					"street" => "",
					"area" => "",
					"city" => "",
					"state" => "",
					"postal_code" => "",
					"address" => "",
				]
			);
		}
		$driver_documents = $driver->documents->flatMap(
			function ($document) {
				return [
					$document->fileTypeArray->search($document->type) => $document->image_name,
				];
			}
		);

		$driver_details = collect($driver)->only(['vehicle_name', 'vehicle_number', 'vehicle_type_name', 'vehicle_image']);

		$driver_profile = $user->merge($user_address)->merge($driver_details)->merge($driver_documents);
		
		return response()->json(
			[
				'status_message' => trans('api_messages.driver.driver_profile_details_updated'),
				'status_code' => '1',
				'driver_profile' => $driver_profile,
			]
		);
	}

	/**
	 * To accept the request
	 *
	 * @return Response Json response
	 */
	public function accept_request() {

		$request = request();
		$driver = Driver::authUser()->first();
		$order = new Order;

		$rules = [
			'request_id' => 'required|exists:request,id,driver_id,' . $driver->id,
			'order_id' => 'required|exists:order,id', /*,status,'.$order->statusArray['accepted']*/
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

		$order = Order::where('id', $request->order_id)->first();
		$driver_request = DriverRequest::where('id', $request->request_id)->first();

		if (($driver_request->status_text != "pending") || ($order->driver_id && $order->driver)) {
			return response()->json(
				[
					'status_message' => trans('api_messages.driver.timed_out'),
					'status_code' => '0',
				]
			);
		}

		$driver_request->status = $driver_request->statusArray['accepted'];
		$driver_request->save();

		$order->driver_accepted($driver_request);

		$update_status = Driver::find($driver->id);
		$update_status->status = 2;
		$update_status->save();

		$this->static_map($order->id);

		return response()->json(
			[
				'status_message' => trans('api_messages.driver.request_accepted_successfully'),
				'status_code' => '1',
				'order_details' => [
					'order_id' => $order->id,
					'mobile_number' => $order->user->mobile_number,
					'eater_thumb_image' => $order->user->user_image_url,
					'rating_value' => '0',
					'vehicle_type' => $driver->vehicle_type_details->name,
					'pickup_location' => $driver_request->pickup_location,
					'pickup_latitude' => $driver_request->pickup_latitude,
					'pickup_longitude' => $driver_request->pickup_longitude,
					'drop_location' => $driver_request->drop_location,
					'drop_latitude' => $driver_request->drop_latitude,
					'drop_longitude' => $driver_request->drop_longitude,
				],
			]
		);
	}

	/**
	 * To cancel the request
	 *
	 * @return Response Json response
	 */
	public function cancel_request() {
		$request = request();
		$driver = Driver::authUser()->first();
		$order = new Order;

		$rules = [
			'request_id' => 'required|exists:request,id,driver_id,' . $driver->id,
			'order_id' => 'required|exists:order,id', /*,status,'.$order->statusArray['accepted']*/
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

		$order = Order::where('id', $request->order_id)->first();
		$driver_request = DriverRequest::where('id', $request->request_id)->first();

		if (($driver_request->status_text != "pending") || ($order->driver_id && $order->driver)) {
			if ($driver_request->status_text == "cancelled") {
				return response()->json(
					[
						'status_message' => trans('api_messages.driver.timed_out'),
						'status_code' => '1',
					]
				);
			}
			return response()->json(
				[
					'status_message' => trans('api_messages.driver.timed_out'),
					'status_code' => '0',
				]
			);
		}

		$driver_request->status = $driver_request->statusArray['cancelled'];
		$driver_request->save();

		// searchRequestDriver(
		// 	$driver_request->order_id,
		// 	$driver_request->group_id,
		// 	$driver_request->pickup_latitude,
		// 	$driver_request->pickup_longitude,
		// 	$driver_request->pickup_location,
		// 	$driver_request->drop_latitude,
		// 	$driver_request->drop_longitude,
		// 	$driver_request->drop_location
		// );

		return response()->json(
			[
				'status_message' => trans('api_messages.driver.request_cancelled_successfully'),
				'status_code' => '1',
			]
		);
	}

	/**
	 * To search drivers for delivery
	 *
	 * @return Response Json response
	 */
	public function search_drivers() {
		$request = request();
		$order = new Order;

		$rules = [
			'order_id' => 'required|exists:order,id,status,' . $order->statusArray['accepted'],
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
		searchDrivers($request->order_id);
	}

	/**
	 * To search and send request to the driver
	 *
	 * @param  integer $order            Id of the order
	 * @param  integer $group_id         Group id
	 * @param  string  $pickup_latitude  Pickup latitude
	 * @param  string  $pickup_longitude Pickup longitude
	 * @param  string  $pickup_location  Pickup location
	 * @param  string  $drop_latitude    Drop latitude
	 * @param  string  $drop_longitude   Drop longitude
	 * @param  string  $drop_location    Drop location
	 * @return null                      Empty
	 */
	public function search_and_send_request_to_driver($order, $group_id, $pickup_latitude, $pickup_longitude, $pickup_location, $drop_latitude, $drop_longitude, $drop_location) {

		logger('driver_controller '.$drop_location);


		$driver_request = new DriverRequest;
		$driver_search_radius = 1000000000000000000;
		$sleep_time = 15;

		DriverRequest::status(['pending'])->groupId([$group_id])->update(['status' => $driver_request->statusArray['cancelled']]);

		if ($order->driver_id && $order->driver) {
			return response()->json(
				[
					'status_message' => trans('api_messages.driver.request_already_accepted'),
					'status_code' => '0',
				]
			)->send();
		}
		$drivers = Driver::search($drop_latitude, $drop_longitude, $driver_search_radius, $order->id)->get();

		if ($drivers->count() == 0) {
			return response()->json(
				[
					'status_message' => trans('api_messages.driver.no_drivers_found'),
					'status_code' => '0',
				]
			)->send();
		}

		$nearest_driver = $drivers->first();

		$driver_request->order_id = $order->id;
		$driver_request->driver_id = $nearest_driver->id;
		$driver_request->pickup_latitude = $pickup_latitude;
		$driver_request->pickup_longitude = $pickup_longitude;
		$driver_request->drop_latitude = $drop_latitude;
		$driver_request->pickup_location = $pickup_location;
		$driver_request->drop_longitude = $drop_longitude;
		$driver_request->drop_location = $drop_location;
		$driver_request->status = $driver_request->statusArray['pending'];
		$driver_request->save();

		$push_notification_title = "Your food preparation started for orderId #" . $order->id;
		$push_notification_data = [
			'type' => 'order_request',
			'request_id' => $driver_request->id,
			'pickup_location' => $pickup_location,
			'min_time' => '0',
			'pickup_latitude' => $pickup_latitude,
			'pickup_longitude' => $pickup_longitude,
		];

		push_notification($nearest_driver->user->device_type, $push_notification_title, $push_notification_data, $nearest_driver->user->type, $nearest_driver->device_id);

		$nexttick = time() + $sleep_time;
		$active = true;
		while ($active) {
			if (time() >= $nexttick) {
				$active = false;
				$this->search_and_send_request_to_driver($order, $group_id, $pickup_latitude, $pickup_longitude, $pickup_location, $drop_latitude, $drop_longitude, $drop_location);
			}
		}
	}

	/**
	 * To get the order details
	 *
	 * @return Response Json response
	 */
	public function driver_order_details() {

		$request = request();
		$driver = Driver::authUser()->first();
		$order = new Order;

		$rules = [
			'order_id' => 'required|exists:order,id,driver_id,' . $driver->id,
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

		$order = Order::where('id', $request->order_id)->first();

		if ($order->payment_type == 0) {

			$amount = (string) $order->total_amount;

		} else {

			$amount = '';

		}

		$order_details = [

			'order_id' => $order->id,
			'eater_name' => $order->user->name,
			'eater_mobile_number' => $order->user->mobile_number,
			'eater_thumb_image' => $order->user->user_image_url,
			'restaurant_name' => $order->restaurant->name,
			'restaurant_mobile_number' => $order->restaurant->user->mobile_number,
			'restaurant_thumb_image' => restaurant_images($order->restaurant->id, '4'),
			'vehicle_type' => $driver->vehicle_type_details->name,
			'pickup_location' => $order->order_delivery->pickup_location,
			'pickup_latitude' => $order->order_delivery->pickup_latitude,
			'pickup_longitude' => $order->order_delivery->pickup_longitude,
			'drop_location' => $order->order_delivery->drop_location,
			'drop_latitude' => $order->order_delivery->drop_latitude,
			'drop_longitude' => $order->order_delivery->drop_longitude,
			'status' => $order->order_delivery->status,
			'collect_cash' => $amount,
			'delivery_note' => isset($order->user->user_address->delivery_note) ? $order->user->user_address->delivery_note : '',
			'flat_number' => isset($order->user->user_address->apartment) ? $order->user->user_address->apartment : '',

			// 'order_items' => $order->order_item->map(
			// 	function ($order_item) {
			// 		return [
			// 			"id" => $order_item->menu_item->id,
			// 			"name" => $order_item->menu_item->name,
			// 			"quantity" => $order_item->quantity,
			// 		];
			// 	}
			// ),
				
			'order_items' => $order->order_item->map(
				function ($order_item) {
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
					 foreach ($order_item_modifier_item as $key => $value)
					    	{
						        if (is_array($value))
						        {
						            foreach($value as $keys =>$val)
						            {
						            $result[] = $val;
						            }

						        }
					    	}
					// dump($order_item->menu_item->name);
					return [
						'name' => $order_item->menu_name,
						// 'notes' => (string) $order_item->notes,
						// 'price' => $order_item->total_amount,
						// 'offer_price' => $order_item->offer_price,
						'quantity' => $order_item->quantity,
						'modifiers' => @$result ? $result : [],
					];
				}
			)->toArray(),
		];
		return response()->json(
			[
				'status_message' => trans('api_messages.driver.order_details_listed'),
				'status_code' => '1',
				'order_details' => $order_details,
			]
		);
	}

	/**
	 * API for getting dropoff data
	 *
	 * @return Response Json response with status
	 */
	public function dropoff_data() {

		$request = request();
		$driver = Driver::authUser()->first();
		$order = new Order;

		$rules = [
			'order_id' => 'required|exists:order,id',
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

		$driver_delivery = IssueType::typeText("driver_delivery")->status()->get()->map(
			function ($issue) {
				return [
					'id' => $issue->id,
					'issue' => $issue->name,
				];
			}
		);

		$dropoff_options = $order->getDropoffOptions()->map(
			function ($value, $key) {
				return [
					'id' => $key,
					'name' => $value,
				];
			}
		);

		return response()->json(
			[
				'status_message' => trans('api_messages.driver.order_cancel_reasons'),
				'status_code' => '1',
				'issues' => $driver_delivery,
				'dropoff_options' => $dropoff_options,
			]
		);
	}

	/**
	 * API for getting dropoff data
	 *
	 * @return Response Json response with status
	 */
	public function pickup_data() {
		$request = request();
		$driver = Driver::authUser()->first();
		$order = new Order;

		$rules = [
			'order_id' => 'required|exists:order,id',
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

		$driver_restaurant_issues = IssueType::typeText("driver_restaurant")->status()->get()->map(
			function ($issue) {
				return [
					'id' => $issue->id,
					'issue' => $issue->name,
				];
			}
		);

		return response()->json(
			[
				'status_message' => trans('api_messages.driver.reasons_listed_successfully'),
				'status_code' => '1',
				'issues' => $driver_restaurant_issues,
			]
		);
	}

	/**
	 * Add payout Preferences
	 *
	 * @param  Get method inputs
	 * @return Response in Json
	 */
	public function add_payout_perference(Request $request)
	{
		$driver = Driver::authUser()->first();
		$user = User::find($driver->user_id);

		$rules = array(
			'payout_method' => 'required|in:stripe,paypal,Stripe,Paypal,manual,Manual',
		);

		$payout_method = ucfirst($request->payout_method);
		if ($payout_method == 'Stripe') {
			$rules['country'] = 'required|exists:country,name';
		}

		$messages = array(
            'required' 	=> ':attribute '.__('api_messages.register.field_is_required'), 
            'exists' 	=> __('api_messages.payout.field_is_selected')
        );
		$attributes = array(
			'payout_method' => __('api_messages.payout.payout_method'),
			'country' 		=> __('api_messages.payout.country'),
		);

		$validator = Validator::make($request->all(), $rules, $messages, $attributes);

		if ($validator->fails()) {
			return response()->json([
			    'status_code' => '0',
			    'status_message' => $validator->messages()->first(),
			]);
		}

		$user_id = $user->id;
        $stripe_document 	= '';
		$account_holder_type= 'company';

		$document = $request->file('document');
		$additional_document = $request->file('additional_document');

		if($document) {
			$file_path = $this->fileUpload($document, 'public/images/payout_documents/' . $user->id);
			$this->fileSave('stripe_document', $user_id, $file_path['file_name'], '1');
			$filename = $file_path['file_name'];
			$document_path = public_path(Storage::url($file_path['path']));
		}

		if($additional_document){
			$a_file_path = $this->fileUpload($additional_document, 'public/images/payout_documents/' . $user->id);
			$this->fileSave('stripe_document', $user_id, $a_file_path['file_name'], '1');
			$a_filename = $a_file_path['file_name'];
			$a_document_path = public_path(Storage::url($a_file_path['path']));
		}


		if($request->country == 'Other') {
			$country = 'OT';
		}
		else {
			$country = Country::where('name', $request->country)->first()->code;
		}

		if ($payout_method == 'Stripe') {
			$stripe_payout = resolve('App\Repositories\StripePayout');

			$request['payout_country'] = $country;
			$iban_supported_country = Country::getIbanRequiredCountries();

			$bank_data = array(
	            "country"       		=> $country,
	            "currency"   			=> $request->currency,
	            "account_holder_name"	=> $request->account_holder_name,
	            "account_holder_type" 	=> $account_holder_type,
	        );

			if (in_array($country, $iban_supported_country)) {
				$request['account_number'] = $request->iban;
				$bank_data['account_number'] = $request->iban;
			}
			else {
				if ($country == 'AU') {
					$request['routing_number'] = $request->bsb;
				}
				elseif ($country == 'HK') {
					$request['routing_number'] = $request->clearing_code . '-' . $request->branch_code;
				}
				elseif ($country == 'JP' || $country == 'SG') {
					$request['routing_number'] = $request->bank_code . $request->branch_code;
				}
				elseif ($country == 'GB') {
					$request['routing_number'] = $request->sort_code;
				}
				$bank_data['routing_number'] = $request['routing_number'];
				$bank_data['account_number'] = $request->account_number;
			}

			$validate_data = $stripe_payout->validateRequest($request);
			if($validate_data) {
	            return $validate_data;
	        }
			$stripe_token = $stripe_payout->createStripeToken($bank_data);

			if(!$stripe_token['status']) {
				return response()->json([
				    'status_code' => '0',
				    'status_message' => $stripe_token['status_message'],
				]);
			}

			$request['stripe_token'] = $stripe_token['token'];

			$stripe_preference = $stripe_payout->createPayoutPreference($request);

			if(!$stripe_preference['status']) {
				return response()->json([
				    'status_code' => '0',
				    'status_message' => $stripe_preference['status_message'],
				]);
			}

			$recipient = $stripe_preference['recipient'];
			if(isset($document_path)) {
				$document_result = $stripe_payout->uploadDocument($document_path,$recipient->id);
				if(!$document_result['status']) {
					return response()->json([
					    'status_code' => '0',
					    'status_message' => $document_result['status_message'],
					]);
				}
				$stripe_document = $document_result['stripe_document'];
			}

			if(isset($a_document_path)) {
				$stripe_document = (isset($document_path)) ? $stripe_document : '';
				$document_result = $stripe_payout->uploadAdditonalDocument($stripe_document,$a_document_path,$recipient->id,$recipient->individual->id);
				if(!$document_result['status']) {
					return response()->json([
					    'status_code' => '0',
					    'status_message' => $document_result['status_message'],
					]);
				}
			}

		}

		$payout_preference = new PayoutPreference;
		$payout_preference->user_id 		= $user_id;
		$payout_preference->country 		= $country;
		$payout_preference->currency_code 	= ($country != 'OT') ? $request->currency : '';
		$payout_preference->routing_number 	= $request->routing_number ?? '';
		$payout_preference->account_number 	= $request->account_number;
		$payout_preference->holder_name 	= $request->account_holder_name;
		$payout_preference->holder_type 	= $account_holder_type;
		$payout_preference->paypal_email 	= isset($recipient->id) ? $recipient->id : $user->email;
		$payout_preference->address1 	= $request->address1 ?? '';
		$payout_preference->address2 	= $request->address2 ?? '';
		$payout_preference->city 		= $request->city;
		$payout_preference->state 		= $request->state;
		$payout_preference->postal_code = $request->postal_code;
		if (isset($document_path)) {
			$payout_preference->document_id 	= $stripe_document;
			$payout_preference->document_image 	= $filename;
		}
		if (isset($a_document_path)) {
			$payout_preference->additional_document_image 	= $a_filename;
		}
		$payout_preference->phone_number 	= $request->phone_number ?? '';
		$payout_preference->branch_code 	= $request->branch_code ?? '';
		$payout_preference->bank_name		= $request->bank_name ?? '';
		$payout_preference->branch_name 	= $request->branch_name ?? '';
		$payout_preference->ssn_last_4 		= $country == 'US' ? $request->ssn_last_4 : '';
		$payout_preference->payout_method 	= $payout_method;
		$payout_preference->address_kanji 	= isset($address_kanji) ? json_encode($address_kanji) : json_encode([]);
		$payout_preference->save();
		
		$payout_check = PayoutPreference::where('user_id', $user_id)->where('default', 'yes')->count();
		if ($payout_check == 0) {
			$payout_preference->default = 'yes';
			$payout_preference->save();
		}

		return response()->json([
			'status_code' 		=> '1',
			'status_message' 	=> trans('api_messages.driver.payout_details_added'),
		]);
	}

	/**
	 * Driver to confirm the order
	 *
	 * @return Response Json response
	 */
	public function confirm_order_delivery() {

		$request = request();
		$driver = Driver::authUser()->first();
		$issue_type = new IssueType;
		$order_delivery = new OrderDelivery;

		$order = Order::where('id', $request->order_id)->first();
		$order_delivery_id = 0;
		if ($order && $order->order_delivery) {
			$order_delivery_id = $order->order_delivery->id;
		}

		$request_data = $request->all();
		$request_data['issues_array'] = explode(',', $request->issues);
		$request_data['order_delivery_id'] = $order_delivery_id;

		$rules = [
			'order_id' => 'required|exists:order,id,driver_id,' . $driver->id,
			'is_thumbs' => 'required|in:0,1',

			'order_delivery_id' => 'exists:order_delivery,id,status,' . $order_delivery->statusArray['pending'],
		];

		$messages = [
			//'issues_array.*.exists' => 'The selected issue type :input is not belongs to the current review type',
			'order_delivery_id.exists' => trans('validation.exists', ['attribute' => 'order id']),
		];

		$validator = Validator::make($request_data, $rules, $messages);

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
		$review->type = $review->typeArray['driver_restaurant'];
		$review->reviewer_id = $order->driver_id;
		$review->reviewee_id = $order->restaurant_id;
		$review->is_thumbs = $request->is_thumbs;
		$review->comments = $request->comments ?: "";
		$review->save();
		if ($request->issues) {
			$issues = explode(',', $request->issues);
			if ($request->is_thumbs == 0 && count($issues) > 0) {
				foreach ($issues as $issue_id) {
					$review_issue = new ReviewIssue;
					$review_issue->review_id = $review->id;
					$review_issue->issue_id = $issue_id;
					$review_issue->save();
				}
			}
		}
		$order->order_delivery->confirmed();

		return response()->json(
			[
				'status_message' =>trans('api_messages.driver.order_confirmed'),
				'status_code' => '1',
			]
		);
	}

	/**
	 * Driver to start the order delivery
	 *
	 * @return Response Json response
	 */
	public function start_order_delivery(Request $request)
	{
		$driver = Driver::authUser()->first();

		$order_delivery = new OrderDelivery;
		$order = Order::where('id', $request->order_id)->first();

		$order_delivery_id = 0;
		if ($order && $order->order_delivery) {
			$order_delivery_id = $order->order_delivery->id;
		}

		$request_data = $request->all();
		$request_data['order_delivery_id'] = $order_delivery_id;

		$rules = [
			'order_id' => 'required|exists:order,id,driver_id,' . $driver->id,
			'order_delivery_id' => 'exists:order_delivery,id,status,' . $order_delivery->statusArray['confirmed'],
		];

		$messages = [
			'order_delivery_id.exists' => trans('validation.exists', ['attribute' => 'order id']),
		];

		$validator = Validator::make($request_data, $rules, $messages);

		if ($validator->fails()) {
			return response()->json([
				'status_code' => '0',
				'status_message' => $validator->messages()->first(),
			]);
		}

		$order->order_delivery->started();

		return response()->json([
			'status_code' => '1',
			'status_message' => trans('api_messages.driver.order_started'),
		]);
	}

	/**
	 * Driver to drop off delivery
	 *
	 * @return Response Json response
	 */
	public function drop_off_delivery() {
		$request = request();
		$driver = Driver::authUser()->first();
		$issue_type = new IssueType;

		$order_delivery = new OrderDelivery;
		$order = Order::where('id', $request->order_id)->first();

		$order_delivery_id = 0;
		if ($order && $order->order_delivery) {
			$order_delivery_id = $order->order_delivery->id;
		}

		$request_data = $request->all();
		$request_data['issues_array'] = explode(',', $request->issues);
		$request_data['order_delivery_id'] = $order_delivery_id;

		$dropoff_recipient = $order->getDropoffOptions()->keys()->implode(',');

		$rules = [
			'order_id' => 'required|exists:order,id,driver_id,' . $driver->id,
			'recipient' => 'required|in:' . $dropoff_recipient,
			'is_thumbs' => 'required|in:0,1',
			// 'issues' => 'required_if:is_thumbs,0',
			// 'issues_array.*' => 'required_if:is_thumbs,0|exists:issue_type,id,type_id,' . $issue_type->typeArray['driver_delivery'],
			'order_delivery_id' => 'exists:order_delivery,id,status,' . $order_delivery->statusArray['started'],
		];

		$messages = [
			// 'issues_array.*.exists' => 'The selected issue type :input is not belongs to the current review type',
			'order_delivery_id.exists' => trans('validation.exists', ['attribute' => 'order id']),
		];

		$validator = Validator::make($request_data, $rules, $messages);

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
		$review->type = $review->typeArray['driver_delivery'];
		$review->reviewer_id = $order->driver_id;
		$review->reviewee_id = $order->user_id;
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

		$order->order_delivery->delivered();

		$order->delivery_delivered($request->recipient);

		return response()->json(
			[
				'status_message' => trans('api_messages.driver.order_dropped_off'),
				'status_code' => '1',
			]
		);
	}

	/**
	 * Driver to complete the order delivery
	 *
	 * @return Response Json response
	 */
	public function complete_order_delivery()
	{
		$request = request();
		$driver = Driver::authUser()->first();

		$order_delivery = new OrderDelivery;
		$order = Order::where('id', $request->order_id)->first();

		$order_delivery_id = 0;
		if ($order && $order->order_delivery) {
			$order_delivery_id = $order->order_delivery->id;
		}

		$request_data = $request->all();
		$request_data['order_delivery_id'] = $order_delivery_id;

		$rules = [
			'order_id' => 'required|exists:order,id,driver_id,' . $driver->id,
			'order_delivery_id' => 'exists:order_delivery,id,status,' . $order_delivery->statusArray['delivered'],
		];

		$messages = [
			'order_delivery_id.exists' => trans('validation.exists', ['attribute' => 'order id']),
		];

		$validator = Validator::make($request_data, $rules, $messages);

		if ($validator->fails()) {
			return response()->json([
				'status_code' => '0',
				'status_message' => $validator->messages()->first(),
			]);
		}

		// Trip Map upload //

		$file = $request->file('map_image');

		if ($file) {

			$file_path = $this->fileUpload($file, 'public/images/trip_image/' . $order->id);

			$this->fileSave('trip_image', $order->id, $file_path['file_name'], '1');

			$original_path = Storage::url($file_path['path']);
		}

		$order->order_delivery->completed();

		return response()->json([
			'status_code' => '1',
			'status_message' => trans('api_messages.orders.order_completed'),
		]);
	}

	/**
	 * Driver to cancel order
	 *
	 * @return Response Json response with status
	 */
	public function cancel_order_delivery() {

		$request = request();
		$driver = Driver::authUser()->first();

		$order_delivery = new OrderDelivery;
		$order = Order::where('id', $request->order_id)->first();

		$order_delivery_id = 0;
		if ($order && $order->order_delivery) {
			$order_delivery_id = $order->order_delivery->id;
		}

		$request_data = $request->all();
		$request_data['order_delivery_id'] = $order_delivery_id;

		$rules = [
			'order_id' => 'required|exists:order,id,driver_id,' . $driver->id,
			'order_delivery_id' => 'exists:order_delivery,id',
			'cancel_reason' => 'required|exists:order_cancel_reason,id',
		];

		$messages = [

			'order_delivery_id.exists' => trans('validation.exists', ['attribute' => 'order id']),
		];

		$validator = Validator::make($request_data, $rules, $messages);

		if ($validator->fails()) {
			return response()->json(
				[
					'status_code' => '0',
					'status_message' => $validator->messages()->first(),
				]
			);
		}

		if ($order->order_delivery->status_text == 'pending' || $order->order_delivery->status_text == 'confirmed') {

			$order->cancel_order("driver", $request->cancel_reason, $request->cancel_message);

			$order->order_delivery->revert();

		} else {

			$order->cancel_order("driver", $request->cancel_reason, $request->cancel_message);

			$order->order_delivery->cancelled();

			//Revert Penality amount if exists

			$penality_Revert = revertPenality($order->id);

		}

		return response()->json(
			[
				'status_message' => trans('api_messages.driver.order_has_been_cancelled'),
				'status_code' => '1',
			]
		);
	}

	/**
	 * get owe amount
	 *
	 * @return Response Json response with status
	 */
	public function get_owe_amount() {
		$request = request();
		$driver = Driver::authUser()->first();

		$owe_amount = DriverOweAmount::where('user_id', $driver->user_id)->first();

		if ($owe_amount) {
			$amount = $owe_amount->amount;
			$currency_code = $owe_amount->currency_code;

			return response()->json(
				[

					'status_message' => trans('api_messages.driver.payout_successfully'),
					'status_code' => '1',
					'amount' => $amount,
					'currency_code' => $currency_code,

				]
			);
		} else {
			return response()->json(
				[

					'status_message' => trans('api_messages.driver.not_generate_amount'),
					'status_code' => '1',

				]
			);
		}
	}

	/**
	 * payout to admin
	 *
	 * @return Response Json response with status
	 */
	public function pay_to_admin(Request $request)
	{
		$driver = Driver::authUser()->first();

		$amount = $request->amount;

		$owe_amount = DriverOweAmount::where('user_id', $driver->user_id)->first();

		if ($owe_amount == '') {
			return response()->json([
				'status_code' => '0',
				'status_message' => trans('api_messages.driver.not_generate_amount'),
			]);
		}

		$total_owe_amount = $owe_amount->amount;
		$currency_code = $owe_amount->currency_code;
		$remaining_amount = $total_owe_amount - $amount;

		if ($total_owe_amount <= 0) {
			return response()->json([
				'status_code' => '0',
				'status_message' => trans('api_messages.driver.owe_amount_empty'),
			]);
		}

		$stripe_payment = resolve('App\Repositories\StripePayment');
		if($request->filled('payment_intent_id')) {
			$payment_result = $stripe_payment->CompletePayment($request->payment_intent_id);
		}
		else {
			$user_payment_method = UserPaymentMethod::where('user_id', $driver->user_id)->first();

			$paymentData = array(
				"amount" 		=> $amount * 100,
				'currency' 		=> $currency_code,
				'description' 	=> 'Payment for Owe Amount by : '.$driver->user->first_name,
				"customer" 		=> $user_payment_method->stripe_customer_id,
				'payment_method'=> $user_payment_method->stripe_payment_method,
		      	'confirm' 		=> true,
		      	'off_session' 	=> true,
			);

			$payment_result = $stripe_payment->createPaymentIntent($paymentData);
		}

		if($payment_result->status == 'requires_action') {
			return response()->json([
				'status_code' 	=> '2',
				'status_message'=> $payment_result->status_message,
				'client_secret'	=> $payment_result->intent_client_secret,
			]);
		}
		else if($payment_result->status != 'success') {
			return response()->json([
				'status_code' 	=> '0',
				'status_message'=> $payment_result->status_message,
			]);
		}

		$owe_amount->amount = $remaining_amount;
		$owe_amount->save();

		$payment = new Payment;
		$payment->user_id = $driver->user_id;
		$payment->order_id = $request->order_id;
		$payment->transaction_id = $payment_result->transaction_id;
		$payment->type = 0;
		$payment->amount = $amount;
		$payment->status = 1;
		$payment->currency_code = $currency_code;
		$payment->save();

		$owe_amount = DriverOweAmount::where('user_id', get_current_login_user_id())->first();

		return response()->json([
			'status_code' 		=> '1',
			'status_message' 	=> __('api_messages.driver.payout_successfully'),
			'owe_amount' 		=> $owe_amount->amount,
			'currency_code' 	=> $owe_amount->currency_code,
		]);
	}

	/**
	 * Display payout details
	 *
	 * @param  Get method request inputs
	 * @return Response in Json
	 */
	public function payout_details() {
		$payout_details = $this->get_payout_details();

		if (count($payout_details) == 0) {
			return response()->json(['status_message' => trans('api_messages.driver.no_data_found'), 'status_code' => '0']);
		}

		return response()->json(
			[

				'status_message' => trans('api_messages.driver.payoutpreference_details'),

				'status_code' => '1',

				'payout_details' => $payout_details,

			]
		);
	}

	/**
	 * Payout Set Default and Delete
	 *
	 * @param  Get method request inputs
	 * @param  Type  Default   Set Default payout
	 * @param  Type  Delete    Delete payout Details
	 * @return Response in Json
	 */
	public function payout_changes(Request $request) {
		$request = request();
		$driver = Driver::authUser()->first();

		$rules = array(

			'payout_id' => 'required|exists:payout_preference,id',

			'type' => 'required',

		);

		$niceNames = array('payout_id' => 'Payout Id');

		$messages = array('required' => ':attribute is required.');

		$validator = Validator::make($request->all(), $rules, $messages);

		$validator->setAttributeNames($niceNames);

		if ($validator->fails()) {
			$error = $validator->messages()->toArray();

			foreach ($error as $er) {
				$error_msg[] = array($er);
			}

			return response()->json(
				[

					'status_message' => $error_msg['0']['0']['0'],

					'status_code' => '0',

				]
			);
		}

		//check valid user or not
		$check_user = PayoutPreference::where('id', $request->payout_id)

			->where('user_id', $driver->user_id)

			->first();

		if ($check_user->count()  < 1) {
			return response()->json(
				[

					'status_message' => trans('api_messages.payout.permission_denied'),

					'status_code' => '0',

				]
			);
		}

		//check valid type or not
		if ($request->type != 'default' && $request->type != 'delete') {
			return response()->json(
				[

					'status_message' => trans('api_messages.payout.the_selected_type_is_invalid'),

					'status_code' => '0',

				]
			);
		}

		//set default payout
		if ($request->type == 'default') {
			$payout = PayoutPreference::where('id', $request->payout_id)->first();

			if ($payout->default == 'yes') {
				return response()->json(
					[

						'status_message' => trans('api_messages.payout.the_given_payout_id'),

						'status_code' => '0']
				);
			} else {
				//Changed default option No in all Payout based on user id
				$payout_all = PayoutPreference::where('user_id', $driver->user_id)->update(['default' => 'no']);

				$payout->default = 'yes';

				$payout->save(); //save payout detils

				$payout_details = $this->get_payout_details();

				return response()->json(
					[

						'status_message' => trans('api_messages.payout.payout_preferences_successfully'),

						'status_code' => '1',

						'payout_details' => $payout_details,

					]
				);
			}
		}
		//Delete payout

		if ($request->type == 'delete') {
			$payout = PayoutPreference::where('id', $request->payout_id)->first();

			if ($payout->default == 'yes') {
				return response()->json(
					[

						'status_message' => trans('api_messages.payout.permission_denied_default_payout'),

						'status_code' => '0',

					]
				);
			} else {
				$payout->delete(); //Delete payout.

				$payout_details = $this->get_payout_details();

				return response()->json(
					[

						'status_message' => trans('api_messages.payout.payout_details_deleted_successfully'),

						'status_code' => '1',

						'payout_details' => $payout_details,

					]
				);
			}
		}
	}

	public function get_payout_details() {
		$request = request();
		$driver = Driver::authUser()->first();

		//get payout preferences details

		$payout_details = @PayoutPreference::where('user_id', $driver->user_id)->get();

		$data = [];

		foreach ($payout_details as $payout_result) {
			$data[] = array(

				'payout_id' => $payout_result->id,

				'user_id' => $payout_result->user_id,

				'payout_method' => $payout_result->payout_method != null

				? $payout_result->payout_method : '',

				'paypal_email' => $payout_result->paypal_email != null

				? $payout_result->paypal_email : '',

				'set_default' => ucfirst($payout_result->default),

			);
		}

		return $data;
	}

	public function earning_list() {

		$request = request();
		$driver = Driver::authUser()->first();

		$rules = [
			'type' => 'required|in:week,weekly,date',
			'start_date' => 'required|date|date_format:Y-m-d',
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

		$start_of_the_week = 0;

		if ($request->type == 'week') {

			$start_date = strtotime($request->start_date);

			$week = date('W', $start_date);
			$year = date('Y', $start_date);

			list($week_start_date, $week_end_date) = $this->getStartAndEndDate($week, $year);

			$order_delivery_list = OrderDelivery::with('order')->driverId([$driver->id])->week(date('Y-m-d', strtotime($week_start_date)))
				->status(['completed'])
				->select()->addSelect(\DB::raw("DATE_FORMAT(confirmed_at, '%d-%m-%Y') as date"))
				->get()->groupBy('date');

			$order_delivery_list = $order_delivery_list->flatMap(

				function ($date_list, $date) {

					return [$date =>
						[
							"total_fare" => $date_list->sum("total_fare") - $date_list->sum("driver_earning"),
							"day" => trans('api_messages.weekday.'.date('l', strtotime($date))),
							"date" => $date,
						],
					];
				}
			);

			$date_list = array();

			$current_date = strtotime($week_start_date);

			while ($current_date <= strtotime("+6 days", strtotime($week_start_date))) {

				$date = date('d-m-Y', $current_date);
				$order_data = isset($order_delivery_list[$date]) ? $order_delivery_list[$date] : [
					"total_fare" => "0",
					"day" => trans('api_messages.weekday.'.date('l', strtotime($date))),
					"date" => $date,
				];

				$date_list[] = $order_data;
				$current_date = strtotime("+1 day", $current_date);
			}

			$last_trip_total_fare = end($date_list)['total_fare'];

			$earning_list = [
				'total_fare' => numberFormat($order_delivery_list->sum('total_fare') - $order_delivery_list->sum('driver_earning')),
				'date_list' => $date_list,
				'last_trip_total_fare' => $last_trip_total_fare,
				'last_payout' => '0',
			];

		}

		$earning_list['currency_code'] = $driver->user->currency_code;
		$earning_list['currency_symbol'] = Currency::original_symbol($earning_list['currency_code']);

		return response()->json(
			[
				'status_code' => '1',
				'status_message' => trans('api_messages.payout.earning_list_listed_successfully'),
				'earning_list' => $earning_list,
			]
		);
	}

	public function order_delivery_history(Request $request)
	{
		$driver = Driver::authUser()->first();

		$today_date = date("Y-m-d");

		$today_delivery = OrderDelivery::driverId([$driver->id])->date($today_date)->orderBy("order_id", "desc")
			->get();

		$past_delivery = OrderDelivery::driverId([$driver->id])->past($today_date)->orderBy("order_id", "desc")
			->get();

		$today_delivery = $today_delivery->map(
			function ($delivery) {
				return [
					'id' => $delivery->order_id,
					'total_fare' => numberFormat($delivery->total_fare - $delivery->driver_earning),
					'vehicle_name' => $delivery->driver->vehicle_type_name,
					'status' => $delivery->status,
					'map_image' => $delivery->trip_path,
				];
			}
		);

		$past_delivery = $past_delivery->map(
			function ($delivery) {
				return [
					'id' => $delivery->order_id,
					'total_fare' => numberFormat($delivery->total_fare - $delivery->driver_earning),
					'vehicle_name' => $delivery->driver->vehicle_type_name,
					'status' => $delivery->status,
					'map_image' => $delivery->trip_path,
				];
			}
		);

		return response()->json(
			[
				'status_message' => trans('api_messages.payout.order_delivery_history'),
				'status_code' => '1',
				'past_delivery' => $past_delivery,
				'today_delivery' => $today_delivery,
				'currency_code' => $driver->user->currency_code,
				'currency_symbol' => Currency::original_symbol($driver->user->currency_code),
			]
		);
	}

	public function weekly_trip() {

		$request = request();
		$driver = Driver::authUser()->first();

		$weekly_trip = OrderDelivery::driverId([$driver->id])->whereNotNull('confirmed_at')
			->status(['completed'])
			->get()
			->groupBy(function ($date) {
				return Carbon::parse($date->confirmed_at)->format('W');
			});

		foreach ($weekly_trip as $key => $value) {

			$total = 0;

			foreach ($value as $fare) {
				$total += $fare->total_fare - $fare->driver_earning;
				$year = date('Y', strtotime($fare->confirmed_at));

			}

			$date = getWeekDates($year, $key);

			$format_date = date('d', strtotime($date['week_start'])).trans('api_messages.monthandtime.'.date('M', strtotime($date['week_start']))) . '-' . date('d', strtotime($date['week_end'])).trans('api_messages.monthandtime.'.date('M', strtotime($date['week_end'])));

			$week[] = ['week' => $format_date,
				'total_fare' => numberFormat($total),
				'year' => $year,
				'date' => $date['week_start']];
		}

		return response()->json(
			[
				'status_code' => '1',
				'status_message' => trans('api_messages.payout.successfully'),
				'trip_week_details' => isset($week) ? $week : [],

			]
		);

	}

	public function weekly_statement() {

		$request = request();
		$driver = Driver::authUser()->first();
		$from = $request->date;

		$date = strtotime("+6 day", strtotime($from));
		$to = date('Y-m-d', $date);

		$details = OrderDelivery::driverId([$driver->id])->whereNotNull('confirmed_at')->select()->addSelect(\DB::raw("DATE_FORMAT(confirmed_at, '%d-%m-%Y') as date"))->whereBetween(DB::raw('Date(confirmed_at)'), [$from, $to])->status(['completed'])->get();

		$statement = $details->groupBy('date');

		$common = Order::whereDriverId($driver->id)->status('completed')->whereBetween(DB::raw('Date(updated_at)'), [$from, $to]);

		$cash = (clone $common)->where('payment_type', 0)->where('total_amount', '!=', 0)->get();

		$driver_fee = (clone $common)->where('payment_type', 0)->get();

		$card = (clone $common)->where('payment_type', 1)->get();

		$driver_commision_fee = numberFormat($driver_fee->sum('driver_commision_fee') + $card->sum('driver_commision_fee'));

		$cash_collected = ($cash->sum('owe_amount')) + ($cash->sum('delivery_fee')) - ($cash->sum('driver_commision_fee'));

		$payout = Payout::whereUserId($driver->user_id)->whereBetween(DB::raw('Date(updated_at)'), [$from, $to])->get()->sum('amount');

		$total = 0;
		$statement = $statement->flatMap(
			function ($date_list, $date) {

				return [

					["total_fare" => numberFormat($date_list->sum("total_fare")),
						"driver_earning" => numberFormat($date_list->sum("total_fare") - $date_list->sum("driver_earning")),
						"day" => trans('api_messages.weekday.'.date('l', strtotime($date))),
						"format" => date('d/m', strtotime($date)),
						"date" => date('Y-m-d', strtotime($date)),

					],

				];
			}

		);

		$total = array_column($statement->toArray(), 'total_fare');
		$total = numberFormat(array_sum($total));

		$total_fare = numberFormat($total - $driver_commision_fee);

		return response()->json(
			[
				'status_code' => '1',
				'status_message' => trans('api_messages.payout.successfully'),
				'statement' => $statement,
				'total_fare' => (string) $total_fare,
				'base_fare' => (string) $total,
				'access_fee' => (string) $driver_commision_fee,
				'cash_collected' => (string) numberFormat($cash_collected),
				'completed_trips' => $details->count(),
				'format_date' => trans('api_messages.monthandtime.'.date('M', strtotime($from))).date('d', strtotime($from)) . '-' .trans('api_messages.monthandtime.'.date('M', strtotime($to))). date('d', strtotime($to)),				
				'bank_deposits' => (string) numberFormat($payout),
				'time_online' => '',

			]
		);

	}

	public function daily_statement() {

		$request = request();
		$driver = Driver::authUser()->first();
		$from = $request->date;

		$daily_statement = OrderDelivery::driverId([$driver->id])->whereNotNull('confirmed_at')->where(DB::raw('Date(confirmed_at)'), $from)->status(['completed'])->get();

		$total_fare = numberFormat($daily_statement->sum('total_fare'));

		$common = Order::whereDriverId($driver->id)->status('completed')->where(DB::raw('Date(updated_at)'), $from);

		$cash = (clone $common)->where('payment_type', 0)->where('total_amount', '!=', 0)->get();
		$driver_fee = (clone $common)->where('payment_type', 0)->get();
		$card = (clone $common)->where('payment_type', 1)->get();

		$driver_commision_fee = ($driver_fee->sum('driver_commision_fee')) + ($card->sum('driver_commision_fee'));

		$cash_collected = ($cash->sum('owe_amount')) + ($cash->sum('delivery_fee')) - ($cash->sum('driver_commision_fee'));

		$payout = Payout::whereUserId($driver->user_id)->where(DB::raw('Date(updated_at)'), $from)->get()->sum('amount');

		$daily_statement = $daily_statement->map(
			function ($daily) {
				return [
					'id' => $daily->order_id,
					'total_fare' => numberFormat($daily->total_fare),
					"driver_earning" => numberFormat($daily->total_fare - $daily->driver_earning),
					'time' => date('h:i', strtotime($daily->confirmed_at)).trans('api_messages.monthandtime.'.date('a', strtotime($daily->confirmed_at))),

				];
			}
		);
		$earning = $total_fare - $driver_commision_fee;

		return response()->json(
			[
				'status_code' => '1',
				'status_message' => trans('api_messages.payout.successfully'),
				'daily_statement' => $daily_statement,
				'date' => $from,
				'format_date' => date('d/m', strtotime($from)),
				"day" =>  trans('api_messages.weekday.'.date('l', strtotime($from))),
				'total_fare' => (string) numberFormat($earning),
				'base_fare' => (string) $total_fare,
				'access_fee' => (string) numberFormat($driver_commision_fee),
				'cash_collected' => (string) numberFormat($cash_collected),
				'completed_trips' => $daily_statement->count(),
				'bank_deposits' => (string) numberFormat($payout),
				'time_online' => '',

			]
		);

	}

	public function particular_order()
	{
		$request = request();
		$driver = Driver::authUser()->first();

		$trip_details = OrderDelivery::with('order.payout_table')->OrderId([$request->order_id])->first();

		$vehicle_type = $trip_details->driver ? $trip_details->driver->vehicle_type_name : '';

		$trip_details = replace_null_value($trip_details->toArray());

		$driver_commision_fee = numberFormat($trip_details['order']['driver_commision_fee']);

		$driver_payout = numberFormat($trip_details['total_fare'] - $driver_commision_fee);

		if ($trip_details['order']['payment_type'] == 0 && $trip_details['order']['total_amount'] != 0) {
			if ($trip_details['order']['owe_amount'] != 0) {
				$cash_collected = numberFormat($trip_details['order']['owe_amount'] + $driver_payout);
			}
			else {
				$cash_collected = numberFormat($trip_details['order']['total_amount']);
			}
		}
		else {
			$cash_collected = '0.00';
		}

		if ($trip_details['confirmed_at']) {
			$date = $trip_details['confirmed_at'];
		}
		else {
			$date = $trip_details['updated_at'];
		}

		$can_payout = Payout::where('order_id', $trip_details['order_id'])->
			where('user_id', $driver->user_id)->first();

		$cancel_payout = 0;

		if($can_payout) {
			$cancel_payout = $can_payout->amount;
		}

		$owe_amount = '0';
		$distance_fare = '0';
		$pickup_fare = '0';
		$drop_fare = '0';

		if ($trip_details['status'] != '6') {
			$owe_amount = $trip_details['order']['owe_amount'];
			$distance_fare = $trip_details['est_distance'] ? (string) numberFormat($trip_details['distance_fare'] * $trip_details['est_distance']) : '0';
			$pickup_fare = $trip_details['pickup_fare'] ? (string) $trip_details['pickup_fare'] : '0.00';
			$drop_fare = $trip_details['drop_fare'] ? $trip_details['drop_fare'] : '0.00';
			$total_fare = $trip_details['total_fare'];
		}
		else {
			$cash_collected = '0.00';
			$total_fare = '0';
			$driver_payout = '0';
		}
		$duration_hour = '0';	
		$duration_min = '0';
		
		//hours and min translation process
		$getDurations = (string) $trip_details['duration']; 
		//first hr and min from table
		if(!empty($getDurations)) {
			
			$parts = explode(' ', $getDurations); //string convert to array

			if (count($parts) == 2) {
				//checking process hour or minutes
				if ($parts[1] == 'hr') {
			  		$duration_hour = $parts[0];
				}
				if ($parts[1] == 'min') {
			  		$duration_min = $parts[0];
				}
			}
			else{
				//checking process hour or minutes
				if ($parts[1] == 'hr') {
			  		$duration_hour= $parts[0];
				}
				if ($parts[1] == 'min') {
			  		$duration_min = $parts[0];
				}
				if ($parts[3] == 'min') {
			  		$duration_min = $parts[2];	
				}
			}
		}

		$trip = [
			'order_id' 			=> $trip_details['order_id'],
			'total_fare' 		=> $total_fare,
			'status' 			=> $trip_details['status'],
			'vehicle_name' 		=> $vehicle_type,
			'map_image' 		=> $trip_details['trip_path'],
			'trip_date' 		=> trans('api_messages.weekday.'.date('l', strtotime($date))).date('d/m/Y h:i', strtotime($date)).trans('api_messages.clock.'.date('a', strtotime($date))),
			'pickup_latitude'	=> $trip_details['pickup_latitude'],
			'pickup_longitude'	=> $trip_details['pickup_longitude'],
			'pickup_location' 	=> $trip_details['pickup_location'],
			'drop_location' 	=> $trip_details['drop_location'],
			'drop_latitude' 	=> $trip_details['drop_latitude'],
			'drop_longitude' 	=> $trip_details['drop_longitude'],
			'duration_hour' 	=> $duration_hour,
			'duration_min' 		=> $duration_min,
			'distance' 			=> number_format($trip_details['drop_distance'], 1),
			'pickup_fare' 		=> $pickup_fare,
			'drop_fare' 		=> $drop_fare,
			'driver_payout' 	=> (string) $driver_payout,
			'trip_amount' 		=> $trip_details['order']['total_amount'],
			'delivery_fee' 		=> $trip_details['order']['delivery_fee'],
			'owe_amount' 		=> $owe_amount,
			'admin_payout' 		=> (string) numberFormat($driver_commision_fee),
			'distance_fare' 	=> $distance_fare,
			'cash_collected' 	=> (string) $cash_collected,
			'driver_penality' 	=> (string) $trip_details['order']['driver_penality'],
			'applied_penality' 	=> (string) $trip_details['order']['app_driver_penality'],
			'cancel_payout' 	=> (string) numberFormat($cancel_payout),
			'applied_owe' 		=> (string) $trip_details['order']['applied_owe'],
			'notes' 			=> (string) $trip_details['order']['driver_notes'],
		];

		return response()->json([
			'status_code' => '1',
			'status_message' => trans('api_messages.payout.successfully'),
			'trip_details' => $trip,
		]);
	}

	public function static_map($order_id = '') {

		$order = Order::find($order_id);

		$user_id = get_restaurant_user_id($order->restaurant_id);

		$res_address = get_restaurant_address($user_id);

		$user_address = get_user_address($order->user_id);

		// $origin = "45.291002,-0.868131";
		// $destination = "44.683159,-0.405704";

		$origin = $res_address->latitude . ',' . $res_address->longitude;
		$destination = $user_address->latitude . ',' . $user_address->longitude;

		$map_url = getStaticGmapURLForDirection($origin, $destination);

		// Trip Map upload //

		if ($map_url) {

			$directory = storage_path('app/public/images/map_image');

			if (!is_dir($directory = storage_path('app/public/images/map_image'))) {
				mkdir($directory, 0755, true);
			}

			$time = time();
			$imageName = 'map_' . $time . '.PNG';
			$imagePath = $directory . '/' . $imageName;
			file_put_contents($imagePath, file_get_contents($map_url));

			$this->fileSave('map_image', $order_id, $imageName, '1');

		}

	}

	function getStartAndEndDate($week, $year) {

		$dateTime = new DateTime();
		$dateTime->setISODate($year, $week);
		$start_date = $dateTime->format('Y-m-d');
		$dateTime->modify('+6 days');
		$end_date = $dateTime->format('Y-m-d');
		return array($start_date, $end_date);

	}

}
