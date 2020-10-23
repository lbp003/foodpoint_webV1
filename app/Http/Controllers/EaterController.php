<?php

/**
 * EaterController
 *
 * @package    GoferEats
 * @subpackage  Controller
 * @category    Eater
 * @author      Trioangle Product Team
 * @version     1.3
 * @link        http://trioangle.com
 */

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Country;
use App\Models\Menu;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\OrderCancelReason;
use App\Models\OrderItem;
use App\Models\PromoCode;
use App\Models\Restaurant;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\UsersPromoCode;
use App\Models\MenuItemModifier;
use App\Models\OrderItemModifier;
use App\Models\OrderItemModifierItem;
use Auth;
use Session;

class EaterController extends Controller
{
	/**
	 * Restaurant detail page
	 *
	 */
	public function detail()
	{
		if (session::get('schedule_data') == null) {
			//dd('ss');
			$schedule_data = array('status' => 'ASAP', 'delivery_mode' => '2', 'date' => '', 'time' => '');

			session::put('schedule_data', $schedule_data);
		}

		$this->view_data['restaurant_id'] = request()->restaurant_id;
		$this->view_data['user_details'] = auth()->guard('web')->user();
		$this->view_data['order_detail_data'] = '';


		$this->view_data['restaurant'] = Restaurant::where('id',$this->view_data['restaurant_id'])->status()->userstatus()->firstOrFail();


		$this->view_data['restaurant_time_data'] = 0;
		if (isset($this->view_data['restaurant']->restaurant_all_time[0])) {
			$this->view_data['restaurant_time_data'] = $this->view_data['restaurant']->restaurant_all_time[0]->is_available;
		}

		$this->view_data['restaurant_cuisine'] = $this->view_data['restaurant']->restaurant_cuisine;

		$this->view_data['restaurant_menu'] = Menu::menuRelations()
			->where('restaurant_id', $this->view_data['restaurant_id'])->get();

		$this->view_data['menu_category'] = '';

		if ($this->view_data['restaurant_menu']->count()) {
			$this->view_data['menu_category'] = $this->view_data['restaurant_menu']->first()->menu_category;
		}

		$this->view_data['order_detail_data'] = get_user_order_details($this->view_data['restaurant_id'], @$this->view_data['user_details']->id);
		$cart_restaurant_id = $this->view_data['order_detail_data'] ? $this->view_data['order_detail_data']['restaurant_id'] : '';
		if ($cart_restaurant_id) {
			$this->view_data['other_restaurant_detail'] = Restaurant::findOrFail($cart_restaurant_id);
		}

		$this->view_data['other_restaurant'] = ($cart_restaurant_id != '' && $cart_restaurant_id != $this->view_data['restaurant_id']) ? 'yes' : 'no';

		if ($this->view_data['other_restaurant'] == 'yes') {
			$this->view_data['order_detail_data'] = '';
		}
		
		$this->view_data['restaurant_logo_url'] = $this->view_data['restaurant']->restaurant_logo;

		return view('detail', $this->view_data);
	}

	//session clear for menu's

	public function session_clear_data()
	{
		$result = session_clear_all_data();
		if($result == 'success') {
			return ['status' => true];
		}
		return ['status' => false];
	}

	//menu item detail
	public function menu_item_detail(Request $request)
	{
		$item_id = $request->item_id;
		$menu_item = MenuItem::with('menu_item_modifier.menu_item_modifier_item')->find($item_id);
		$menu_detail = $menu_item->toArray();
		$menu_detail['menu_item_status'] = $menu_item->menu->menu_closed;
		$menu_detail['menu_closed_status'] = $menu_item->menu->menu_closed_status;
		return json_encode(['menu_item' => $menu_detail]);
	}

	//menu category detail
	public function menu_category_details(Request $request)
	{
		$id = $request->id;
		$menu_category = MenuCategory::with('menu_item')->where('menu_id', $id)->get();
		return json_encode(['menu_category' => $menu_category]);
	}

	//orders store in session
	public function orders_store(Request $request)
	{
		$menu_data = $request->menu_data;
		$item_count = $request->item_count;
		$item_notes = $request->item_notes;
		$item_price = $request->item_price;
		$individual_price = $request->individual_price;
		$restaurant_id = $request->restaurant_id;

		$order_array = [];
		$count = $item_count;

		if (session('order_data') != null) {
			$order_array = session('order_data');
		}

		$order_data = array('menu_data' => $menu_data, 'restaurant_id' => $restaurant_id, 'item_notes' => $item_notes, 'item_count' => $item_count, 'item_price' => $item_price, 'individual_price' => $individual_price);

		array_push($order_array, $order_data);

		session(['order_data' => $order_array]);

		return json_encode(['last_pushed' => $order_data, 'all_order' => session('order_data')]);
	}

	//orders remove from session
	public function orders_remove(Request $request)
	{
		$order_data = $request->order_data;
		$user_details = auth()->guard('web')->user();

		if (!$user_details) {
			session()->forget('order_data');
			session(['order_data' => $order_data]);
			$order_data = get_user_order_details();

			return json_encode(['order_data' => $order_data]);
		}

		$order_item_id = array_column($order_data['items'], 'order_item_id');
		$order = OrderItem::where('order_id', $order_data['order_id'])->whereNotIn('id', $order_item_id)->get();
		foreach ($order as $key => $value) {
			$remove_order_item = OrderItemModifier::where('order_item_id',[$value->id])->get();
			foreach($remove_order_item as $modifier_item) {
				$remove_order_item_modifer = OrderItemModifierItem::whereIn('order_item_modifier_id',[$modifier_item->id])->delete();
			}
			OrderItemModifier::where('order_item_id',[$value->id])->delete();
		}
		OrderItem::where('order_id', $order_data['order_id'])->whereNotIn('id', $order_item_id)->delete();
		$order_data = get_user_order_details($order_data['restaurant_id'], $user_details->id);

		return json_encode(['order_data' => $order_data]);
	}

	public function orders_change(Request $request)
	{
		$order_item_id = $request->order_item_id;
		$order_data = $request->order_data;
		$user_details = auth()->guard('web')->user();
		$delivery_mode 	= request()->delivery_mode ?? '2';

		if (!$user_details) {
			session()->forget('order_data');
			session(['order_data' => $order_data]);
			$order_data = get_user_order_details();
			return json_encode(['order_data' => $order_data]);
		}

		foreach ($order_data['items'] as $order_item) {
			if ($order_item['order_item_id'] == $order_item_id) {
				$update_item = OrderItem::with('order_item_modifier.order_item_modifier_item')->find($order_item_id);
				foreach($update_item->order_item_modifier as $item)
				{
					$modifier = $item->order_item_modifier_item;
					foreach ($modifier as $modifier_item) {
						$modifier_item['count'] = ($modifier_item['default_count'] * $order_item['item_count']);
				$modifier_item = OrderItemModifierItem::where('id',$modifier_item['id'])->update(['count' => $modifier_item['count']]);
					}
				}

				$orderitem_modifier_ids = $update_item->order_item_modifier->pluck('id')->toArray();

				$orderitem_modifiers = OrderItemModifier::whereIn('id',$orderitem_modifier_ids);

				$update_item->quantity = $order_item['item_count'];
				
				$update_item->total_amount = $order_item['item_count'] * ($update_item->price + $orderitem_modifiers->sum('modifier_price') );
				$update_item->tax = calculate_tax(($order_item['item_count'] * $update_item->price), $update_item->menu_item->tax_percentage);
				$update_item->save();
			}
		}
		$order_data = get_user_order_details($order_data['restaurant_id'], $user_details->id,$delivery_mode);


		// Update delivery_mode
		$user_address =  UserAddress::where('user_id', $user_details->id)->default()->first();
		$user_address->delivery_mode = $delivery_mode;
		$user_address->save();

		$session_schedule_data = session('schedule_data');
		if($session_schedule_data) {
			$session_schedule_data['delivery_mode'] = $delivery_mode;
			session::put('schedule_data', $session_schedule_data);
			// logger( json_encode(session('schedule_data')) );
		}

		return json_encode(['order_data' => $order_data]);
	}

	//order history
	public function order_history()
	{
		$this->view_data['user_details'] = auth()->guard('web')->user();

		$this->view_data['order_details'] = Order::getAllRelation()->where('user_id', $this->view_data['user_details']->id)->history()->orderBy('id', 'DESC')->get();
		$this->view_data['cancel_reason'] = OrderCancelReason::where('status', 1)->where('order_cancel_reason.type',Auth::user()->type)->get();
		$this->view_data['upcoming_order_details'] = Order::getAllRelation()->where('user_id', $this->view_data['user_details']->id)->upcoming()->orderBy('id', 'DESC')->get();

		return view('orders', $this->view_data);
	}

	//order invoice

	public function order_invoice()
	{
		$order_id = request()->order_id;

		$order = Order::with(['order_item' => function ($query) {
			$query->with('menu_item','order_item_modifier.order_item_modifier_item');
		}])->find($order_id);

		$currency_symbol = Order::find($order_id)->currency->symbol;

		return json_encode(['order_detail' => $order, 'currency_symbol' => $currency_symbol]);
	}

	//promo code changes

	public function add_promo_code(Request $request)
	{
		$code=$request->code;
		$user_details = auth()->guard('web')->user();
		$promo_code_date_check = PromoCode::with('promotranslation')->where(function($query)use ($code){

			$query->whereHas('promotranslation',function($query1) use($code)
			{
				$query1->where('code',$code);

			})->orWhere('code',$code);


		})->where('start_date','<=',date('Y-m-d'))->where('end_date', '>=', date('Y-m-d'))->where('status',1)->first();
		$data['status'] = 1;
		$data['message'] = trans('api_messages.add_promo_code.promo_applied_successfully');
		if ($promo_code_date_check) {

			$user_promocode = UsersPromoCode::where('promo_code_id', $promo_code_date_check->id)->where('user_id', $user_details->id)->first();

			if ($user_promocode) {
				$data['status'] = 0;
				$data['message'] = trans('messages.profile_orders.already_applied');
			} else {
				$users_promo_code = new UsersPromoCode;
				$users_promo_code->user_id = $user_details->id;
				$users_promo_code->promo_code_id = $promo_code_date_check->id;
				$users_promo_code->order_id = 0;
				$users_promo_code->save();
			}
			$amount = promo_calculation();
			$data['order_detail_data'] = get_user_order_details($request->restaurant_id, $user_details->id);
		} else {

			$promo_code = PromoCode::with('promotranslation')->where(function($query) use($code){

			$query->whereHas('promotranslation',function($query1) use($code)
			{
				$query1->where('code',$code);

			})->orWhere('code',$code);


			})->where('end_date', '<', date('Y-m-d'))->first();

			if ($promo_code) {
				$data['status'] = 0;
				$data['message'] = trans('api_messages.add_promo_code.promo_code_expired');
			} else {
				$data['status'] = 0;
				$data['message'] = trans('api_messages.add_promo_code.invalid_code');
			}

		}
		if (isset($request->page)) {
			$class = ($data['status'] == 1) ? 'success' : 'danger';
			flash_message($class, $data['message']);
			return back();
		}
		return $data;
	}

	public function removePromoCode(Request $request)
	{
		$promo_code=$request->id;
		$user_details = auth()->guard('web')->user();
		$user_promocode = UsersPromoCode::where('promo_code_id', $promo_code)->where('user_id', $user_details->id)->delete();
		$data['status'] = 1;
		$data['message'] = trans('api_messages.add_promo_code.promo_deleted_successfully');
		$data['order_detail_data'] = get_user_order_details($request->restaurant_id, $user_details->id);
		return $data;
	}

	//confirm address check with restaurant address
	public function location_check(Request $request)
	{
		$order_id = $request->order_id;
		$restuarant_id = $request->restuarant_id;
		$city = $request->city;
		$address1 = $request->address1;
		$state = $request->state;
		$country = $request->country;
		$location = $request->location;
		$postal_code = $request->postal_code;
		$latitude = $request->latitude;
		$longitude = $request->longitude;

		$user_id = get_current_login_user_id();
		$user_address = UserAddress::where('user_id', $user_id)->where('type',2)->first();
		if ($user_address == '') {
			$user_address = new UserAddress;
		}

		$user_address->user_id = $user_id;
		$user_address->address = $location;
		$user_address->street = $address1;
		$user_address->first_address = $location;
		$user_address->second_address = $address1;
		$user_address->city = $city;
		$user_address->state = $state;
		$user_address->country = $country;
		$user_address->postal_code = $postal_code;
		$user_address->latitude = $latitude;
		$user_address->longitude = $longitude;
		$user_address->default = 1;
		$user_address->delivery_options = 0;
		$user_address->save();

		session()->put('city', $city);
		session()->put('address1', $address1);
		session()->put('state', $state);
		session()->put('country', $country);
		session()->put('location', $location);
		session()->put('postal_code', $postal_code);
		session()->put('latitude', $latitude);
		session()->put('longitude', $longitude);

		$result = check_location($order_id);

		if ($result == 1) {
			return json_encode(['success' => 'true']);
		}
		if(!$request->checkout_page) {
			$OrderDelivery = OrderDelivery::where('order_id', $order_id)->first();
			$OrderDelivery->delete($OrderDelivery->id);

			$OrderItem = OrderItem::where('order_id', $order_id)->get();
			foreach ($OrderItem as $key => $value) {
				$value->delete($OrderItem[$key]->id);
			}

			$order = Order::find($order_id);
			$order->delete($order_id);

			session::forget('order_data');
			session::forget('order_detail');
		}

		return json_encode(['success' => 'none','message'=>trans('admin_messages.sorry_this_place_not_delivery')]);
	}

	//location not found
	public function location_not_found()
	{
		return view('location_not_found');
	}
}