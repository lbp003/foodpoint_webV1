<?php

/**
 * PaymentController
 *
 * @package    GoferEats
 * @subpackage  Controller
 * @category    Payment
 * @author      Trioangle Product Team
 * @version     1.3
 * @link        http://trioangle.com
 */

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Country;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\OrderItem;
use App\Models\OrderItemModifier;
use App\Models\OrderItemModifierItem;
use App\Models\Payment;
use App\Models\Payout;
use App\Models\OrderCancelReason;
use App\Models\Restaurant;
use App\Models\RestaurantTime;
use App\Models\SiteSettings;
use App\Models\MenuItemModifierItem;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\UserPaymentMethod;
use App\Traits\FileProcessing;
use App\Traits\PlaceOrder;
use Auth;
use Session;

class PaymentController extends Controller
{
	use FileProcessing, PlaceOrder;

	// checkout detail page
	public function checkout()
	{
		$this->view_data['user_details'] = auth()->guard('web')->user();

		if (!isset($this->view_data['user_details']->id) && !session()->has('order_data')) {
			return redirect()->route('home');
		}

		$subtotal = 0;
		$tax = 0;
		if ($this->view_data['user_details']) {
			session::forget('url.intended');
			$this->view_data['payment_detail'] = UserPaymentMethod::where('user_id', $this->view_data['user_details']->id)->first();

			$this->view_data['order_id'] = Order::where('user_id', $this->view_data['user_details']->id)->status('cart')->first();
		}
		if (isset($this->view_data['user_details']->id)) {
			$already_cart = Order::where('user_id', $this->view_data['user_details']->id)->status('cart')->first();
			if ($already_cart) {
				$restaurant_id = $already_cart->restaurant_id;
			}
			else {
				return redirect()->route('home');
			}
		}
		else {
			$order_data = session::get('order_data');
			$restaurant_id = $order_data['restaurant_id'];
		}
		$id = $restaurant_id;

		$restaurant = Restaurant::find($id);

		$this->view_data['order_detail_data'] = get_user_order_details($restaurant_id, @$this->view_data['user_details']->id, @session('schedule_data')['delivery_mode']);

		if (!isset($this->view_data['user_details']->id)) {
			$this->view_data['user_id'] = '';
			$this->view_data['order_details'] = '';
			Session::put('url.intended', 'checkout');
		}

		$restaurant_time = RestaurantTime::where('restaurant_id', $restaurant_id)->first();

		$restaurant_time_data1 = 0;
		if ($restaurant_time) {
			$restaurant_time_data1 = $restaurant_time->is_available;
		}

		if ($this->view_data['order_detail_data'] == '') {
			return redirect()->route('details', $id);
		}
		// delivery mode
		// set default value
		$this->view_data['pickup_restaurant'] = $this->view_data['delivery_door'] = false;

		// get value from db
        	$delivery_mode = explode(',', $restaurant->delivery_mode);

        	// set value
        	if(in_array('1', $delivery_mode))
        		$this->view_data['pickup_restaurant'] = 1;
        	if(in_array('2', $delivery_mode))
        		$this->view_data['delivery_door'] = 2;

		$this->view_data['schedule_data'] = session('schedule_data');
		$this->view_data['restaurant_details'] = Restaurant::find($id);
		$this->view_data['staicmap'] = "https://maps.googleapis.com/maps/api/staticmap?center=".session('latitude').",".session('longitude')."&zoom=15&size=200x175&maptype=roadmap&markers=color:red%7Clabel:C%7C".session('latitude').",".session('longitude')."&key=".site_setting('google_api_key');

		$this->view_data['restaurant_logo_url'] = $this->view_data['restaurant_details']->restaurant_logo;
		return view('checkout', $this->view_data);
	}

	// web payment card details
	public function add_card_details(Request $request)
	{
		$user_details = auth()->guard('web')->user();

		$stripe_payment = resolve('App\Repositories\StripePayment');

		$payment_details = UserPaymentMethod::firstOrNew(['user_id' => $user_details->id]);

		if($request->filled('intent_id')) {
			$setup_intent = $stripe_payment->getSetupIntent($request->intent_id);
			if($setup_intent->status == 'succeeded') {

				if($payment_details->stripe_payment_method != '') {
					$stripe_payment->detachPaymentToCustomer($payment_details->stripe_payment_method);
				}

				$payment_method = $stripe_payment->getPaymentMethod($setup_intent->payment_method);
				$payment_details->stripe_intent_id = $setup_intent->id;
				$payment_details->stripe_payment_method = $setup_intent->payment_method;
				$payment_details->brand = $payment_method['card']['brand'];
				$payment_details->last4 = $payment_method['card']['last4'];
				$payment_details->save();

				return response()->json([
					'status_code' => '2',
					'status_message' => 'Successfully',
					'brand' => $payment_details->brand,
					'last4' => $payment_details->last4,
					'payment_details' => $payment_details,
				]);
			}

			return response()->json([
				'status_code' => '0',
				'status_message' => $setup_intent->status,
			]);
		}

		if(is_null($request->card_number)){
			return response()->json([
				'status_code' => '0',
				'status_message' => trans('messages.profile.card_number_valid'),
			]);
		}
		if(is_null($request->cvv_number)){
			return response()->json([
				'status_code' => '0',
				'status_message' => trans('messages.profile.card_cvv_valid'),
			]);
		}

		if(!isset($payment_details->stripe_customer_id)) {
			$stripe_customer = $stripe_payment->createCustomer($user_details->email);
			if($stripe_customer->status == 'failed') {
				return response()->json([
					'status_code' 		=> "0",
					'status_message' 	=> $stripe_customer->status_message,
				]);
			}
			$payment_details->stripe_customer_id = $stripe_customer->customer_id;
			$payment_details->save();
		}
		$customer_id = $payment_details->stripe_customer_id;

		// Check New Customer if customer not exists
		$customer_details = $stripe_payment->getCustomer($customer_id);
		if($customer_details->status == "failed" && $customer_details->status_message == "resource_missing") {
			$stripe_customer = $stripe_payment->createCustomer($user_details->email);
			if($stripe_customer->status == 'failed') {
				return response()->json([
					'status_code' 		=> "0",
					'status_message' 	=> $stripe_customer->status_message,
				]);
			}
			$payment_details->stripe_customer_id = $stripe_customer->customer_id;
			$payment_details->save();
			$customer_id = $payment_details->stripe_customer_id;
		}

		$card =  array(
            "number" 	=> $request->card_number,
            "exp_month" => $request->expire_month,
            "exp_year" 	=> $request->expire_year,
            "cvc" 		=> $request->cvv_number,
        );

		$payment_method = $stripe_payment->createPaymentMethod($card);
		if($payment_method->status == 'failed') {
			return response()->json([
				'status_code' => '0',
				'status_message' => $payment_method->status_message,
			]);
		}

		$stripe_payment->attachPaymentToCustomer($customer_id,$payment_method->id);
		$setup_intent = $stripe_payment->createSetupIntent($customer_id);

		if($setup_intent->status == 'failed') {
			return response()->json([
				'status_code' => '0',
				'status_message' => $setup_intent->status_message,
			]);
		}

		if($setup_intent->status == 'requires_payment_method') {
			$setup_intent = $stripe_payment->attachPaymentToSetupIntent($setup_intent->intent_id,$payment_method->id);
		}

		if($setup_intent->status == 'requires_confirmation') {
			return response()->json([
				'status_code' => '1',
				'status_message' => $setup_intent->status,
				'intent_client_secret' => $setup_intent->intent_client_secret,
			]);
		}

		return response()->json([
			'status_code' => '0',
			'status_message' => __('messages.api.something_went_wrong_try_again'),
		]);
	}

	public function add_cart(Request $request)
	{
		$schedule_data = session('schedule_data');
		$schedule_status = 0;
		$schedule_datetime = null;
		$modifier_price = 0;

		$menu_item_id = $request->menu_item_id;
		$restaurant_id = $request->restaurant_id;
		$quantity = $request->item_count;
		$notes = $request->item_notes;
		$menu_data = $request->menu_data;

		foreach ($menu_data['menu_item_modifier'] as $modifier) {	
			foreach ($modifier['menu_item_modifier_item'] as $modifier_item) {
				if($modifier_item['is_selected']) {
					$modifier_price += ($modifier_item['item_count'] * $modifier_item['price']);
				}
			}
		}

		if ($schedule_data['status'] == 'Schedule') {
			$schedule_status = 1;
			$schedule_datetime = $schedule_data['date'] . ' ' . $schedule_data['time'];
		}

		$user_details = auth()->guard('web')->user();
		if ($user_details) {
			$menu = MenuItem::find($menu_data['id']);
			$already_cart = Order::where('user_id', $user_details->id)->status('cart')->first();

			if ($already_cart) {
				if ($already_cart->restaurant_id != $restaurant_id) {
					$order1 = Order::where('user_id', $user_details->id)->status('cart')->where('restaurant_id', $already_cart->restaurant_id)->first();
					$order1->order_item()->delete();
					$order1->order_delivery()->delete();
					$order1->delete();
				}
			}

			$order = Order::where('user_id', $user_details->id)->where('restaurant_id', $restaurant_id)->status('cart')->first();

			$restaurant = Restaurant::find($restaurant_id);

			if ($order == '') {
				$order = new Order;
				$order->restaurant_id = $restaurant_id;
				$order->user_id = $user_details->id;
				$order->currency_code = $restaurant->currency_code;
				$order->schedule_status = $schedule_status;
				$order->schedule_time = $schedule_datetime;
				$order->status = 0;
				$order->save();
			}
			$menu_price = $menu->price;
			$total_amount = $quantity * ($menu_price + $modifier_price);
			$tax = ($total_amount * $menu->tax_percentage / 100);

			$orderitem = new OrderItem;
			$orderitem->order_id = $order->id;
			$orderitem->menu_item_id = $menu_data['id'];
			$orderitem->menu_name = $menu->name;
			$orderitem->price = $menu_price;
			$orderitem->quantity = $quantity;
			$orderitem->menu_name = $menu->name;
			$orderitem->notes = $notes;
			$orderitem->total_amount = $total_amount;
			$orderitem->modifier_price = $modifier_price;
			$orderitem->tax = $tax;
			$orderitem->save();

			foreach ($menu_data['menu_item_modifier'] as $modifier) {
				if($modifier['is_selected'] == true) {
					$orderitem_modifier = OrderItemModifier::firstOrCreate([
						'order_item_id' => $orderitem->id,
						'modifier_id' 	=> $modifier['id']
					]);
					$orderitem_modifier->modifier_name = $modifier['name'];
					$orderitem_modifier->save();
					$modifier_item_price = 0;
					foreach ($modifier['menu_item_modifier_item'] as $modifier_item) {	
						if($modifier_item['is_selected'] == true) {
							$orderitem_modifier_item = OrderItemModifierItem::firstOrCreate([
								'order_item_modifier_id' => $orderitem_modifier->id,
								'menu_item_modifier_item_id' => $modifier_item['id']
							]);

							$orderitem_modifier_item->modifier_item_name = $modifier_item['name'];
							$orderitem_modifier_item->count = $modifier_item['item_count'];
							$orderitem_modifier_item->default_count = $modifier_item['item_count'];
							$orderitem_modifier_item->price = $modifier_item['price'];
							$orderitem_modifier_item->save();
							$modifier_item_price += ($modifier_item['item_count'] * $modifier_item['price']);
						}
					}
					$orderitem_modifier->modifier_price = $modifier_item_price;
					$orderitem_modifier->save();
				}
			}
			// update order or cart sum price and tax

			$orderitem = OrderItem::where('order_id', $order->id)->get();

			$order_update = Order::find($order->id);

			$order_delivery = $order_update->order_delivery;

			if (!$order_delivery) {
				$order_delivery = new OrderDelivery;
				$order_delivery->order_id = $order_update->id;
				$order_delivery->save();
			}

			// if (site_setting('delivery_fee_type') == 0) {
			// 	$delivery_fee = site_setting('delivery_fee');
			// 	$order_update->delivery_fee = $delivery_fee;

			// 	$order_delivery->fee_type = 0;
			// 	$order_delivery->total_fare = $delivery_fee;
			// 	$order_delivery->save();

			// }
			// else {
			// 	$pickup_fare = site_setting('pickup_fare');
			// 	$drop_fare = site_setting('drop_fare');
			// 	$distance_fare = site_setting('distance_fare');

			// 	$lat1 = $order_update->user_location[0]['latitude'];
			// 	$lat2 = $order_update->user_location[1]['latitude'];
			// 	$long1 = $order_update->user_location[0]['longitude'];
			// 	$long2 = $order_update->user_location[1]['longitude'];

			// 	$result = get_driving_distance($lat1, $lat2, $long1, $long2);

			// 	$km = round(floor($result['distance'] / 1000) . '.' . floor($result['distance'] % 1000));

			// 	$delivery_fee = $pickup_fare + $drop_fare + ($km * $distance_fare);

			// 	$order_delivery->fee_type = 0;
			// 	$order_delivery->pickup_fare = $pickup_fare;
			// 	$order_delivery->drop_fare = $drop_fare;
			// 	$order_delivery->distance_fare = $distance_fare;
			// 	$order_delivery->drop_distance = $km;
			// 	$order_delivery->est_distance = $km;
			// 	$order_delivery->total_fare = $delivery_fee;
			// 	$order_delivery->save();
			// }

			

			$pickup_fare 	= 0;
			$drop_fare 		= 0;
			$distance_fare 	= $restaurant->delivery_fee;

			$lat1 	= $order_update->user_location[0]['latitude'];
			$lat2 	= $order_update->user_location[1]['latitude'];
			$long1 	= $order_update->user_location[0]['longitude'];
			$long2 	= $order_update->user_location[1]['longitude'];

			$result = get_driving_distance($lat1, $lat2, $long1, $long2);

			$km = round(floor($result['distance'] / 1000) . '.' . floor($result['distance'] % 1000));

			$delivery_fee = $pickup_fare + $drop_fare + ($km * $distance_fare);

			$order_delivery->fee_type 		= 1;
			$order_delivery->pickup_fare 	= $pickup_fare;
			$order_delivery->drop_fare 		= $drop_fare;
			$order_delivery->distance_fare 	= $distance_fare;
			$order_delivery->drop_distance 	= $km;
			$order_delivery->est_distance 	= $km;
			$order_delivery->total_fare 	= $delivery_fee;
			$order_delivery->save();


			$subtotal = number_format_change($orderitem->sum('total_amount'));

			$order_tax = $orderitem->sum('tax');
			$order_quantity = $orderitem->sum('quantity');
			$booking_percentage = SiteSettings::where('name', 'booking_fee')->first()->value;
			$booking_fee = ($subtotal * $booking_percentage / 100);

			$order_update->subtotal = $subtotal;
			$order_update->tax = $order_tax;
			$order_update->booking_fee = $booking_fee;
			$order_update->delivery_fee = $delivery_fee;
			$order_update->wallet_amount = 0;
			$order_update->owe_amount = 0;
			$order_update->save();

			$offer_amount = offer_calculation($restaurant_id, $order->id);

			$promo_amount = promo_calculation();

			$data = get_user_order_details($restaurant_id, $user_details->id);
			return response()->json([
				'success' => 'true',
				'cart_detail' => $data,
			]);
		}

		$data = $this->add_to_session_cart($restaurant_id, $quantity, $notes, $menu_data);
		session()->forget('order_data');
		session(['order_data' => $data]);
		return response()->json([
			'success' => 'true',
			'cart_detail' => $data,
		]);
	}

	public function add_to_session_cart($restaurant_id, $quantity, $notes, $menu_data)
	{
		$restaurant_detail = Restaurant::find($restaurant_id);
		$delivery_fee = get_delivery_fee($restaurant_detail->user_address->latitude, $restaurant_detail->user_address->longitude,$restaurant_id);
		
		$cart_detail = session('order_data');
		$price_sum = ($menu_data['offer_price'] > 0) ? $menu_data['offer_price'] : $menu_data['price'];
		$menu_modifier = array();
		$modifier_price = 0;
		foreach ($menu_data['menu_item_modifier'] as $key => $modifier) {	
			$item_total_price = 0;
			foreach ($modifier['menu_item_modifier_item'] as $modifier_item) {
				if($modifier_item['is_selected']) {
					$item_total_price += ($modifier_item['item_count'] * $modifier_item['price']);
				}
			}
			$modifier_price += $item_total_price;
			$menu_data['menu_item_modifier'][$key]['price'] = $item_total_price;
		}

		$price_tot = $quantity * ($price_sum + $modifier_price);
	
		$tax = calculate_tax($price_tot, $menu_data['tax_percentage']);
		$cart_detail['restaurant_id'] = @$restaurant_id;
		$cart_detail['delivery_fee'] = $delivery_fee;
		$cart_detail['tax'] = @$cart_detail['tax'] + $tax;
		$cart_detail['subtotal'] = @$cart_detail['subtotal'] + $price_tot;
		$cart_detail['booking_fee'] = get_booking_fee(@$cart_detail['subtotal']);
		$cart_detail['total_price'] = number_format_change(@$cart_detail['total_price'] + $price_tot + $tax+@$cart_detail['booking_fee']);
		$cart_detail['total_item_count'] = @$cart_detail['total_item_count'] + $quantity;
		
		$cart_detail['items'][] = array(
			'name'		=> $menu_data['name'],
			'item_notes'=> $notes,
			'item_id' 	=> $menu_data['id'],
			'item_count'=> $quantity,
			'tax' 		=> $tax,
			'item_total'=> $price_tot,
			'item_price'=> $price_sum,
			'modifier' 	=> $menu_data['menu_item_modifier'],
		);
		return $cart_detail;
	}

	//order data store
	public function place_order_details(Request $request)
	{
		$city 		= $request->confirm_address;
		$street 	= $request->street;
		$order_city = $request->city;
		$state 		= $request->state;
		$country 	= $request->country;
		$postal_code= $request->postal_code;
		$latitude 	= $request->latitude;
		$longitude 	= $request->longitude;
		$suite 		= $request->suite;
		$delivery_note = $request->delivery_note;
		$payment_method = $request->payment_method;
		$order_note = $request->order_note;

		$user_id = get_current_login_user_id();

		if ($user_id) {

			$order = Order::where('user_id', $user_id)->status('cart')->first();

			$schedule_data = session::get('schedule_data');

			$schedule_status = 0;
			$schedule_datetime = null;

			if ($schedule_data['status'] == 'Schedule') {
				$schedule_status = 1;
				$schedule_datetime = $schedule_data['date'] . ' ' . $schedule_data['time'];
			}

			$order->schedule_status = $schedule_status;
			$order->schedule_time = $schedule_datetime;
			$order->notes = ($order_note) ? $order_note : null;
			$order->payment_type = $payment_method;
			$order->save();

			$this->static_map_track($order->id);

			//user address store
			$user_address = UserAddress::where('user_id', $user_id)->first();

			if (!$user_address) {
				$user_address = new UserAddress;
			}
			$user_address->default = 1;
			$user_address->type = 0;
			$user_address->user_id 	= $user_id;
			$user_address->address 	= $city;
			$user_address->street 	= $street ?? $order_city;
			$user_address->city 	= $order_city;
			$user_address->state 	= $state;
			$user_address->postal_code = $postal_code;
			$user_address->country 	= $country;
			$user_address->latitude = $latitude;
			$user_address->longitude= $longitude;
			$user_address->apartment = $suite ?? null;
			$user_address->delivery_note = $delivery_note ?? null;

			$user_address->save();

			return json_encode(['success' => 'true', 'order' => $order, 'address' => $user_address]);
		}

		return json_encode(['success' => '', 'order' => '', 'order_item' => '', 'address' => '']);
	}

	/**
	 * Eater payment details
	 *
	 */
	public function place_order(Request $request)
	{
		$user_details = User::find(get_current_login_user_id());
		return $this->PlaceOrder($request, $user_details);
	}

	// order track
	public function order_track(Request $request)
	{
		$order_id = $request->order_id;
		$order = Order::findOrFail($order_id);
		if(Auth::user()->id != $order->user_id){
			return redirect('404');
		}
		$order_delivery = OrderDelivery::where('order_id', $order_id)->first();

		$this->view_data['map_url'] = $order_delivery->trip_path;
		$this->view_data['cancel_reason'] = OrderCancelReason::where('status', 1)->where('order_cancel_reason.type',Auth::user()->type)->get();
		$this->view_data['order_detail'] = Order::with('currency')->find($order_id);

		session::forget('order_data');
		session::forget('order_detail');

		return view('order_track', $this->view_data);
	}

	// map track
	public function static_map_track($order_id)
	{
		$order = Order::findOrFail($order_id);
		$user_id = get_restaurant_user_id($order->restaurant_id);

		$res_address = get_restaurant_address($user_id);

		$user_address = get_user_address($order->user_id);

		$origin = $res_address->latitude . ',' . $res_address->longitude;
		$destination = $user_address->latitude . ',' . $user_address->longitude;

		$map_url = getStaticGmapURLForDirection($origin, $destination);

		$directory = storage_path('app/public/images/map_image');

		if (!is_dir($directory = storage_path('app/public/images/map_image'))) {
			mkdir($directory, 0755, true);
		}

		$time = time();
		$imageName = 'map_' . $time . '.PNG';
		$imagePath = $directory . '/' . $imageName;
		if ($map_url) {
			file_put_contents($imagePath, file_get_contents($map_url));
			$this->fileSave('map_image', $order_id, $imageName, '1');
		}
	}

	/**
	 * Refund when the eater cancel the order
	 *
	 * @param Get method request inputs
	 *
	 * @return Response Json
	 */
	public function cancel_order(Request $request)
	{
		$user_details = auth()->guard('web')->user();

		$order_id = $request->order_id;

		$reason = $request->reason;
		$cancel_message = $request->message;

		$order = Order::find($order_id);

		if ($order->status == $order->statusArray['pending']) {
			$wallet_amount = $order->wallet_amount;

			if ($wallet_amount != 0) {
				$return_wallet = $this->wallet_amount($wallet_amount, $order->user_id);
			}

			if ($order->payment_type == 1) {
				try {
					$stripe_payment = resolve('App\Repositories\StripePayment');
					$payment = Payment::where('order_id', $order->id)->first();
					$amount = $payment->amount;

					$refund = $stripe_payment->refundPayment($payment->transaction_id);

					if ($refund->status != 'success') {
						flash_message('danger', $refund->status_message);
						return redirect()->route('order_track', ['order_id' => $order_id]);
					}

					$payout = new Payout;
					$payout->amount = $amount;
					$payout->transaction_id = $payment->transaction_id;
					$payout->currency_code = $refund->currency;
					$payout->order_id = $order_id;
					$payout->user_id = get_current_login_user_id();
					$payout->status = 1;
					$payout->save();
				}
				catch (\Exception $e) {
					flash_message('danger', $e->getMessage());
					return redirect()->route('order_track', ['order_id' => $order_id]);
				}
			}

			$order1 = Order::find($order->id);
			$order1->cancel_order("eater", $reason, $cancel_message);
			if ($order->payment_type == 1) {
				flash_message('success', trans('messages.profile_orders.amount_has_been_refunded'));
			}
			else {
				flash_message('success', trans('messages.profile_orders.order_canceled_successfully'));
			}
			return redirect()->route('order_track', ['order_id' => $order_id]);
		}

		flash_message('danger', trans('messages.profile_orders.you_cant_cancel_this_order'));
		return redirect()->route('order_track', ['order_id' => $order_id]);
	}
}