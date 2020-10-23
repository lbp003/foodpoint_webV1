<?php

/**
 * Eater Controller
 *
 * @package    GoferEats
 * @subpackage Controller
 * @category   Eater
 * @author     Trioangle Product Team
 * @version    1.2
 * @link       http://trioangle.com
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cuisine;
use App\Models\Currency;
use App\Models\IssueType;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\PenalityDetails;
use App\Models\PromoCode;
use App\Models\Restaurant;
use App\Models\RestaurantTime;
use App\Models\Review;
use App\Models\ReviewIssue;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\UserPaymentMethod;
use App\Models\UsersPromoCode;
use App\Models\MenuItemModifier;
use App\Models\OrderItemModifier;
use App\Models\OrderItemModifierItem;
use App\Models\Wallet;
use App\Models\Wishlist;
use App\Traits\FileProcessing;
use App\Traits\AddOrder;
use Auth;
use Carbon\Carbon;
use DB;
use JWTAuth;
use Storage;
use Stripe;
use Validator;

class EaterController extends Controller
{
	use FileProcessing,AddOrder;

	public function __construct()
	{
		parent::__construct();
	}

	protected function mapUserAddress($user_address)
	{
		return $user_address->map(function($address) {
			return [
				'id' 			=> $address->id,
				'user_id' 		=> $address->user_id,
				'address' 		=> $address->address ?? "",
				'street'		=> $address->street,
				'first_address' => $address->first_address ?? "",
				'second_address'=> $address->second_address ?? '',
				'address1' 		=> $address->address1 ?? '',
				'city' 			=> $address->city ?? '',
				'state' 		=> $address->state ?? '',
				'postal_code' 	=> $address->postal_code ?? '',
				'country_code' 	=> $address->country_code ?? '',
				'latitude' 		=> $address->latitude ?? '',
				'longitude' 	=> $address->longitude ?? '',
				'default' 		=> $address->default ?? 0,
				'delivery_options' => $address->delivery_options ?? 0,
				'order_type' 	=> $address->order_type ?? 0,

				'delivery_mode' => $address->delivery_mode ?? '2',
				'delivery_mode_text' => $address->delivery_mode_text ?? '',

				'delivery_time' => $address->delivery_time ?? "",
				'apartment' 	=> $address->apartment ?? "",
				'delivery_note' => $address->delivery_note ?? "",
				'type' 			=> $address->type ?? 0,
				'static_map' 	=> $address->static_map ?? "",
				'default_timezone' => $address->default_timezone,
			];
		});
	}

	/**
	 * Restaurant Details display to Eater
	 *
	 * @param Get method request inputs
	 *
	 * @return Response Json
	 */
	public function home(Request $request)
	{
		$default_currency_code=DEFAULT_CURRENCY;
		$default_currency_symbol=Currency::where('code',DEFAULT_CURRENCY)->first()->symbol;	
		$default_currency_symbol=html_entity_decode($default_currency_symbol);

		$user_details = '';
		$already_cart = '';
		if(isset(request()->token)) {
			$user_details = JWTAuth::parseToken()->authenticate();
			$already_cart = Order::where('user_id', $user_details->id)->status('cart')->first();
		}

		$cuisine = Cuisine::where('is_dietary', '1')->where('status', 1)->get();
		$cuisine = $cuisine->map(function ($item) {
			return [
				'id' => $item['id'],
				'name' => $item['name'],
				'dietary_icon' => $item['dietary_icon'],
			];
		})->toArray();

		$address_details = $this->address_details();

		$perpage = 7;

		$latitude = $address_details['latitude'];
		$longitude = $address_details['longitude'];
		$order_type = $address_details['order_type'];
		$delivery_time = $address_details['delivery_time'];

		
		$restaurant_details = (object) [];

		if($already_cart) {
			$available = Restaurant::find($already_cart->restaurant_id);
			$restaurant_details = ['image' => $available->banner, 'name' => $available->name];
		}

		$address = UserAddress::where('user_id', @$user_details->id)->default()->first();
		if($user_details) {
			if (isset($request->order_type)) {
				$address->order_type = $request->order_type;
				$address->save();
			}
			if (isset($request->delivery_mode)) {
				$address->delivery_mode = $request->delivery_mode;
				$address->save();
			}
		}

		//more Restaurant
		$date = \Carbon\Carbon::today();

		$common = User::with(
			['restaurant' => function ($query) use ($latitude, $longitude, $date) {
				$query->with(['restaurant_cuisine', 'review', 'restaurant_offer', 'user_address', 'restaurant_time']);
			}]
		)->Type('restaurant')
		->whereHas('restaurant', function ($query) use ($latitude, $longitude, $address) {
			$query->location($latitude, $longitude)
				->where(function($q) use($address) {
					if(isset($address))
						$q->DeliveryMode($address->delivery_mode);
					else
						$q->DeliveryMode('2');
				})
				->whereHas('restaurant_time', function ($query) {

				});
		})->status();

		$more_restaurant = (clone $common)->paginate($perpage);

		$count_restaurant = $more_restaurant->lastPage();

		$more_restaurant = $this->common_map($more_restaurant);

		// New restaurant
		$date = \Carbon\Carbon::today()->subDays(10);

		$new_restaurant = (clone $common)->where('created_at', '>=', $date)->paginate($perpage);

		$count_new_restaurant = $new_restaurant->lastPage();

		$new_restaurant = $this->common_map($new_restaurant);
		
		// Restaurant Offer
		$restaurant_offer = (clone $common)->whereHas(
			'restaurant',
			function ($query) use ($date) {
				$query->whereHas(
					'restaurant_offer',
					function ($query) use ($date) {
						$query->where('start_date', '<=', $date)->where('end_date', '>=', $date);
					}
				);
			}
		)->paginate($perpage);

		$count_offer = $restaurant_offer->lastPage();

		$restaurant_offer = $restaurant_offer->map(
			function ($item) {
				return [
					'restaurant_id' => $item['restaurant']['id'],
					'name' => $item['restaurant']['name'],
					'banner' => $item['restaurant']['banner'],
					'title' => $item['restaurant']['restaurant_offer'][0]['offer_title'],
					'description' => $item['restaurant']['restaurant_offer'][0]['offer_description'],
					'percentage' => $item['restaurant']['restaurant_offer'][0]['percentage'],
				];
			}
		);

		// Under prepartion time min
		$under = (clone $common)->get();
		$count_under = 0;

		$convert_mintime = 0;
		if (count($under) != 0) {

			$under = $under->sortBy(
				function ($unders) {
					return $unders->restaurant->convert_mintime;
				}
			)->values();

			$max_time = $under[0]['restaurant']['max_time'];

			$convert_mintime = $under[0]['restaurant']['convert_mintime'];

			$under_minutes = (clone $common)->whereHas(
				'restaurant',
				function ($query) use ($max_time) {
					$query->where('max_time', $max_time);
				}
			)->paginate($perpage);

			$count_under = $under_minutes->lastPage();

			$under_minutes = $this->common_map($under_minutes);
		}
		
		// Wishlist

		$wish = Wishlist::selectRaw('*,restaurant_id as ids, (SELECT count(restaurant_id) FROM wishlist WHERE restaurant_id = ids) as count')->with(

			['restaurant' => function ($query) use ($latitude, $longitude) {

				$query->with(['restaurant_cuisine', 'review', 'user', 'restaurant_time', 'restaurant_offer']);
			}]
		)->whereHas('restaurant', function ($query) use ($latitude, $longitude) {

			$query->UserStatus()->location($latitude, $longitude)->whereHas('restaurant_time', function ($query) {

			});

		});

		if($user_details) {
			$wishlist = (clone $wish)->where('user_id', $user_details->id)->paginate($perpage);
			$count_fav = $wishlist->lastPage();
			$wishlist = $this->common_map($wishlist);
		}		
		else {
			$wishlist = [];
			$count_fav = 0;
		}

		// Popular Restaurant
		$popular = (clone $wish)->groupBy('restaurant_id')->orderBy('count', 'desc')
		->paginate($perpage);

		$count_popular = $popular->lastPage();

		$popular = $this->common_map($popular);

		$more_restaurant = (count($more_restaurant) > 0) ? $more_restaurant->toArray() : array(); // more restaurant
		$fav = (count($wishlist) > 0) ? $wishlist->toArray() : array(); // favourite restaurant
		$under_minutes = (count($under) > 0) ? $under_minutes->toArray() : array();
		$restaurant_offer = (count($restaurant_offer) > 0) ? $restaurant_offer->toArray() : array();
		$new_restaurant = (count($new_restaurant) > 0) ? $new_restaurant->toArray() : array();

		if($user_details)
			$wallet = Wallet::where('user_id', $user_details->id)->first();


		return response()->json([
			'status_code' => '1',
			'status_message' => "Success",
			'under_time' => $convert_mintime,

			'More Restaurant' => $more_restaurant,

			'Favourite Restaurant' => $fav,

			'Popular Restaurant' => $popular,

			'New Restaurant' => $new_restaurant,

			'Under Restaurant' => $under_minutes,

			'Restaurant Offer' => $restaurant_offer,

			'more_count' => $count_restaurant,

			'fav_count' => $count_fav,

			'popular_count' => $count_popular,

			'under_count' => $count_under,

			'offer_count' => $count_offer,

			'new_count' => $count_new_restaurant,

			'wallet_amount' => isset($wallet->amount) ? $wallet->amount : 0,

			'wallet_currency' => isset($wallet->currency_code) ? $wallet->currency_code : DEFAULT_CURRENCY,

			'cart_details' => $restaurant_details,

			'home_categories' => [trans('api_messages.home.more_restaurant'), trans('api_messages.home.favourite_restaurant'), trans('api_messages.home.popular_restaurant'), trans('api_messages.home.new_restaurant'), trans('api_messages.home.under_restaurant')],

			'cuisine' => $cuisine,
			'default_currency_code'=>$default_currency_code,
			'default_currency_symbol'=>$default_currency_symbol,

			'delivery_mode'			=> $address->delivery_mode ?? '2',
			'delivery_mode_text'	=> $address->delivery_mode_text ?? trans('admin_messages.delievery_door'),
		]);
	}
	/**
	 * API for Ios
	 *
	 * @return Response Json response with status
	 */
	public function ios(Request $request)
	{

		if(isset($_POST['token']))
			$user_details =  JWTAuth::toUser($_POST['token']);
		else
			$user_details = JWTAuth::parseToken()->authenticate();

		$request = request();

		$order = Order::getAllRelation()->where('id', $request->order_id)->first();

		$rating = str_replace('\\', '', $request->rating);

		$rating = json_decode($rating);

		$order_id = $order->id;

		$food_item = $rating->food;

		//Rating for Menu item
		if ($food_item) {

			foreach ($food_item as $key => $value) {

				$review = new Review;
				$review->order_id = $order_id;
				$review->type = $review->typeArray['user_menu_item'];
				$review->reviewer_id = $user_details->id;
				$review->reviewee_id = $value->id;
				$review->is_thumbs = $value->thumbs;
				$review->order_item_id = $value->order_item_id;
				$review->comments = $value->comment ?: "";
				$review->save();

				if ($value->reason) {
					$issues = explode(',', $value->reason);
					if ($request->thumbs == 0 && count($value->reason)) {
						foreach ($issues as $issue_id) {
							$review_issue = new ReviewIssue;
							$review_issue->review_id = $review->id;
							$review_issue->issue_id = $issue_id;
							$review_issue->save();
						}
					}

				}

			}

		}

		//Rating for driver

		if (count(get_object_vars($rating->driver)) > 0) {

			$review = new Review;
			$review->order_id = $order_id;
			$review->type = $review->typeArray['user_driver'];
			$review->reviewer_id = $user_details->id;
			$review->reviewee_id = $order->driver_id;
			$review->is_thumbs = $rating->driver->thumbs;
			$review->comments = $rating->driver->comment ?: "";
			$review->save();

			if ($rating->driver->reason) {
				$issues = explode(',', $rating->driver->reason);
				if ($rating->driver->thumbs == 0 && count($issues)) {
					foreach ($issues as $issue_id) {
						$review_issue = new ReviewIssue;
						$review_issue->review_id = $review->id;
						$review_issue->issue_id = $issue_id;
						$review_issue->save();
					}
				}

			}

		}

		//Rating for Restaurant
		if (count(get_object_vars($rating->restaurant)) > 0) {
			$review = new Review;
			$review->order_id = $order_id;
			$review->type = $review->typeArray['user_restaurant'];
			$review->reviewer_id = $user_details->id;
			$review->reviewee_id = $order->restaurant_id;
			$review->rating = $rating->restaurant->thumbs;
			$review->comments = $rating->restaurant->comment ?: "";
			$review->save();
		}
		return response()->json([
			'status_code' => '1',
			'status_message' => 'Updated Successfully',
		]);
	}

	/**
	 * API for Search
	 *
	 * @return Response Json response with status
	 */
	public function categories(Request $request)
	{
		$top_cuisine = Cuisine::where('is_top', 1)->get();
		$more_cuisine = Cuisine::where('is_top', 0)->get();

		return response()->json([
			'status_code' => '1',
			'status_message' => "Success",
			'top_category' => $top_cuisine,
			'category' => $more_cuisine,
		]);
	}

	public function search(Request $request)
	{
		$user_details ='';
		if(request()->token) {
			$user_details = JWTAuth::parseToken()->authenticate();
		}

		$address_details = $this->address_details();
		return restaurant_search($user_details, $address_details, $request->keyword);
	}

	/**
	 * API for An Restaurant Details
	 *
	 * @return Response Json response with status
	 */
	public function get_restaurant_details(Request $request)
	{
		$user_details = '';
		
		if($request->has('token')) {
			$user_details = JWTAuth::parseToken()->authenticate();
			if (isset($request->order_type)) {
				$address = UserAddress::where('user_id', $user_details->id)->default()->first();
				$address->order_type = $request->order_type;
				$address->save();
			}
		}


		$rules = array(
			'restaurant_id' => 'required',
		);
		$messages = array(
			'required'                => ':attribute '.trans('api_messages.register.field_is_required').'', 
		);

		$attributes = array(
			'restaurant_id' => trans('api_messages.restaurant.restaurant_id'),
		);

		$validator = Validator::make($request->all(), $rules,$messages, $attributes);
		if ($validator->fails()) {
			return response()->json([
            	'status_code' => '0',
            	'status_message' => $validator->messages()->first()
            ]);
		}
		$address_details = $this->address_details();

		$latitude = $address_details['latitude'];
		$longitude = $address_details['longitude'];
		$order_type = $address_details['order_type'];
		$delivery_time = $address_details['delivery_time'];

		$date = \Carbon\Carbon::today();

		$restaurant_details = Restaurant::with(
			[
				'restaurant_cuisine', 'review',
				'restaurant_preparation_time',
				'restaurant_offer',
				'restaurant_time',
				'restaurant_menu' => function ($query) {
					$query->with(
						['menu_category' => function ($query) {
							$query->with(
								['menu_item' => function ($query) {
									// 
								}]
							)->has('menu_item');
						}]
					)->has('menu_category.menu_item');
				},

				'file' => function ($queryB) {
					$queryB->where('type', '4');
				},

			]
		)->where('id', $request->restaurant_id)->UserStatus()->location($latitude, $longitude)->get();

		$restaurant_details = $restaurant_details->mapWithKeys(
			function ($item) use ($user_details, $delivery_time, $order_type) {
				$restaurant_cuisine = $item['restaurant_cuisine']->map(
					function ($item) {
						return $item['cuisine_name'];
					}
				)->toArray();
				$wishlist = 0;
				if(request()->token)
				{
					$wishlist = $item->wishlist($user_details->id, $item['id']);
				}
				$open_time = $item['restaurant_time']['start_time'];
 
				return [

					'order_type' => $order_type,
					'delivery_time' => $delivery_time,
					'restaurant_id' => $item['id'],
					'name' => $item['name'],
					'category' => implode(',', $restaurant_cuisine),
					'banner' => $item['banner'],
					'min_time' => $item['convert_mintime'],
					'max_time' => $item['convert_maxtime'],
					'wished' => $wishlist ,
					'status' => $item['status'],
					'restaurant_menu' => $item['restaurant_menu'],
					'restaurant_rating' => $item['review']['restaurant_rating'],
					'price_rating' => $item['price_rating'],
					'average_rating' => $item['review']['average_rating'],
					'restaurant_closed' => $item['restaurant_time']['closed'],
					'restaurant_open_time' =>$open_time,
					'restaurant_next_time' => $item['restaurant_next_opening'],

					'restaurant_offer' => $item['restaurant_offer']->map(

						function ($item) {

							return [

								'title' => $item->offer_title,
								'description' => $item->offer_description,
								'percentage' => $item->percentage,

							];
						}
					),

				];
			}
		);

		$restaurant_details = $restaurant_details->toArray();

		if (count($restaurant_details) > 0) {

			return response()->json([
				'status_code' => '1',
				'status_message' => trans('api_messages.success'),
				'restaurant_details' => $restaurant_details,
			]);
		}

		$restaurant = Restaurant::find($request->restaurant_id);
		$restaurant_name = $restaurant->name;
		$restaurant_cuisine = $restaurant->restaurant_cuisine[0]['cuisine_name'];

		$check_address = check_restaurant_location('', $latitude, $longitude, $request->restaurant_id);

		if ($restaurant->status == 0 || $check_address == 0) {
			return response()->json([
				'status_code' => '2',
				'status_message' => trans('api_messages.restaurant.unavailable'),
				'messages' => trans('api_messages.restaurant.it_look_like') . $restaurant_name . trans('api_messages.restaurant.close_enough'),
				'cuisine' => $restaurant_cuisine,
			]);
		}

		return response()->json([
			'status_code' => '3',
			'status_message' => trans('api_messages.restaurant.restaurant_inactive'),
			'messages' =>trans('api_messages.restaurant.it_look_like'). $restaurant_name . trans('api_messages.restaurant.currently_unavailable'),
			'cuisine' => $restaurant_cuisine,
		]);
	}

	public function get_menu_item_addon(Request $request)
	{
		if($request->has('token')) {
			$user_details = JWTAuth::parseToken()->authenticate();
		}

		$rules = array(
			'menu_item_id'	=> 'required',
		);
		
		$attributes = array(
			'restaurant_id' => trans('api_messages.restaurant.restaurant_id'),
			'menu_item_id'	=> trans('api_messages.restaurant.menu_item_id'),
		);
		$messages = array(
			'required' => ':attribute '.trans('api_messages.register.field_is_required'),
		);

		$validator = Validator::make($request->all(), $rules,$messages,$attributes);

		if ($validator->fails()) {
			return response()->json([
            	'status_code' => '0',
            	'status_message' => $validator->messages()->first()
            ]);
		}

		//To get Menu Item Addon
		$menu_item_addon = MenuItemModifier::with('menu_item_sub_addon')->where('menu_item_id',$request->menu_item_id)->get();

		$menu_item_addon = $menu_item_addon->map(function($menu_modifier) {
			$menu_item_sub_addon = $menu_modifier->menu_item_sub_addon->map(function($menu_modifier_item) {
				return [
					'id' 		=> $menu_modifier_item->id,
					'name' 		=> $menu_modifier_item->name,
					'price' 	=> $menu_modifier_item->price,
					'is_visible'=> $menu_modifier_item->is_visible,
					'count' 	=> $menu_modifier_item->count,
					'menu_item_modifier_id' 	=> $menu_modifier_item->menu_item_modifier_id,
					'is_select' => $menu_modifier_item->is_select,
				];
			})->toArray();
			return [
				'id' 			=> $menu_modifier->id,
				'menu_item_id' 	=> $menu_modifier->menu_item_id,
				'name' 			=> $menu_modifier->name,
				'count_type' 	=> $menu_modifier->count_type,
				'is_multiple'	=> $menu_modifier->is_multiple,
				'min_count' 	=> $menu_modifier->min_count,
				'max_count' 	=> $menu_modifier->max_count,
				'count' 		=> collect($menu_item_sub_addon)->sum('count'),
				'is_required' 	=> $menu_modifier->is_required,
				'is_selected' 	=> $menu_modifier->is_selected,
				'menu_item_sub_addon'	=> $menu_item_sub_addon,
			];
		});

		return response()->json([
			'status_code' => '1',
			'status_message' => 'Success',
			'menu_item_addon' => $menu_item_addon,
		]);

	}

	/**
	 * API for Add Promo details
	 *
	 * @return Response Json response with status
	 */
	public function add_promo_code(Request $request)
	{
		$user_details = JWTAuth::parseToken()->authenticate();

		$rules = array(
			'code' => 'required',
		);
		$messages = array(
			'required'                => ':attribute '.trans('api_messages.register.field_is_required'),
		);
		$attributes = array(
			'code' => trans('api_messages.add_promo_code.code')
		);
		$validator = Validator::make($request->all(), $rules,$messages,$attributes);

		if ($validator->fails()) {
			return response()->json([
            	'status_code' => '0',
            	'status_message' => $validator->messages()->first()
            ]);
		}

		$code=$request->code;
		$promo_code_date_check = PromoCode::with('promotranslation')->where(function($query) use($code){

			$query->whereHas('promotranslation',function($query1) use($code)
			{
				$query1->where('code',$code);
			})->orWhere('code',$code);

		})->where('start_date','<=',date('Y-m-d'))->where('end_date', '>=', date('Y-m-d'))->first();

		if ($promo_code_date_check) {

			$user_promocode = UsersPromoCode::where('promo_code_id', $promo_code_date_check->id)->where('user_id', $user_details->id)->first();

			if ($user_promocode) {
				return ['status_code' => '0', 'status_message' => trans('api_messages.add_promo_code.promo_code_already_applied')];
			} else {
				$users_promo_code = new UsersPromoCode;
				$users_promo_code->user_id = $user_details->id;
				$users_promo_code->promo_code_id = $promo_code_date_check->id;
				$users_promo_code->order_id = 0;
				$users_promo_code->save();
			}

			$user_promocode = UsersPromoCode::WhereHas(
				'promo_code',
				function ($q) {
				}
			)->where('user_id', $user_details->id)->where('order_id', '0')->get();

			$final_promo_details = [];

			foreach ($user_promocode as $row) {
				if (@$row->promo_code) {
					$promo_details['id'] = $row->promo_code->id;
					$promo_details['price'] = $row->promo_code->price;
					$promo_details['type'] = $row->promo_code->promo_type;
					$promo_details['percentage'] = $row->promo_code->percentage;
					$promo_details['code'] = $row->promo_code->code;
					$promo_details['expire_date'] = $row->promo_code->end_date;
					$final_promo_details[] = $promo_details;
				}
			}

			$user = array('promo_details' => $final_promo_details, 'status_message' => trans('api_messages.add_promo_code.promo_applied_successfully'), 'status_code' => '1');
			return response()->json($user);
		}

		$promo_code = PromoCode::with('promotranslation')->where(function($query)use ($code){

			$query->whereHas('promotranslation',function($query1) use($code)
			{
				$query1->where('code',$code);

			})->orWhere('code',$code);

		})->where('end_date', '<', date('Y-m-d'))->first();

		if ($promo_code) {
			return ['status_code' => '0', 'status_message' => trans('api_messages.add_promo_code.promo_code_expired')];
		}
		return ['status_code' => '0', 'status_message' => trans('api_messages.add_promo_code.invalid_code')];
	}

	/**
	 * API for Promo details
	 *
	 * @return Response Json response with status
	 */
	public function get_promo_details(Request $request)
	{
		$user_details = JWTAuth::parseToken()->authenticate();

		$user_promocode = UsersPromoCode::WhereHas(
			'promo_code',
			function ($q) {
			}
		)->where('user_id', $user_details->id)->where('order_id', '0')->get();

		$final_promo_details = [];

		foreach ($user_promocode as $row) {
			if (@$row->promo_code) {
				$promo_details['id'] = $row->promo_code->id;
				$promo_details['price'] = $row->promo_code->price;
				$promo_details['type'] = $row->promo_code->promo_type;
				$promo_details['percentage'] = $row->promo_code->percentage;
				$promo_details['code'] = $row->promo_code->code;
				$promo_details['expire_date'] = $row->promo_code->end_date;
				$final_promo_details[] = $promo_details;
			}
		}
		$user = array('promo_details' => $final_promo_details, 'status_message' =>trans('api_messages.success'), 'status_code' => '1');
		return response()->json($user);
	}

	public function get_location(Request $request)
	{
		$user_details = JWTAuth::parseToken()->authenticate();

		$address = UserAddress::where('user_id', $user_details->id)
			->orderBy('type', 'ASC')
			->get()
			->map(function ($val) {
				$val->first_address = str_replace('', Null, $val->first_address);  
				$val->second_address = str_replace('', Null, $val->second_address);
				$val->apartment = str_replace('', Null, $val->apartment);
				$val->street = str_replace('', Null, $val->street);
				$val->postal_code = str_replace('', Null, $val->postal_code);
				$val->city = str_replace('', Null, $val->city);
				$val->address = str_replace('', Null, $val->address);
				$val->address1 = str_replace('', Null, $val->address1);
				$val->delivery_options = $val->delivery_options ?? 0;
				$val->delivery_note = $val->delivery_note ?? "";
			return $val;
		});

		$user = array(
			'status_message' => 'Success',
			'status_code' => '1',
			'user_address' => $address,
		);

		return response()->json($user);
	}

	/**
	 * API for Set Save Location
	 *
	 * @return Response Json response with status
	 */
	public function saveLocation(Request $request)
	{
		$user_details = JWTAuth::parseToken()->authenticate();

		$already_cart = Order::where('user_id', $user_details->id)->status('cart')->first();

		if ($already_cart) {
			$check_address = check_restaurant_location($already_cart['id'], $request->latitude, $request->longitude);

			if ($check_address == 0) {
				$address = UserAddress::where('user_id', $user_details->id)->orderBy('type', 'ASC')->get();

				return response()->json(['status_message' => 'Restaurant unavailable', 'status_code' => '2', 'user_address' => $check_address]);
			}
		}

		if($request->type == 2) {
			UserAddress::where('user_id', $user_details->id)->update(['default' => 0]);
		}

		$address = UserAddress::where('user_id', $user_details->id)->where('type', $request->type)->first();

		if ($request->type == 2 || optional($address)->default == 1) {
			$default = 1;
		}
		else {
			$add = UserAddress::where('user_id', $user_details->id)->where('default','!=',1)
			->update(['default' => 0]);

			if(!$add) {
				$default =0;
			}
		}

		if ($address == '') {
			$address = new UserAddress;
			$address->user_id = $user_details->id;
		}

		$address->street = $request->street;
		$address->city = $request->city;
		$address->state = $request->state;
		$address->first_address = $request->first_address;
		$address->second_address = $request->second_address;
		$address->postal_code = $request->postal_code;
		$address->country = $request->country;
		$address->country_code = $request->country_code;
		$address->type = $request->type;		
		$address->default = $default;
		$address->apartment = $request->apartment;
		$address->delivery_note = $request->delivery_note ?? '';
		$address->delivery_options = $request->delivery_options ?? '';
		$address->order_type = $request->order_type ?? '';

		$address->delivery_mode = $request->delivery_mode ?? '2';

		$address->delivery_time = $request->delivery_time ?? '';
		$address->latitude = $request->latitude;
		$address->longitude = $request->longitude;
		$address->address = $request->address;
		$address->save();

		$address_details= UserAddress::where('user_id', $user_details->id)->orderBy('type', 'ASC')->get();

		$user = array(
			'status_code' 	=> '1',
			'status_message'=> 'Success',
			'user_address' 	=> $address_details,
		);

		return response()->json($user);
	}

	/**
	 * API for Set Default Location
	 *
	 * @return Response Json response with status
	 */
	public function defaultLocation(Request $request)
	{
		$user_details = JWTAuth::parseToken()->authenticate();

		$rules = [
			'default' => 'required|exists:user_address,type,user_id,' . $user_details->id,
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails()) {
			return response()->json([
				'status_code' => '0',
				'status_message' => $validator->messages()->first(),
			]);
		}

		$add = UserAddress::where('type', $request->default)->where('user_id', $user_details->id)->first();

		$already_cart = Order::where('user_id', $user_details->id)->status('cart')->first();

		if ($already_cart) {
			$check_address = check_restaurant_location($already_cart['id'], $add->latitude, $add->longitude);

			if ($check_address == 0) {
				$address = UserAddress::where('user_id', $user_details->id)->orderBy('type', 'ASC')->get();

				return response()->json(['status_message' => 'Restaurant unavailable', 'status_code' => '2', 'user_address' => $check_address]);
			}
		}

		UserAddress::where('default', 1)->where('user_id', $user_details->id)->update(['default' => 0]);

		$user_address = UserAddress::where('user_id', $user_details->id)->where('type', $request->default)->first();
		$user_address->default = 1;
		$user_address->order_type = $request->order_type ?? '';

		$user_address->delivery_mode = $request->delivery_mode ?? '2';
		
		$user_address->delivery_time = $request->delivery_time ?? '';
		$user_address->save();

		$user = array(

			'status_message' => 'Success',

			'status_code' => '1',

		);

		return response()->json($user);
	}

	/**
	 * API for Remove Location
	 *
	 * @return Response Json response with status
	 */
	public function remove_location(Request $request)
	{
		$user_details = JWTAuth::parseToken()->authenticate();

		$remove = UserAddress::where('type', $request->type)->where('user_id', $user_details->id)->first();

		if ($remove) {

			$remove->delete();

			if ($remove->default == 1) {

				$update_default = UserAddress::where('user_id', $user_details->id)->first();
				$update_default->default = 1;
				$update_default->save();

			}

		}

		$address = UserAddress::where('user_id', $user_details->id)->get();

		$user = array(

			'status_message' => 'Success',

			'status_code' => '1',

			'user_address' => $address,

		);

		return response()->json($user);
	}

	/**
	 * API for Wishlist
	 *
	 * @return Response Json response with status
	 */
	public function add_wish_list(Request $request)
	{

		$user_details = JWTAuth::parseToken()->authenticate();

		$user = User::where('id', $user_details->id)->first();

		if (isset($user)) {
			$restaurant_id = $request->restaurant_id;

			$wishlist = Wishlist::where('user_id', $user_details->id)->where('restaurant_id', $restaurant_id)->first();

			if (isset($wishlist)) {

				$wishlist->delete();

				return response()->json(
					[

						'status_message' => "unwishlist Success",

						'status_code' => '1',

					]
				);
			} else {
				$wishlist = new Wishlist;
				$wishlist->restaurant_id = $restaurant_id;
				$wishlist->user_id = $user_details->id;
				$wishlist->save();

				return response()->json([
					'status_message' => "wishlist Success",
					'status_code' => '1',
				]);
			}
		} else {
			return response()->json(
				[

					'status_message' => "Invalid credentials",

					'status_code' => '0',

				]
			);
		}
	}

	/**
	 * API for update eater profile details
	 *
	 * @return Response Json response with status
	 */
	public function update_profile(Request $request)
	{

		$user_details = JWTAuth::parseToken()->authenticate();

		$rules = array(

			'first_name' => 'required',
			'last_name' => 'required',
			'email' => 'required',

		);

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails()) {
			$error = $validator->messages()->toArray();

			foreach ($error as $er) {
				$error_msg[] = array($er);
			}
			return ['status_code' => '0', 'status_message' => $error_msg['0']['0']['0']];
		} else {
			$user_check = User::where('id', $user_details->id)->first();

			if (isset($user_check)) {
				User::where('id', $user_details->id)->update(
					['name' => html_entity_decode($request->first_name . '~' . $request->last_name), 'email' => html_entity_decode($request->email),
				]
			);

				$user = User::where('id', $user_details->id)->first();

				return response()->json(
					[

						'status_message' => 'Updated Successfully',

						'status_code' => '1',

						'name' => $user->name,

						'mobile_number' => $user->mobile_number,

						'country_code' => $user->country_code,

						'email_id' => $user->email,

						'profile_image' => $user->eater_image,

					]
				);
			} else {
				return response()->json(
					[

						'status_message' => "Invalid credentials",

						'status_code' => '0',

					]
				);
			}
		}
	}

	/**
	 * API for Eater image
	 *
	 * @return Response Json response with status
	 */
	public function upload_image(Request $request)
	{

	   $user_details = JWTAuth::parseToken()->authenticate();
	

		$rules = array('image' => 'required');

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails()) {

			$error = $validator->messages()->toArray();

			foreach ($error as $er) {
				$error_msg[] = array($er);
			}

			return ['status_code' => '0', 'status_message' => $error_msg['0']['0']['0']];

		} else {

			$user_check = User::where('id', $user_details->id)->first();

			if (isset($user_check)) {

				$file = $request->file('image');

				$file_path = $this->fileUpload($file, 'public/images/eater');

				$this->fileSave('eater_image', $user_details->id, $file_path['file_name'], '1');
				$orginal_path = Storage::url($file_path['path']);

				$user = User::where('id', $user_details->id)->first();

				return response()->json(
					[

						'status_message' => 'Updated Successfully',

						'status_code' => '1',

						'name' => $user->name,

						'mobile_number' => $user->mobile_number,

						'country_code' => $user->country_code,

						'email_id' => $user->email,

						'profile_image' => $user->eater_image,

					]
				);
			} else {

				return response()->json(
					[

						'status_message' => "Invalid credentials",

						'status_code' => '0',

					]
				);
			}
		}
	}

	/**
	 * API for Add to cart
	 *
	 * @return Response Json response with status
	 */
	public function add_to_cart(Request $request)
	{
		$data =  $this->add_cart_item($request,1);

		if($data['status_code'] != 1) {
			return response()->json([
				'status_code' 	 => $data['status_code'],
				'status_message' => $data['status_message'],
			]);
		}

		return response()->json([
			'status_code' => '1',
			'status_message' => 'Updated Successfully',
			'subtotal' => $data['subtotal'],
			'quantity' => intval($data['quantity']),
		]);
	}

	/**
	 * API for view cart
	 *
	 * @return Response Json response with status
	 */
	public function view_cart(Request $request)
	{
		$user_details = JWTAuth::parseToken()->authenticate();
		$address_details = $this->address_details();
		$order_type = $address_details['order_type'];
		$delivery_time = $address_details['delivery_time'];

		//Check Cart
		$cart_order = Order::getAllRelation()->where('user_id', $user_details->id)->status('cart')->first();
		if (!$cart_order) {
			return response()->json([
				'status_code' => '2',
				'status_message' => trans('api_messages.cart.cart_empty'),
			]);
		}
		// Order menu Available or not
		$date = ($order_type == 0) ?  date('Y-m-d H:i') : $delivery_time;
		$check_menu = check_menu_available($cart_order->id, $date);
		
		if (isset($check_menu['status']) && $check_menu['status'] == false) {
			return response()->json([
				'status_code' => '4',
				'status_message' => $check_menu['status_message'],
			]);
		}
		else if (count($check_menu) > 0 && !isset($check_menu['status'])) {
			return response()->json([
				'status_code' => '3',
				'status_message' => trans('api_messages.cart.item_not_available'),
				'unavailable' => $check_menu,
			]);
		}

		// Update delivery_mode
		$user_address = UserAddress::where('user_id', $user_details->id)->where('default', 1)->first();
		if(isset($request->delivery_mode)) {
			$user_address->delivery_mode = $request->delivery_mode ? $request->delivery_mode : 1;
			$user_address->save();
		}

		$delivery_address = UserAddress::where('user_id', $user_details->id)->default()->limit(1)->get();
		$delivery_address_data = $this->mapUserAddress($delivery_address)->first();
		$delivery_address = $delivery_address->first();
		//check address
		if (!$delivery_address) {
			$delivery_address = '';
			return response()->json([
				'status_code' => '0',
				'status_message' => trans('api_messages.cart.address_empty'),
			]);
		}

		$check_address = check_location($cart_order['id']);
		if ($check_address == 0) {
			return response()->json([
				'status_code' => '0',
				'status_message' => trans('api_messages.cart.restaurant_unavailable'),
			]);
		}

		$update_order = Order::with('restaurant.user_address')->find($cart_order->id);

		$delivery_mode = Restaurant::where('id', $update_order->restaurant->id)->first();
		$mode = explode(',',$delivery_mode->delivery_mode);

		$update_order->schedule_status = $order_type;
		$update_order->schedule_time = $delivery_time;
		$restaurant_address = optional($update_order->restaurant)->user_address;
		if($restaurant_address != '') {
			// $delivery_type 		= site_setting('delivery_fee_type');
			// if($delivery_type == '1') {
			// 	$pickup_fare 		= site_setting('pickup_fare');
			// 	$drop_fare 			= site_setting('drop_fare');
			// 	$distance_fare 		= site_setting('distance_fare');
			// 	$distance 			= get_driving_distance($delivery_address->latitude,$restaurant_address->latitude,$delivery_address->longitude,$restaurant_address->longitude);
			// 	$km 				= round(floor($distance['distance'] / 1000) . '.' . floor($distance['distance'] % 1000));
			// 	$delivery_fee 		= $pickup_fare + $drop_fare + ($km * $distance_fare);
			// }
			// else {
			// 	$delivery_fee = site_setting('delivery_fee');
			// }

			$delivery_fee = get_delivery_fee($restaurant_address->latitude, $restaurant_address->longitude,$update_order->restaurant->id);
			$update_order->delivery_fee = $delivery_fee;
		}


		$update_order->delivery_mode 	= $request->delivery_mode ? $request->delivery_mode : 1;
		$update_order->save();

		if(($request->delivery_mode == '' && $mode[0] == 1) || ($request->delivery_mode == 1)){
			$delivery_fee = 0.00;
			$update_order_pickup = Order::with('restaurant.user_address')->find($cart_order->id);
			$update_order->delivery_fee 	= $delivery_fee;
			$update_order->save();
		}

		$offer_amount = offer_calculation($cart_order->restaurant_id, $cart_order->id);
		$promo_amount = promo_calculation();
		$penality = penality($cart_order->id);

		//wallet apply
		$is_wallet = $request->isWallet;

		$wallet_amount = use_wallet_Amount($cart_order->id, $is_wallet);

		$cart_order = Order::getAllRelation()->where('user_id', $user_details->id)->status('cart')->first();

		$results = array();
		
		$cart_details = $cart_order->toArray();

		$data = [
			'id' => $cart_details['id'],
			'restaurant_id' => $cart_details['restaurant_id'],
			'user_id' => $cart_details['user_id'],
			'address' => $delivery_address_data,
			'driver_id' => $cart_details['driver_id'] ?? 0,
			'subtotal' => $cart_details['subtotal'],
			'offer_percentage' => $cart_details['offer_percentage'],
			'offer_amount' => $cart_details['offer_amount'],
			'promo_id' => $cart_details['promo_id'],
			'promo_amount' => $cart_details['promo_amount'],
			'delivery_fee' => $cart_details['delivery_fee_text'],
			'booking_fee' => $cart_details['booking_fee'],
			'restaurant_commision_fee' => $cart_details['restaurant_commision_fee'],
			'driver_commision_fee' => $cart_details['driver_commision_fee'] ?? '',
			'tax' => $cart_details['tax'],
			'total_amount' => $cart_details['total_amount'],
			'wallet_amount' => $cart_details['wallet_amount'],
			'payment_type' => $cart_details['payment_type'] ?? 0,
			'owe_amount' => $cart_details['owe_amount'],
			'status' => $cart_details['status'],
			'payout_status' => $cart_details['payout_status'] ?? '',
			'restaurant_status' => $cart_details['restaurant_status'],
			'penality' => $cart_details['user_penality'],

		];

		$data_invoices = [];
		$data_invoice  = [
			array('key' =>trans('api_messages.cart.subtotal'), 'value' => $cart_details['subtotal'])
		];

		if($cart_details['delivery_mode']=='2') {
			$data_invoices = [
				array('key' =>trans('api_messages.cart.delivery_fee'), 'value' => $cart_details['delivery_fee'])
			];
		}

		$dataInvoice = [
			array('key' =>trans('api_messages.cart.booking_fee'), 'value' => $cart_details['booking_fee']),
			array('key' =>trans('api_messages.cart.tax'), 'value' => $cart_details['tax']),
			array('key' =>trans('api_messages.cart.promo_amount'), 'value' => $cart_details['promo_amount']),
			array('key' =>trans('api_messages.cart.wallet_amount'), 'value' => $cart_details['wallet_amount']),
			array('key' =>trans('api_messages.cart.total'), 'value' => $cart_details['total_amount']),
		];

		$data['invoice'] = array_merge( array_merge($data_invoice,$data_invoices) ,$dataInvoice);
		// $data['cart_details'] = $cart_details;

		$data['restaurant'] = $cart_details['restaurant'];
		$order_item = $cart_details['order_item'];

		foreach ($order_item as $order_item) {
			$order_item_modifier = collect($order_item['order_item_modifier']);
			$results = array();
			$order_item_modifier->map(function($item) use (&$results) {
				$order_item_modifier_item = collect($item['order_item_modifier_item']);
				return $order_item_modifier_item->map(function($item) use (&$results) {
					$results[] = [
						'id' 	=> $item['id'],
						'count' => $item['count'],
						'price' => (string) number_format($item['price'] * $item['count'],'2'),
						'name'  => $item['modifier_item_name'],
					];
					return [];
				});
			});
			
			$data['menu_item'][] = [
				'order_item_id' => $order_item['id'],
				'order_id' => $order_item['order_id'],
				'menu_item_id' => $order_item['menu_item_id'],
				'price' => $order_item['price'],
				'quantity' => $order_item['quantity'],
				'modifier_price' => $order_item['modifier_price'],
				'total_amount' => $order_item['total_amount'],
				'offer_price' => $order_item['offer_price'],
				'tax' => $order_item['tax'],
				'notes' => $order_item['notes'],
				'id' => $order_item['menu_item']['id'],
				'is_visible' => $order_item['menu_item']['is_visible'],
				'is_offer' => $order_item['menu_item']['is_offer'],
				'menu_id' => $order_item['menu_item']['menu_id'],
				'menu_category_id' => $order_item['menu_item']['menu_category_id'],
				'name' => $order_item['menu_name'],
				'description' => $order_item['menu_item']['description'],
				'tax_percentage' => $order_item['menu_item']['tax_percentage'],
				'type' => $order_item['menu_item']['type'],
				'status' => $order_item['menu_item']['status'],
				'menu_item_image' => $order_item['menu_item']['menu_item_image'],
				'menu_item_main_addon' => $results,
			];
		}

		$user_promocode = UsersPromoCode::WhereHas('promo_code', function ($q) {
			})
			->where('user_id', $user_details->id)
			->where('order_id', '0')
			->get();

		$final_promo_details = $user_promocode->map(function($user_promo) {
			return [
				'id' 			=> $user_promo->promo_code->id,
				'price'			=> $user_promo->promo_code->price,
				'code' 			=> $user_promo->promo_code->code,
				'expire_date' 	=> $user_promo->promo_code->expire_date,
			];
		});

		if($request->delivery_mode == '') $delivery_mode_ios = 'Choose'; elseif($request->delivery_mode == 1) $delivery_mode_ios = 'Pickup'; else $delivery_mode_ios = 'Delivery';

		return response()->json([
			'status_code' => '1',
			'status_message' => trans('api_messages.cart.updated_successfully'),
			'cart_details' => $data,
			'promo_details' => $final_promo_details,
			'delivery_mode' => $delivery_mode->app_delivery_mode,
		]);
	}

	//testing purpose
	public function unicode_decode($data)
	{
		$str = preg_replace("/%u([0-9a-f]{3,4})/i","&#x\\\\1;",urldecode($data));
		return $str;
	}

	/**
	 * API for order item
	 *
	 * @return Response Json response with status
	 */
	public function clear_cart(Request $request)
	{
		$user_details = JWTAuth::parseToken()->authenticate();

		$remove_order = OrderItem::with('order_item_modifier.order_item_modifier_item')->find($request->order_item_id);
		$remove_order_item = OrderItemModifier::where('order_item_id',$request->order_item_id)->get();

		if($remove_order_item) {
			foreach ($remove_order_item as $key => $value) {
			$order = $value->id;
			$remove_order_item_modifer = OrderItemModifierItem::whereIn('order_item_modifier_id',[$order])->delete();
			}
			OrderItemModifier::where('order_item_id',$request->order_item_id)->delete();
		}
		

		if ($remove_order) {
			$remove_order->delete();
		}

		$orderitem = OrderItem::where('order_id', $request->order_id)->count();

		if ($orderitem == 0) {
			$remove_order_delivery = OrderDelivery::where('order_id', $request->order_id)->first();

			if ($remove_order_delivery) {
				$remove_order_delivery->delete();
			}

			$order = Order::find($request->order_id);

			if ($order) {

				$remove_penality = PenalityDetails::where('order_id', $order->id)->first();

				if ($remove_penality) {
					$remove_penality->delete();
				}

				$order->delete();

				//ASAP
				$address = UserAddress::where('user_id', $user_details->id)->default()->first();
				$address->order_type = 0;
				$address->save();
			}

		}

		return response()->json(
			[

				'status_message' => 'Removed Successfully',

				'status_code' => '1',

			]
		);
	}

	/**
	 * API for order with order item
	 *
	 * @return Response Json response with status
	 */
	public function clear_all_cart(Request $request)
	{
		$user_details = JWTAuth::parseToken()->authenticate();
		$order = Order::where('user_id', $user_details->id)->status('cart')->first();
		
		if(is_null($order)) {
			return response()->json([
				'status_code' => '0',
				'status_message' => trans('api_messages.already_removed'),
			]);
		}

		try {
			\DB::beginTransaction();
			$order_items = OrderItem::where('order_id', $order->id)->get();

			$order_item_modifiers = OrderItemModifier::whereIn('order_item_id',$order_items->pluck('id')->toArray())->get();
			
			if($order_item_modifiers->count()) {
				$order_item_modifier_items = OrderItemModifierItem::whereIn('order_item_modifier_id',$order_item_modifiers->pluck('id')->toArray())->delete();
			}

			OrderItemModifier::whereIn('order_item_id',$order_items->pluck('id')->toArray())->delete();
			OrderItem::where('order_id', $order->id)->delete();
			OrderDelivery::where('order_id', $order->id)->delete();
			PenalityDetails::where('order_id', $order->id)->delete();
			Order::find($order->id)->delete();
			\DB::commit();
		}
		catch (\Exception $e) {
			\DB::rollback();
			return response()->json([
				'status_code' => '0',
				// 'status_message' => trans('api_messages.unable_to_remove'),
				'status_message' => $e->getMessage(),
			]);
		}

		return response()->json([
			'status_code' => '1',
			'status_message' => trans('api_messages.removed_successfully'),
		]);
	}

	/**
	 * API for Order history and upcoming order
	 *
	 * @return Response Json response with status
	 */
	public function order_list(Request $request)
	{
		$user_details = JWTAuth::parseToken()->authenticate();

		$order_list = Order::getAllRelation()->where('user_id', $user_details->id)->history()->orderBy('id', 'DESC')->get();
		$upcoming = Order::getAllRelation()->where('user_id', $user_details->id)->upcoming()->orderBy('id', 'DESC')->get();

		$order_list = $order_list->map(
			function ($item) {
				$menu_item = $item['order_item']->map(
					function ($item) {
						$order_item_modifier_item = $item->order_item_modifier->map(function($menu) {
							return $menu->order_item_modifier_item->map(function ($item) {
								return [	
									'id'	=> $item->id,			
									'count' => $item->count,
									'price' => (string) number_format($item->price * $item->count,'2'),
									'name'  => @$item->modifier_item_name,
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

						return [
							'quantity' => $item['quantity'],
							'menu_item_id' => $item['menu_item']['id'],
							'item_image' => $item['menu_item']['menu_item_image'],
							'price' => $item['total_amount'],
							'menu_name' => $item['menu_name'],
							'menu_item_main_addon' => @$result ? $result : [],
							'type' => $item['menu_item']['type'],
							'status' => $item['menu_item']['status'],
							'review' => $item['review'] ? $item['review']['is_thumbs'] : 2,
						];
					}
				)->toArray();

				$rating = "0";
				$contact = '';

				if ($item->driver_id && $item->driver) {

					if ($item->driver->review) {
						$rating = $item->driver->review->user_to_driver_rating;
					}

					$contact = $item->driver->driver_contact;

				}

				$user_id = get_restaurant_user_id($item['restaurant']['id']);

				$restaurant_address = get_restaurant_address($user_id);

				$user_address = get_user_address($item['user_id']);

				$star_rating = '0.0';
				$is_rating = 0;
				$food_status = [];

				if ($item->status_text == 'completed') {

					$food_status[] = [

						'time' => $item->completed_at->format('h:i').' '.trans('api_messages.monthandtime.'.$item->completed_at->format('a')),
						'status' => trans('api_messages.orders.ready_to_eat'),
					];

				}

				if (($item->status_text == 'delivery' || $item->status_text == 'completed') && isset($item->order_delivery->started_at)) {

					$delivery_at = (string) date('h:i', strtotime($item->delivery_at)).' '.trans('api_messages.monthandtime.'.date('a', strtotime($item->delivery_at)));

					$food_status[] = [

						'time' => $delivery_at,
						'status' => trans('api_messages.orders.food_on_the_way'),
					];
				}

				if ($item->status_text == 'accepted' || $item->status_text == 'completed') {

					$food_status[] = [

						'time' => $item->accepted_at->format('h:i').' '.trans('api_messages.monthandtime.'.$item->accepted_at->format('a')),
						'status' => trans('api_messages.orders.preparing_your_food'),
					];

					$food_status[] = [

						'time' => $item->accepted_at->format('h:i').' '.trans('api_messages.monthandtime.'.$item->accepted_at->format('a')),
						'status' => trans('api_messages.orders.order_accepted'),
					];

					$is_rating = @$item->review->user_atleast == 1 ? 1 : 0;

					$star_rating = $item->review !== Null ? $item->review->star_rating : '0';
				}
				$show_date = ($item->order_status == 4) ? date('d F Y h:i a', strtotime($item['cancelled_at'])) : date('d F Y h:i a', strtotime($item['updated_at']));

				$total_amount = $item['total_amount'];

				$getOpenTime = $item['restaurant']['restaurant_time']['start_time'];
				$get_show_date = date('d', strtotime($show_date)).' '.trans('api_messages.monthandtime.'.date('M', strtotime($show_date))).' '.date('Y', strtotime($show_date)).' '.date('h:i', strtotime($show_date)).' '.trans('api_messages.monthandtime.'.date('a', strtotime($show_date)));
				$est_time = date('h:i', strtotime($item->est_delivery_time)).' '.trans('api_messages.monthandtime.'.date('a', strtotime($item->est_delivery_time)));

				return [
					'order_id' => $item['id'],
					'delivery_mode' => $item['delivery_mode'] ?? "",
					'delivery_mode_text' => $item['delivery_mode_text'] ?? "",
					'total_amount' => $total_amount,
					'subtotal' => $item['subtotal'],
					'delivery_fee' => $item['delivery_fee_text'],
					'booking_fee' => $item['booking_fee'],
					'tax' => $item['tax'],
					'wallet_amount' => $item['wallet_amount'],
					'promo_amount' => $item['promo_amount'],
					'order_status' => $item['status'],
					'name' => $item['restaurant']['name'],
					'restaurant_id' => $item['restaurant']['id'],
					'restaurant_status' => $item['restaurant']['status'],
					'restaurant_open_time' => $getOpenTime,
					'status' => $item['status'],
					'restaurant_banner' => $item['restaurant']['banner'],
					'date' => $get_show_date ,
					'menu' => $menu_item,
					'total_seconds' => $item->user_total_seconds,
					'remaining_seconds' => $item->user_remaining_seconds,
					'user_status_text' => $item->user_status_text,
					'est_complete_time' => $est_time,

					'driver_name' => $item->driver ? $item->driver->user->name : "",
					'driver_image' => $item->driver ? $item->driver->user->user_image_url : "",
					'vehicle_type' => $item->driver ? $item->driver->vehicle_type_details->name : '',
					'vehicle_number' => $item->driver ? $item->driver->vehicle_number : '',
					'driver_rating' => $rating,
					'driver_contact' => $contact,
					'order_type' => $item['schedule_status'],
					'delivery_time' => $item['schedule_time'],
					'delivery_options' => $item->user->user_address ? $item->user->user_address->delivery_options : '',

					'apartment' => $item->user->user_address ? $item->user->user_address->apartment : '',
					'delivery_note' => $item->user->user_address ? $item->user->user_address->delivery_note : '',
					'order_delivery_status' => $item->order_delivery ? $item->order_delivery['status'] : '-1',

					'pickup_latitude' => $restaurant_address->latitude,

					'pickup_longitude' => $restaurant_address->longitude,

					'restaurant_location' => $restaurant_address->address,

					'drop_latitude' => $user_address->latitude,

					'drop_longitude' => $user_address->longitude,

					'drop_address' =>$user_address->address1,

					'driver_latitude' => $item->driver ? $item->driver->latitude : "",

					'driver_longitude' => $item->driver ? $item->driver->longitude : "",

					'is_rating' => $is_rating,

					'star_rating' => $star_rating,

					'food_status' => $food_status,

					'restaurant_closed' => $item['restaurant']['restaurant_time']['closed'],

					'restaurant_next_time' => $item['restaurant']['restaurant_next_opening'],

					'penality' => $item['user_penality'],

					'applied_penality' => $item['user_applied_penality'],

					'notes' => (string) $item['user_notes'],

					'invoice' => [
						array('key' =>trans('api_messages.cart.subtotal'), 'value' => $item['subtotal']),
						array('key' =>trans('api_messages.cart.delivery_fee'), 'value' => $item['delivery_fee_text']),
						array('key' =>trans('api_messages.cart.booking_fee'), 'value' => $item['booking_fee']),
						array('key' =>trans('api_messages.cart.tax'), 'value' => $item['tax']),
						array('key' =>trans('api_messages.cart.promo_amount'), 'value' => $item['promo_amount']),
						array('key' =>trans('api_messages.cart.wallet_amount'), 'value' => $item['wallet_amount']),
						array('key' =>trans('api_messages.cart.total'), 'value' => $item['total_amount']),
					],
				];
			}
		);

		$order_list = $order_list->toArray();

		//upcoming

$upcoming = $upcoming->map(
	function ($item) {
		$upcoming_menu_item = $item['order_item']->map(
			function ($item) {
				$order_item_modifier_item = $item->order_item_modifier->map(function($menu) {
							return $menu->order_item_modifier_item->map(function ($item) {
								return[	
									'id'	=> $item->id,			
									'count' => $item->count,
									'price' => (string) number_format($item->price * $item->count,'2'),
									'name'  => @$item->modifier_item_name,
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
				return [

					'quantity' => $item['quantity'],
					'menu_item_id' => $item['menu_item']['id'],
					'item_image' => $item['menu_item']['menu_item_image'],
					'price' => $item['total_amount'],
					'menu_name' => $item['menu_name'],
					'menu_item_main_addon' => @$result ? $result : [],
					'type' => $item['menu_item']['type'],
					'status' => $item['menu_item']['status'],

				];
			}
		)->toArray();

		$rating = 0;
		$contact = '';

		if ($item->driver_id && $item->driver) {

			if ($item->driver->review) {
				$rating = $item->driver->review->user_to_driver_rating;
			}

			$contact = $item->driver->driver_contact;

		}

		$user_id = get_restaurant_user_id($item['restaurant']['id']);

		$restaurant_address = get_restaurant_address($user_id);

		$user_address = get_user_address($item['user_id']);

		$food_status = array();

		if ($item->status_text == 'completed') {

			$food_status[] = [


				'time' => $item->completed_at->format('h:i').' '.trans('api_messages.monthandtime.'.$item->completed_at->format('a')),
				'status' => trans('api_messages.orders.ready_to_eat'),
			];

		}

		if (($item->status_text == 'delivery' || $item->status_text == 'completed') && isset($item->order_delivery->started_at)) {


			$delivery_at = (string) date('h:i', strtotime($item->delivery_at)).' '.trans('api_messages.monthandtime.'.date('a', strtotime($item->delivery_at)));
			$food_status[] = [

				'time' => $delivery_at,
				'status' => trans('api_messages.orders.food_on_the_way'),
			];
		}

		if ($item->schedule_status == '0' && ($item->status_text == 'accepted' || $item->status_text == 'completed' || $item->status_text == 'delivery')) {

			$food_status[] = [



				'time' => $item->accepted_at->format('h:i').' '.trans('api_messages.monthandtime.'.$item->accepted_at->format('a')),
				'status' => trans('api_messages.orders.preparing_your_food'),
			];

		}

		if ($item->status_text == 'accepted' || $item->status_text == 'completed' || $item->status_text == 'delivery') {

			$food_status[] = [

				'time' => $item->accepted_at->format('h:i').' '.trans('api_messages.monthandtime.'.$item->accepted_at->format('a')),
				'status' => trans('api_messages.orders.order_accepted'),
			];
		}

		$date = date('Y-m-d', strtotime($item['created_at']));
		$schedule_time = date('Y-m-d', strtotime($item['schedule_time']));

		if ($item['schedule_status'] == 0) {

			if ($date == date('Y-m-d')) {

				$date = 'Today' . ' ' . date('M d h:i a', strtotime($item['created_at']));
			} else if ($date == date('Y-m-d', strtotime("+1 days"))) {

				$date = 'Tomorrow' . ' ' . date('M d h:i a', strtotime($item['created_at']));
			} else { $date = date('l M d h:i a', strtotime($item['created_at']));}

		} else {

			$time_Stamp = strtotime($item['schedule_time']) + 1800;

			$del_time = date('h:i a', $time_Stamp);
			$common = date('M d h:i a', strtotime($item['schedule_time']));

			if ($schedule_time == date('Y-m-d')) {

				$date = 'Today' . ' ' . $common . ' - ' . $del_time;
			} else if ($schedule_time == date('Y-m-d', strtotime("+1 days"))) {

				$date = 'Tomorrow'. ' ' . $common . ' - ' . $del_time;
			} else {
				$date = date('l M d h:i a', strtotime($item['schedule_time'])) . ' - ' . $del_time;
			}

		}

		if ($item->status_text == "pending") {

			$est_completed_time = $item->est_delivery_time;
		} else {
			$est_completed_time = $item->completed_at;
		}

		$date = date('Y-m-d H:i:s', strtotime($item['created_at']));
	
		$get_show_date = date('d', strtotime($date)).' '.trans('api_messages.monthandtime.'.date('M', strtotime($date))).' '.date('Y', strtotime($date)).' '.date('h:i', strtotime($date)).' '.trans('api_messages.monthandtime.'.date('a', strtotime($date)));

		$est_time = date('h:i', strtotime($est_completed_time)).' '.trans('api_messages.monthandtime.'.date('a', strtotime($est_completed_time)));

		if(is_null($item['schedule_time'])){
			$get_delivery_time = null;
		}else{
		
		$delivery_time = date('Y-m-d H:i:s', strtotime($item['schedule_time']));
		$get_delivery_time = date('d', strtotime($delivery_time)).' '.trans('api_messages.monthandtime.'.date('M', strtotime($delivery_time))).' '.date('Y', strtotime($delivery_time)).' '.date('h:i', strtotime($delivery_time)).' '.trans('api_messages.monthandtime.'.date('a', strtotime($delivery_time)));						
		}

		return [

			'order_id' => $item['id'],
			'delivery_mode' => $item['delivery_mode'] ?? "",
			'delivery_mode_text' => $item['delivery_mode_text'] ?? "",
			'total_amount' => $item['total_amount'],
			'subtotal' => $item['subtotal'],
			'delivery_fee' => $item['delivery_fee'],
			'booking_fee' => $item['booking_fee'],
			'tax' => $item['tax'],
			'wallet_amount' => $item['wallet_amount'],
			'promo_amount' => $item['promo_amount'],
			'order_status' => $item['status'],
			'name' => $item['restaurant']['name'],
			'restaurant_id' => $item['restaurant']['id'],
			'restaurant_status' => 1,
			'restaurant_open_time' => $item['restaurant']['restaurant_time']['start_time'],

			'status' => $item['status'],
			'restaurant_banner' => $item['restaurant']['banner'],
			'order_type' => $item['schedule_status'],
			'delivery_time' => $get_delivery_time,
			'date' => $get_show_date,
			'menu' => $upcoming_menu_item,
			'total_seconds' => $item->user_total_seconds,
			'remaining_seconds' => $item->user_remaining_seconds,
			'user_status_text' => $item->user_status_text,
			'est_complete_time' => $est_time,

			'driver_name' => $item->driver ? $item->driver->user->name : "",
			'driver_image' => $item->driver ? $item->driver->user->user_image_url : "",
			'vehicle_type' => $item->driver ? $item->driver->vehicle_type_details->name : '',
			'vehicle_number' => $item->driver ? $item->driver->vehicle_number : '',
			'driver_rating' => $rating,
			'driver_contact' => $contact,

			'delivery_options' => $item->user->user_address ? $item->user->user_address->delivery_options : '',

			'apartment' => $item->user->user_address ? $item->user->user_address->apartment : '',
			'delivery_note' => $item->user->user_address ? $item->user->user_address->delivery_note : '',
			'order_delivery_status' => $item->order_delivery ? $item->order_delivery['status'] : '-1',

			'pickup_latitude' => $restaurant_address->latitude,

			'pickup_longitude' => $restaurant_address->longitude,

			'restaurant_location' => $restaurant_address->address,

			'drop_latitude' => $user_address->latitude,

			'drop_longitude' => $user_address->longitude,

			'drop_address' =>$user_address->address1,
 
			'driver_latitude' => $item->driver ? $item->driver->latitude : "",

			'driver_longitude' => $item->driver ? $item->driver->longitude : "",

			'food_status' => $food_status,

			'restaurant_closed' => 1,

			'restaurant_next_time' => $item['restaurant']['restaurant_next_opening'],
			'penality' => $item['user_penality'],


			'invoice' => [



				array('key' =>trans('api_messages.cart.subtotal'), 'value' => $item['subtotal']),
				array('key' =>trans('api_messages.cart.delivery_fee'), 'value' => $item['delivery_fee_text']),
				array('key' =>trans('api_messages.cart.booking_fee'), 'value' => $item['booking_fee']),
				array('key' =>trans('api_messages.cart.tax'), 'value' => $item['tax']),
				array('key' =>trans('api_messages.cart.promo_amount'), 'value' => $item['promo_amount']),
				array('key' =>trans('api_messages.cart.wallet_amount'), 'value' => $item['wallet_amount']),
				array('key' =>trans('api_messages.cart.total'), 'value' => $item['total_amount']),


			],

		];
	}
);

$upcoming = $upcoming->toArray();

return response()->json(
	[

		'status_message' => trans('api_messages.orders.successfully'),

		'status_code' => '1',

		'order_history' => $order_list,

		'upcoming' => $upcoming,

	]
);
}

	/**
	 * API for create a customer id  based on card details using stripe payment gateway
	 *
	 * @return Response Json response with status
	 */
	public function add_card_details(Request $request)
	{
		$rules = array(
            'intent_id'			=> 'required',
        );

        $attributes = array(
            'intent_id'     	=> 'Setup Intent Id',
        );

        $validator = Validator::make($request->all(), $rules,$attributes);

        if($validator->fails()) {
            return response()->json([
                'status_code' => '0',
                'status_message' => $validator->messages()->first(),
            ]);
        }

		$user_details = JWTAuth::parseToken()->authenticate();
		$stripe_payment = resolve('App\Repositories\StripePayment');

		$payment_details = UserPaymentMethod::firstOrNew(['user_id' => $user_details->id]);

		$setup_intent = $stripe_payment->getSetupIntent($request->intent_id);

		if($setup_intent->status != 'succeeded') {
			return response()->json([
				'status_code' => '0',
				'status_message' => $setup_intent->status,
			]);
		}

		if($payment_details->stripe_payment_method != '') {
			$stripe_payment->detachPaymentToCustomer($payment_details->stripe_payment_method);
		}

		$stripe_payment->attachPaymentToCustomer($payment_details->stripe_customer_id,$setup_intent->payment_method);

		$payment_method = $stripe_payment->getPaymentMethod($setup_intent->payment_method);
		$payment_details->stripe_intent_id = $setup_intent->id;
		$payment_details->stripe_payment_method = $setup_intent->payment_method;
		$payment_details->brand = $payment_method['card']['brand'];
		$payment_details->last4 = $payment_method['card']['last4'];
		$payment_details->save();

		return response()->json([
			'status_code' 		=> '1',
			'status_message' 	=> 'Added Successfully',
			'brand' 			=> $payment_details->brand,
			'last4' 			=> strval($payment_details->last4),
		]);
	}

	/**
	 * API for payment card details
	 *
	 * @return Response Json response with status
	 */
	public function get_card_details(Request $request)
	{
		$user_details = JWTAuth::parseToken()->authenticate();
		$stripe_payment = resolve('App\Repositories\StripePayment');

		$payment_details = UserPaymentMethod::firstOrNew(['user_id' => $user_details->id]);

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

		$status_code = "1";
		if($payment_details->stripe_intent_id == '') {
			$status_code = "2";
		}

		$setup_intent = $stripe_payment->createSetupIntent($customer_id);
		if($setup_intent->status == 'failed') {
			return response()->json([
				'status_code' 		=> "0",
				'status_message' 	=> $setup_intent->status_message,
			]);
		}

		return response()->json([
			'status_code' 		=> $status_code,
			'status_message' 	=> 'Listed Successfully',
			'intent_client_secret'=> $setup_intent->intent_client_secret,
			'brand' 			=> $payment_details->brand ?? '',
			'last4' 			=> (string)$payment_details->last4 ?? '',
		]);
	}

	/**
	 * API for filter
	 *
	 * @return Response Json response with status
	 */
	public function filter(Request $request)
	{
		$user_details ='';

		if($request->filled('token')) {
			$user_details = JWTAuth::parseToken()->authenticate();
		}

		$address_details = $this->address_details();

		$latitude = $address_details['latitude'];
		$longitude = $address_details['longitude'];

		$perpage = 7;

		$dietary = '';
		$price = [];

		if ($request->price) {
			$price = explode(',', $request->price);
		}

		if ($request->dietary || $request->dietary != '') {
			$dietary = explode(',', $request->dietary);
		}

		$type = $request->type;
		$sort = $request->sort;

		$search = [
			0 => 'Filter',
			1 => 'Favourite',
			2 => 'Popular',
			3 => 'Under',
			4 => 'New Restaurant',
			5 => 'More restaurant',
		];

		$user = User::with(['restaurant' => function ($query) use ($price, $dietary, $type, $sort) {
				$query->with(['restaurant_cuisine', 'restaurant_preparation_time', 'wished' => function ($query) {
					$query->select('restaurant_id', DB::raw('count(restaurant_id) as count'))->groupBy('restaurant_id');
				}, 'review']);
			}])
			->Type('restaurant')
			->whereHas('restaurant', function ($query) use ($latitude, $longitude) {
				$query->location($latitude, $longitude)
				->whereHas('restaurant_time', function ($query) {
				});
			})->status();

		if ($type == 0) {
			$restaurant = $user->whereHas('restaurant',function ($query) use ($price, $dietary, $type, $sort) {
				if ($this->count($price) > 0) {
					$query->whereIn('price_rating', $price);
				}

				if ($sort == 0 && $sort != null) {
					$query->where('recommend', '1');
				}

				if ($sort == 1) {
					$query->whereHas('wished', function ($query) {
					});
				}

				if ($this->count($dietary) > 0 && $dietary != '') {
					$query->whereHas('restaurant_cuisine', function ($query) use ($dietary) {
						$query->whereIn('cuisine_id', $dietary);
					});
				}
			});

			if ($sort == 2) {
				$rating = (clone $restaurant)->get();
				$collection = collect($rating)->sortByDesc(function ($rating) {
					return $rating->restaurant->review->restaurant_rating;
				});

				$restaurant = $collection->forPage($request->page, $perpage)->values();

				$page_count = round(ceil($restaurant->count() / $perpage));
			}
			else if ($sort == 3) {
				$delivery_time = (clone $restaurant)->get();
				$collection = collect($delivery_time)->sortBy(function ($delivery_time) {
					return $delivery_time->restaurant->convert_mintime;
				});

				$restaurant = $collection->forPage($request->page, $perpage)->values();
				$page_count = round(ceil($restaurant->count() / $perpage));
			}
			else {
				$restaurant = $restaurant->paginate($perpage);
				$page_count = $restaurant->lastPage();
			}
		}
		else {
			if ($type == 2) {
				$restaurant = Wishlist::select('restaurant_id', DB::raw('count(restaurant_id) as count'))->with(['restaurant' => function ($query) use ($latitude, $longitude) {
					$query->with(['restaurant_cuisine', 'review', 'user', 'restaurant_time', 'restaurant_offer']);
				}])
				->whereHas('restaurant', function ($query) use ($latitude, $longitude) {
					$query->UserStatus()->location($latitude, $longitude)->whereHas('restaurant_time', function ($query) {
					});
				})
				->groupBy('restaurant_id')
				->orderBy('count', 'desc')
				->paginate($perpage);
				$page_count = $restaurant->lastPage();
			}
			else {
				$date = \Carbon\Carbon::today()->subDays(10);
				$min_time = $request->min_time ? convert_format($request->min_time) : '00:20:00';
				$restaurant = $user->whereHas('restaurant',function ($query) use ($type, $min_time, $date) {
					if ($type == 1) {
						$query->whereHas('wished', function ($query) {
						});
					}
					else if ($type == 3) {
						$query->where('max_time', $min_time);
					}
					else if ($type == 4) {
						$query->where('created_at', '>=', $date);
					}
					else {
						// more restaurant
					}
				})->paginate($perpage);
				$page_count = $restaurant->lastPage();
			}
		}

		$user = $this->common_map($restaurant);

		$user = (count($user) > 0) ? $user->toArray() : array();

		return response()->json([
			'status_code' 		=> '1',
			'status_message' 	=> "Success",
			'restaurant' 		=> $user,
			'page_count' 		=> $page_count,
			'search_text' 		=> $search[$type],
		]);
	}

	/**
	 * API for cancel order who place the order
	 *
	 * @return Response Json response with status
	 */
	public function cancel_order(Request $request, PaymentController $PaymentController)
	{
		$order = Order::where('id', $request->order_id)->first();

		if ($order->status == '2' || $order->status == '4') {
			return response()->json([
				'status_code' => '0',
				'status_message' => trans('api_messages.eater.already_cancelled'),
			]);
		}

		if ($order->schedule_status == 0) {
			$rules = [
				'order_id' => 'required|exists:order,id,status,' . $order->statusArray['pending'],
			];

			$messages = [
				'order_id.exists' => trans('api_messages.eater.your_order_progress'),

			];

			$validator = Validator::make($request->all(), $rules, $messages);
			if ($validator->fails()) {
				return response()->json([
					'status_code' => '0',
					'status_message' => $validator->messages()->first(),
				]);
			}
		}

		$user_details = JWTAuth::parseToken()->authenticate();

		$order->cancel_order("eater", $request->cancel_reason, $request->cancel_message);

		$PaymentController->refund($request, 'Cancelled',$order->user_id,'eater');

		return response()->json([
			'status_code' => '1',
			'status_message' => trans('api_messages.eater.order_cancel'),
		]);
	}

	/**
	 * API for user review details and issue type
	 *
	 * @return Response Json response with status
	 */
	public function user_review(Request $request)
	{

		$user_details = JWTAuth::parseToken()->authenticate();

		$item = Order::getAllRelation()->where('user_id', $user_details->id)->where('id', $request->order_id)->first();

		if (!$item) {
			return response()->json([

				'status_message' => 'Empty',

				'status_code' => '0',

			]);
		}

		$menu_item = $item['order_item']->map(
			function ($item) {
				return [
					'quantity' => $item['quantity'],
					'order_item_id' => $item['id'],
					'menu_item_id' => $item['menu_item']['id'],
					'item_image' => $item['menu_item']['menu_item_image'],
					'price' => $item['menu_item']['price'],
					'menu_name' => $item['menu_name'],
					'type' => $item['menu_item']['type'],
					'status' => $item['menu_item']['status'],

				];
			}
		)->toArray();

		$driver_image = '';
		$driver_id = 0;
		$driver_name = '';

		if ($item->driver_id && $item->driver) {

			$driver_image = $item->driver->user->user_image_url;
			$driver_name = $item->driver->user->name;
			$driver_id = $item->driver_id;

		}

		$issue_user_menu_item = [];
		$issue_user_driver = [];

		$issue_user_menu_item = IssueType::TypeText('user_menu_item')->get();
		$issue_user_driver = IssueType::TypeText('user_driver')->get();

		$order_details = [

			'order_id' => $item['id'],
			'total_amount' => $item['total_amount'],
			'subtotal' => $item['subtotal'],
			'delivery_fee' => $item['delivery_fee'],
			'tax' => $item['tax'],
			'order_status' => $item['status'],
			'name' => $item['restaurant']['name'],
			'restaurant_id' => $item['restaurant']['id'],
			'restaurant_open_time' => $item['restaurant']['restaurant_time']['start_time'],
			'status' => $item['status'],
			'restaurant_banner' => $item['restaurant']['banner'],
			'date' => date('d F Y H:i a', strtotime($item['updated_at'])),
			'menu' => $menu_item,
			'driver_image' => $driver_image,
			'driver_name' => $driver_name,
			'driver_id' => $driver_id,
			'issue_user_menu_item' => $issue_user_menu_item,
			'issue_user_driver' => $issue_user_driver,
		];

		return response()->json([
			'status_code' 		=> '1',
			'status_message' 	=> 'Success',
			'user_review_data' 	=> $order_details,
		]);

	}

	/**
	 * API for Add rating in a order to menu item and delivery from user
	 *
	 * @return Response Json response with status
	 */
	public function add_user_review()
	{
		$user_details = JWTAuth::parseToken()->authenticate();

		$request = request();

		$order = Order::getAllRelation()->where('id', $request->order_id)->first();

		$rating = str_replace('\\', '', $request->rating);

		$rating = json_decode($rating);

		$order_id = $order->id;

		$food_item = $rating->food;

		//Rating for Menu item
		if ($food_item) {
			foreach ($food_item as $key => $value) {
				$review = new Review;
				$review->order_id = $order_id;
				$review->type = $review->typeArray['user_menu_item'];
				$review->reviewer_id = $user_details->id;
				$review->reviewee_id = $value->id;
				$review->is_thumbs = $value->thumbs;
				$review->order_item_id = $value->order_item_id;
				$review->comments = $value->comment ?: "";
				$review->save();

				if ($value->reason) {
					$issues = explode(',', $value->reason);
					if ($request->thumbs == 0 && $this->count($value->reason)) {
						foreach ($issues as $issue_id) {
							$review_issue = new ReviewIssue;
							$review_issue->review_id = $review->id;
							$review_issue->issue_id = $issue_id;
							$review_issue->save();
						}
					}
				}
			}
		}

		// Rating for driver
		if ($this->count(get_object_vars($rating->driver)) > 0) {
			$review = new Review;
			$review->order_id = $order_id;
			$review->type = $review->typeArray['user_driver'];
			$review->reviewer_id = $user_details->id;
			$review->reviewee_id = $order->driver_id;
			$review->is_thumbs = $rating->driver->thumbs;
			$review->comments = $rating->driver->comment ?: "";
			$review->save();

			if ($rating->driver->reason) {
				$issues = explode(',', $rating->driver->reason);
				if ($rating->driver->thumbs == 0 && $this->count($issues)) {
					foreach ($issues as $issue_id) {
						$review_issue = new ReviewIssue;
						$review_issue->review_id = $review->id;
						$review_issue->issue_id = $issue_id;
						$review_issue->save();
					}
				}
			}
		}

		//Rating for Restaurant
		if ($this->count(get_object_vars($rating->restaurant)) > 0) {

			$review = new Review;
			$review->order_id = $order_id;
			$review->type = $review->typeArray['user_restaurant'];
			$review->reviewer_id = $user_details->id;
			$review->reviewee_id = $order->restaurant_id;
			$review->rating = $rating->restaurant->thumbs;
			$review->comments = $rating->restaurant->comment ?: "";
			$review->save();

		}
		return response()->json([
			'status_code' => '1',
			'status_message' => 'Updated Successfully',
		]);
	}

	/**
	 * API for wallet amount
	 *
	 * @return Response Json response with status
	 */
	public function add_wallet_amount(Request $request)
	{
		$user_details = JWTAuth::parseToken()->authenticate();
		$amount = $request->amount;
		$currency_code = DEFAULT_CURRENCY;

		$stripe_payment = resolve('App\Repositories\StripePayment');

		if($request->filled('payment_intent_id')) {
			$payment_result = $stripe_payment->CompletePayment($request->payment_intent_id);
		}
		else {
			$user_payment_method = UserPaymentMethod::where('user_id', $user_details->id)->first();

			$paymentData = array(
				"amount" 		=> $amount * 100,
				'currency' 		=> $currency_code,
				'description' 	=> 'Wallet Payment by '.$user_details->first_name,
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

		$wallet = Wallet::where('user_id', $user_details->id)->first();

		if ($wallet) {
			$amount = $wallet->amount + $amount;
		}

		Wallet::updateOrCreate(
			['user_id' => $user_details->id],
			['amount' => $amount, 'currency_code' => $currency_code]
		);

		$payment = new Payment;
		$payment->user_id = $user_details->id;
		$payment->transaction_id = $payment_result->transaction_id;
		$payment->amount = $amount;
		$payment->status = 1;
		$payment->type = 1;
		$payment->currency_code = $currency_code;
		$payment->save();
		
		$wallet_details = Wallet::where('user_id', $user_details->id)->first();

		return response()->json([
			'status_code' => '1',
			'status_message' => trans('api_messages.success'),
			'wallet_amount' => $wallet_details->amount,
			'currency_code' => $wallet_details->currency_code,
		]);
	}

	/**
	 * API for Wishlist
	 *
	 * @return Response Json response with status
	 */
	public function wishlist(Request $request)
	{

		$user_details = JWTAuth::parseToken()->authenticate();

		$user = User::where('id', $user_details->id)->first();

		list('latitude' => $latitude, 'longitude' => $longitude) =
		collect($user->user_address)->only(['latitude', 'longitude'])->toArray();

		$wishlist = Wishlist::selectRaw('*,restaurant_id as ids, (SELECT count(restaurant_id) FROM wishlist WHERE restaurant_id = ids) as count')->with(

			['restaurant' => function ($query) use ($latitude, $longitude) {

				$query->with(['restaurant_cuisine', 'review', 'user', 'restaurant_time', 'restaurant_offer']);
			}]
		)->whereHas('restaurant', function ($query) use ($latitude, $longitude) {

			$query->UserStatus()->location($latitude, $longitude)->whereHas('restaurant_time', function ($query) {

			});

		})->where('user_id', $user_details->id)->get();

		$wishlist = $this->common_map($wishlist);

		return response()->json(
			[

				'wishlist' => $wishlist ? $wishlist : [],

				'status_message' => 'Success',

				'status_code' => '1',

			]
		);

	}

	/**
	 * API for Info window
	 *
	 * @return Response Json response with status
	 */
	public function info_window(Request $request)
	{
		if(request()->token)
		{
			$user_details = JWTAuth::parseToken()->authenticate();
			$user_address = get_user_address($user_details->id);
			list('latitude' => $user_latitude, 'longitude' => $user_longitude, 'address' => $user_location) = collect($user_address)->only(['latitude', 'longitude', 'address'])->toArray();
		}
		else
		{

			$user_latitude =  request()->latitude;
			$user_longitude = request()->longitude;
			$user_location =  request()->address;

		}

		$restauant_user_id = get_restaurant_user_id($request->id);
		
		$restauant_address = get_restaurant_address($restauant_user_id);

		list('latitude' => $restaurant_latitude, 'longitude' => $restaurant_longitude, 'address' => $restaurant_location) = collect($restauant_address)->only(['latitude', 'longitude', 'address'])->toArray();

		$restaurant_time = RestaurantTime::where('restaurant_id', $request->id)->orderBy('day', 'asc')->get();
		$restaurant = Restaurant::find($request->id);
		$restaurant_name = $restaurant->name;

		return response()->json(
			[

				'status_message' => 'Success',
				'status_code' => '1',
				'user_latitude' => $user_latitude,
				'user_longitude' => $user_longitude,
				'user_location' => $user_location,
				'restaurant_latitude' => $restaurant_latitude,
				'restaurant_longitude' => $restaurant_longitude,
				'restaurant_location' => $restaurant_location,
				'restaurant_time' => $restaurant_time,
				'restaurant_name' => $restaurant_name,

			]
		);

	}

	public function common_map($query) {

		if(isset(request()->token))
		{
			$user_details = JWTAuth::parseToken()->authenticate();
			$user = User::where('id', $user_details->id)->first();

			list('latitude' => $latitude, 'longitude' => $longitude, 'order_type' => $order_type, 'delivery_time' => $delivery_time) =
			collect($user->user_address)->only(['latitude', 'longitude', 'order_type', 'delivery_time'])->toArray();

		}
		else
		{
			$user_details = '';
			$latitude = request()->latitude;
			$longitude = request()->longitude;
			$order_type = request()->order_type;
			$delivery_time = request()->delivery_time;
			
		}

		

		return $query->map(
			function ($item) use ($user_details, $order_type, $delivery_time) {
				$restaurant_cuisine = $item['restaurant']['restaurant_cuisine']->map(
					function ($item) {
						return $item['cuisine_name'];
					}
				)->toArray();


				if($user_details)
					$wishlist = $item['restaurant']->wishlist($user_details->id, $item['restaurant']['id']);
				else
					$wishlist = 0;



				return [

					'order_type' => $order_type,
					'delivery_time' => $delivery_time,
					'restaurant_id' => $item['restaurant']['id'],
					'name' => $item['restaurant']['name'],
					'category' => implode(',', $restaurant_cuisine),
					'banner' => $item['restaurant']['banner'],
					'min_time' => $item['restaurant']['convert_mintime'],
					'max_time' => $item['restaurant']['convert_maxtime'],
					'restaurant_rating' => $item['restaurant']['review']['restaurant_rating'],
					'price_rating' => $item['restaurant']['price_rating'],
					'average_rating' => $item['restaurant']['review']['average_rating'],
					'wished' => $wishlist,
					'status' => $item['restaurant']['status'],
					'restaurant_open_time' => $item['restaurant']['restaurant_time']['start_time'],
					'restaurant_next_time' => $item['restaurant']['restaurant_next_opening'],
					'restaurant_closed' => $item['restaurant']['restaurant_time']['closed'],
					'restaurant_offer' => $item['restaurant']['restaurant_offer']->map(

						function ($item) {

							return [

								'title' => $item->offer_title,
								'description' => $item->offer_description,
								'percentage' => $item->percentage,

							];
						}
					),

				];
			}
		);
	}

	/**
	 * Default user address
	 */
	public function address_details()
	{
		if(isset(request()->token)) {
			$user_details = JWTAuth::toUser(request()->token);			
			$user = User::where('id', $user_details->id)->first();

			return list('latitude' => $latitude, 'longitude' => $longitude, 'order_type' => $order_type,'delivery_mode' => $delivery_mode, 'delivery_time' => $delivery_time) =
			collect($user->user_address)->only(['latitude', 'longitude', 'order_type', 'delivery_mode', 'delivery_time'])->toArray();
		}
		else {
			return ['latitude' => request()->latitude, 'longitude' => request()->longitude, 'order_type' => request()->type, 'delivery_mode' => @request()->delivery_mode ?? '', 'delivery_time' => request()->delivery_time ];
		}
	}

	public function count($array)
	{
 
        if(is_array($array))
        {
            $sum = 0;
            foreach($array as $ar)
            {
               $sum+= 1;
            }
            return $sum;
        }

        return 0;
       

	}

}