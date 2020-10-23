<?php

/**
 * RestaurantController
 *
 * @package    GoferEats
 * @subpackage  Controller
 * @category    Restaurant
 * @author      Trioangle Product Team
 * @version     1.3
 * @link        http://trioangle.com
 */

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Mail\ForgotEmail;
use App\Models\Country;
use App\Models\Cuisine;
use App\Models\Currency;
use App\Models\HomeSlider;
use App\Models\IssueType;
use App\Models\Menu;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\MenuItemModifier;
use App\Models\MenuItemModifierItem;
use App\Models\MenuTime;
use App\Models\OrderItem;
use App\Models\Payout;
use App\Models\PayoutPreference;
use App\Models\Restaurant;
use App\Models\RestaurantCuisine;
use App\Models\RestaurantDocument;
use App\Models\SiteSettings;
use App\Models\RestaurantPreparationTime;
use App\Models\RestaurantTime;
use App\Models\User;
use App\Models\UserAddress;
use App\Traits\FileProcessing;
use Auth;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Mail;
use Session;
use Storage;
use Stripe;
use Validator;
use App\Models\MenuTranslations;
use App\Models\MenuCategoryTranslations;
use App\Models\MenuItemTranslations;
use App\Exports\ArrayExport;
use App\Models\Review;
use App\Charts\BasicChart;

use App\Models\DriverOweAmount;
use App\Models\Order;

class RestaurantController extends Controller
{
	use FileProcessing;

	public function signup(Request $request)
	{
		if (request()->getMethod() == 'GET') {
			$data['cuisine'] = Cuisine::Active()->get()->pluck('name', 'id');
			$data['country'] = Country::Active()->get();
			session()->put('code', (canDisplayCredentials()) ? '1' : '1');
			$data['slider'] = HomeSlider::where('status', 1)->type('restaurant')->get();
			return view('restaurant.signup', $data);
		}

		$rules = [
			'city' => 'required',
			'email' => 'required|email|unique:user,email,NULL,id,type,1',
			'mobile_number' =>'required|unique:user,mobile_number,NULL,id,type,1',
		];
		$niceNames = array(
			'email' => trans('messages.driver.email'),
			'mobile_number'=>trans('messages.profile.phone_number'),
		);
		$validator = Validator::make($request->all(), $rules);
		$validator->setAttributeNames($niceNames);
		if ($validator->fails()) {
			return back()->withErrors($validator)->withInput();
		}

		// $user = User::where('mobile_number', $request->mobile_number)->where('type', 1)->get();

		// if (count($user)) {
		// 	$data['message'] = trans('messages.driver.this_number_already_exists');
		// 	return json_encode(['success' => 'no', 'data' => $data]);
		// }

		$user = new User;
		$user->name = $request->first_name . '~' . $request->last_name;
		$user->email = $request->email;
		$user->mobile_number = $request->mobile_number;
		$user->type = 1;
		$user->country_code = $request->country;
		$user->status = (env('APP_ENV')=="live") ? 1 : 4; 
		$user->password = bcrypt($request->password);
		$user->save();

		$country = Country::where('code', $request->country_code)->first();

		$user_address = new UserAddress;
		$user_address->street = $request->street;
		$user_address->city = $request->city;
		$user_address->state = $request->state;
		$user_address->country = $country->name;
		$user_address->country_code = $country->code;
		$user_address->latitude = $request->latitude;
		$user_address->longitude = $request->longitude;
		$user_address->user_id = $user->id;
		$user_address->address = $request->address;
		$user_address->type = 1;
		$user_address->default = 1;
		$user_address->save();

		$restaurant = new Restaurant;
		$restaurant->name = $request->name;
		$restaurant->user_id = $user->id;
		$restaurant->currency_code = DEFAULT_CURRENCY;
		$restaurant->price_rating = 0;
		$restaurant->recommend = 0;
		$restaurant->status = (env('APP_ENV')=="live") ? 1 : 0;
		$restaurant->max_time = '00:50:00';
		$restaurant->save();

		$restaurant_cuisine = new RestaurantCuisine;
		$restaurant_cuisine->restaurant_id = $restaurant->id;
		$restaurant_cuisine->cuisine_id = $request->cuisine;
		$restaurant_cuisine->status = 1;
		$restaurant_cuisine->save();

		if (Auth::guard('restaurant')->attempt(['email' => $request->email, 'password' => $request->password, 'type' => 1])) {
			return redirect()->route('restaurant.dashboard');
		}
		flash_message('danger', 'Try Again');
		return redirect()->route('restaurant.login');
	}

	public function thanks(Request $request)
	{
		return view('restaurant.thanks');
	}

	public function login(Request $request)
	{
		if (request()->getMethod() == 'GET') {
			$data['email'] = (canDisplayCredentials()) ? 'fairy@gmail.com' : '';
			return view('restaurant.login',$data);
		}
		$value = $request->textInputValue;
		
		if (is_numeric($value)) {
			$column = 'mobile_number';
			$text = trans('messages.restaurant_dashboard.number');
			$rules = array('textInputValue' => 'required');
			$attributes = array('textInputValue' => trans('admin_messages.mobile_number'));
		}
		else {
			$column = 'email';
			$text = trans('messages.driver.email');
			$rules = array('textInputValue' => 'required|email');
			$attributes = array('textInputValue' => trans('messages.driver.email'));
		}

		$validator = Validator::make(request()->all(), $rules,[], $attributes);
		$validator->setAttributeNames($attributes);
		if ($validator->fails()) {
			return back()->withErrors($validator)->withInput();
		}
		$user_check = User::where($column, $value)->get();
		if(count($user_check)) {
			Session::put('text', $column);
			Session::put('value', $value);
			return redirect()->route('restaurant.password');
		}
		else {
			flash_message('danger', trans('messages.restaurant_dashboard.not_found_your') . $text);
			return redirect()->route('restaurant.login');
		}
	}

	public function password(Request $request)
	{
		if (request()->getMethod() == 'GET') {
			$data['password'] = (canDisplayCredentials()) ? 'trioangle' : '';
			return view('restaurant.password',$data);
		}
		$text = Session::get('text');
		$value = Session::get('value');
		$password = $request->textInputPassword;
		if (Auth::guard('restaurant')->attempt([$text => $value, 'password' => $password, 'type' => 1])) {
			$data['user_details'] = auth()->guard('restaurant')->user();
			if ($data['user_details']->status === '0') {
				Auth::guard('restaurant')->logout();
				flash_message('danger', trans('messages.restaurant_dashboard.youre_disabled_by_admin_please_contact_admin'));
				return redirect()->route('restaurant.login'); // Redirect to login page
			}
			return redirect()->route('restaurant.dashboard');
		}
		flash_message('danger', trans('messages.restaurant_dashboard.invalid_credentials'));
		return redirect()->route('restaurant.login');
	}

	public function dashboard(Request $request)
	{
		session::forget('otp_confirm');
		$restaurant_id = get_current_restaurant_id();
		//chart start
		$last_seven_payouts = Payout::select(DB::raw('DATE(created_at) as date'), DB::raw('sum(amount) as amount'))->where('user_id', get_current_login_user_id())->whereRaw('DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)')->groupBy(DB::raw('DATE(created_at)'))
			->get();
		$data['last_seven_total_payouts'] = 0;
		if (count($last_seven_payouts) > 0) {
			$data['last_seven_total_payouts'] = $last_seven_payouts->sum('amount');
		}
		$payout_array = $last_seven_payouts->toArray();
		$amount = array_column($payout_array, 'amount');
		$date = array_column($payout_array, 'date');
		if (count($payout_array)) {
			$chart = new BasicChart;
			$chart->title("", false);
			$chart->labels($date);
			$chart->dataset('sales', 'bar' ,$amount)->color("#43A422");

			$data['seven_chart'] = $chart;
		}
		$last_thirty_payouts = Payout::select(DB::raw('DATE(created_at) as date'), DB::raw('sum(amount) as amount'))->where('user_id', get_current_login_user_id())->whereRaw('DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)')->groupBy(DB::raw('DATE(created_at)'))
			->get();
		$data['last_thirty_total_payouts'] = 0;
		if (count($last_thirty_payouts) > 0) {
			$data['last_thirty_total_payouts'] = $last_thirty_payouts->sum('amount');
		}
		$last_thirty_payouts = $last_thirty_payouts->toArray();
		$amount1 = array_column($last_thirty_payouts, 'amount');
		$date1 = array_column($last_thirty_payouts, 'date');
		if (count($last_thirty_payouts)) {
			$chart = new BasicChart;
			$chart->title("", false);
			$chart->labels($date1);
			$chart->dataset('sales','bar',$amount1)->color("#43A422");

			$data['thirty_chart'] = $chart;
		}

		$restaurant = Restaurant::find($restaurant_id);
		$data['document'] = RestaurantDocument::where('restaurant_id', $restaurant_id)->get()->count();
		$data['open_time'] = RestaurantTime::where('restaurant_id', $restaurant_id)->where('status', 1)->get()->count();
		$data['profile_step'] = $restaurant->profile_step;
		$data['payout_preference'] = PayoutPreference::where('user_id', get_current_login_user_id())->where('default', 'yes')->get()->count();
		$data['all_menu'] = Menu::with(['menu_category' => function ($query) {
			$query->with('menu_item');
		}])
		->whereHas('menu_category', function ($query) {
			$query->whereHas('menu_item', function ($query) {
				$query->where('status', '1');
			});
		})
		->where('restaurant_id', $restaurant_id)
		->get();

		$menu_id = $this->get_menu_item($data['all_menu']->toArray());
		$user = User::find(get_current_login_user_id());
		$data['menu'] = $data['all_menu']->count();
		if ($user->status === 4 || $user->status === 5) {
			if ($data['document'] && $data['open_time'] && $data['profile_step'] && $data['payout_preference'] && $data['menu']) {
				$user->status = 5;
			}
			else {
				$user->status = 4;
			}
		}
		$user->save();
		$data['user'] = $user;
		//restaurant steps verification end
		//restaurant top sale  items start
		$data['top_sale_saven_days'] = OrderItem::selectRaw('*,count(menu_item_id) as total_times')->with(['order', 'menu_item'])
			->whereHas('order', function ($query) {
				$query->whereRaw('DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)')->where('status', 6);
			})
			->whereIn('menu_item_id', $menu_id['menu_item_id'])->groupBy('menu_item_id')->get();
		$data['top_sale_thirty_days'] = OrderItem::selectRaw('*,count(menu_item_id) as total_times')->with(['order', 'menu_item'])
			->whereHas('order', function ($query) {
				$query->whereRaw('DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)')->where('status', 6);
			})
			->whereIn('menu_item_id', $menu_id['menu_item_id'])->groupBy('menu_item_id')->get();
		//restaurant top sale  items end
		//restaurant item review start
		$review = $this->review_details($restaurant_id);
		$json = json_encode($review);
		$review_array = json_decode($json, true);
		$data['review_column'] = $this->issue_column($review_array);

		//dd($data['review_column']);
		//restaurant item review endd
		//restaurant restaurant review start
		$retaurant_review = $restaurant->all_review()->whereRaw("date(created_at) <= date_sub(now(), interval -3 month)")->get();
		$total_reviewe = $retaurant_review->sum('rating');
		$total_count_reviewe = $retaurant_review->count('id');
		$data['retauarnt_rating'] = 0;
		if ($total_count_reviewe > 0) {
			$data['retauarnt_rating'] = round(($total_reviewe / ($total_count_reviewe * 5)) * 100);
		}
		//restaurant restaurant review endd
		//restaurant restaurant order  start
		$retaurant_order = $restaurant->order();
		$total_order = (clone $retaurant_order)->whereNotIn('status', ['1', '0'])->count('id');
		$data['accepted_rating'] = 0;
		$data['canceled_rating'] = 0;
		$accepted_order = (clone $retaurant_order)->where('accepted_at', '!=', '')->count('id');
		if ($total_order > 0) {
			$data['accepted_rating'] = round(($accepted_order / $total_order) * 100);
		}
		$canceled_order = (clone $retaurant_order)->where('cancelled_at', '!=', '')->count('id');
		if ($total_order > 0) {
			$data['canceled_rating'] = round(($canceled_order / $total_order) * 100);
		}
		//restaurant restaurant order end
		$currency = resolve("Currency")->where('code',$restaurant->currency_code)->first();
		$data['default_currency_symbol'] = $currency->symbol;
		return view('restaurant.dashboard', $data);
	}

	public function get_menu_item($menu)
	{
		$data['menu_item_array'] = [];
		$data['menu_item_id'] = [];
		foreach ($menu as $menu_category) {
			foreach ($menu_category['menu_category'] as $menu_item) {
				foreach ($menu_item['menu_item'] as $menu_item_val) {
					$data['menu_item_array'][] = $menu_item_val;
					$data['menu_item_id'][] = $menu_item_val['id'];
				}
			}
		}
		return $data;
	}

	public function issue_column($review)
	{
		$return_column[] = '';
		foreach ($review as $value) {

			$negative_comments = Review::where(['reviewee_id'=>$value['reviewee_id_'],'is_thumbs'=>'0'])->pluck('comments')->toArray();
			$value['negative_comments'] = array_filter($negative_comments);

			if ($value['issues_id'] == '') {
				$value['prasantage'] = $prasantage = round(($value['thumbs'] / $value['count_thumbs']) * 100);
				$return_column[] = $value;
			}
			else {
				$column = explode(',', $value['issues']);
				$issue_id = explode(',', $value['issues_id']);
				foreach ($column as $issues) {
					$find = IssueType::where('name', $issues)->first()->id;
					$issue_id_filter = array_count_values($issue_id);
					$count = $issue_id_filter[$find];
					$value['issues_column'][$issues] = $count;
				}
				$value['prasantage'] = $prasantage = round(($value['thumbs'] / $value['count_thumbs']) * 100);
				$return_column[] = $value;
			}
		}
		return array_filter($return_column);
	}

	public function array_flatten($array)
	{
		if (!is_array($array)) {
			return FALSE;
		}
		$result = array();
		foreach ($array as $key => $value) {
			if (is_array($value)) {
				$result = array_merge($result, array_flatten($value));
			} else {
				$result[$key] = $value;
			}
		}
		return $result;
	}

	public function preparation(Request $request)
	{
		$id = get_current_restaurant_id();
		$data['preparation'] = RestaurantPreparationTime::where('restaurant_id', $id)->orderBy('day', 'ASC')->get();
		$data['max_time'] = convert_minutes(Restaurant::where('id', $id)->first()->max_time);
		return view('restaurant.preparation_time', $data);
	}

	public function update_preparation_time(Request $request)
	{
		$id = get_current_restaurant_id();
		$restaurant = Restaurant::find($id);
		$restaurant->max_time = convert_format($request->overall_max_time);
		$restaurant->save();
		if (isset($request->day)) {
			foreach ($request->day as $key => $time) {
				if (isset($request->id[$key])) {
					$restaurant_update = RestaurantPreparationTime::find($request->id[$key]);
				} else {
					$restaurant_update = new RestaurantPreparationTime;
				}
				$restaurant_update->from_time = $request->from_time[$key];
				$restaurant_update->to_time = $request->to_time[$key];
				$restaurant_update->max_time = convert_format($request->max_time[$key]);
				$restaurant_update->day = $request->day[$key];
				$restaurant_update->status = $request->status[$key];
				$restaurant_update->restaurant_id = $id;
				$restaurant_update->save();
				$available_id[] = $restaurant_update->id;
			}
			if (isset($available_id)) {
				RestaurantPreparationTime::whereNotIn('id', $available_id)->delete();
			}
			flash_message('success', trans('admin_messages.updated_successfully'));
		} else{
			$store = RestaurantPreparationTime::where('restaurant_id',$id)->delete();
			flash_message('success', trans('admin_messages.updated_successfully'));
		}
		return back();
	}

	/*
	*
	* remove preparation time
	*
	*/
	public function remove_time()
	{
		$id = request()->id;
		$restaurant_time = RestaurantPreparationTime::find($id);
		if ($restaurant_time) {
			$restaurant_time->delete($id);
		}
		return json_encode(['success' => true]);
	}

	protected function getMenu($restaurant_id,$locale)
	{
		$menu = Menu::with('menu_category.all_menu_item.menu_item_modifier.menu_item_modifier_item')->where('restaurant_id', $restaurant_id)->get();

		$menu = $menu->map(function ($item) use ($locale) {
			$item->setDefaultLocale($locale);
			$menu_category = $item->menu_category->map(function ($item)  use ($locale) {
				$item->setDefaultLocale($locale);
				$menu_item = $item->all_menu_item->map(function ($item)  use ($locale) {
					$item->setDefaultLocale($locale);

					$menu_item_modifier = $item->menu_item_modifier->map(function ($item)  use ($locale) {
						$item->setDefaultLocale($locale);
						
						$menu_item_modifier_item = $item->menu_item_modifier_item->map(function($item)  use ($locale) {
							$item->setDefaultLocale($locale);
							return [
								'id' 	=> $item->id,
								'name' 	=> $item->name,
								'price' => $item->price,
							];
						})->toArray();
						$min_count = ($item->count_type == 1) ? $item->count_type : '';
						return [
							'id' 	=> $item->id,
							'name'		=> $item->name,
							'count_type'=> $item->count_type,
							'is_multiple'=> $item->is_multiple,
							'min_count'	=> $min_count,
							'max_count'	=> $item->max_count,
							'is_required'	=> $item->is_required,
							'menu_item_modifier_item' => $menu_item_modifier_item
						];
					})->toArray();

					return [
						'menu_item_id' 		=> $item->id,
						'menu_item_name' 	=> $item->name,
						'menu_item_desc' 	=> $item->description,
						'menu_item_org_name'=> $item->org_name,
						'menu_item_org_desc'=> $item->org_description,
						'menu_item_price' 	=> $item->price,
						'menu_item_tax' 	=> $item->tax_percentage,
						'menu_item_type' 	=> $item->type,
						'menu_item_status' 	=> $item->status,
						'item_image' 		=> is_object($item->menu_item_thump_image) ? '' : $item->menu_item_thump_image,
						'menu_item_modifier'=> $menu_item_modifier,
					];
				})->toArray();
				return [
					'menu_category_id' 	=> $item->id,
					'menu_category' 	=> $item->name,
					'menu_item' 		=> $menu_item,
				];
			})->toArray();

			return [
				'menu_id' 		=> $item->id,
				'menu' 			=> $item->name,
				'menu_category' => $menu_category,
			];
		});

		return $menu;
	}

	public function menu(Request $request)
	{
		$restaurant_id = auth()->guard('restaurant')->user()->id;
		$data['restaurant'] = $restaurant = Restaurant::where('user_id', $restaurant_id)->first();

		$data['menu'] = $this->getMenu($restaurant->id,'en');
		return view('restaurant.menu_editor', $data);
	}

	public function menu_locale(Request $request)
	{
		$restaurant_id = auth()->guard('restaurant')->user()->id;
		$restaurant = Restaurant::where('user_id', $restaurant_id)->first();
		$menu = $this->getMenu($restaurant->id,$request->locale);

		return response()->json(compact('menu'));
	}

	public function update_category(Request $request)
	{
		$locale = $request->locale;

		if($locale == 'en') {
			if ($request->action == 'edit') {
				$category = MenuCategory::find($request->id);
			}
			else {
				$category = new MenuCategory;
				$category->menu_id 	= $request->menu_id;
				$category->name = $request->name;
				$category->save();
				$data['category_id'] = $category->id;
			}
		}
		else {
			if(!$request->id) {
				return [
					'status' => false,
					'status_message' => __('messages.add_english_lang_first'),
				];
			}
			$category = MenuCategory::find($request->id);
			$category = $category->getTranslationById($locale, $category->id);
		}

		$category->name = $request->name;
		$category->save();

		$data['category_name'] = $category->name;
		return $data;
	}

	public function menu_time(Request $request)
	{
		$restaurant_id = auth()->guard('restaurant')->user()->id;
		$restaurant = Restaurant::where('user_id', $restaurant_id)->first();
		$data['menu_time'] = MenuTime::where('restaurant_id', $restaurant->id)->where('menu_id', $request->id)->orderBy('day', 'ASC')->get();
		return $data;
	}

	public function update_menu_time(Request $request)
	{
		$locale = $request->locale;
		$restaurant_id = auth()->guard('restaurant')->user()->id;
		$restaurant = Restaurant::where('user_id', $restaurant_id)->first();
		$id = $restaurant->id;
		$menu_time = $request->menu_time;
		$menu_id = $request->menu_id;
		if($locale == 'en') {
			if ($menu_id) {
				$restaurant_menu = Menu::where('restaurant_id', $id)->where('id', $menu_id)->first();
			}
			else {
				$restaurant_menu = new Menu;
				$restaurant_menu->restaurant_id = $id;
			}
		}
		else {
			if(!$menu_id) {
				return [
					'status' => false,
					'status_message' => __('messages.add_english_lang_first'),
				];
			}
			$restaurant_menu = Menu::where('restaurant_id', $id)->where('id', $menu_id)->first();
			$restaurant_menu = $restaurant_menu->getTranslationById($locale, $restaurant_menu->id);
		}
		$restaurant_menu->name = $request->menu_name;
		$restaurant_menu->save();
		$menu_id = $restaurant_menu->id;
		/*MenuTranslations::where('menu_id',$menu_id)->delete();
		foreach($request->translations ?: array() as $translation_data) {
			$translation = $restaurant_menu->getTranslationById(@$translation_data['locale'], $restaurant_menu->id);
			$translation->name = $translation_data['name'];
			$translation->save();
		}*/
		foreach ($menu_time as $time) {
			if ($time['id'] != '') {
				$menu_time = MenuTime::find($time['id']);
				$menu_time->day = $time['day'];
				$menu_time->start_time = $time['start_time'];
				$menu_time->end_time = $time['end_time'];
				$menu_time->save();
			}
			else {
				$menu_time = new MenuTime;
				if($request->menu_id == ''){
					$menu_time->menu_id = $restaurant_menu->id;
				}else
				{
					$menu_time->menu_id = $request->menu_id;
				}
				$menu_time->restaurant_id = $id;
				$menu_time->day = $time['day'];
				$menu_time->start_time = $time['start_time'];
				$menu_time->end_time = $time['end_time'];
				$menu_time->save();
			}
		}

		if ($request->menu_id) {
			return ['message' => true, 'menu_name' => $restaurant_menu->name];
		}
		else {
			$menu = Menu::with(['menu_category' => function ($query) {
				$query->with('all_menu_item');
			}])->where('restaurant_id', $id)->get();
		}

		$data['menu'] = $menu->map(function ($item) use ($locale) {
			$item->setDefaultLocale($locale);
			$menu_category = $item->menu_category->map(function ($item) use ($locale) {
				$item->setDefaultLocale($locale);
				$menu_item = $item->all_menu_item->map(function ($item) use ($locale) {
					$item->setDefaultLocale($locale);
					return [
						'menu_item_id' 		=> $item->id,
						'menu_item_name' 	=> $item->name,
						'menu_item_desc' 	=> $item->description,
						'menu_item_price' 	=> $item->price,
						'menu_item_tax' 	=> $item->tax_percentage,
						'menu_item_modifier'=> $item->menu_item_modifier,
					];
				})->toArray();
				return [
					'menu_category_id' 	=> $item->id,
					'menu_category' 	=> $item->name,
					'menu_item' 		=> $menu_item,
				];
			})->toArray();
			return [
				'menu_id' 		=> $item->id,
				'menu' 			=> $item->name,
				'menu_category' => $menu_category,
			];
		});
		return $data;
	}

	protected function uploadRestaurantImage($file,$menu_item_id)
	{
		$restaurant_id = get_current_restaurant_id();
		$file_path = $this->fileUpload($file, 'public/images/restaurant/' . $restaurant_id . '/menu_item');
		$this->fileSave('menu_item_image', $menu_item_id, $file_path['file_name'], '1');
		$orginal_path = Storage::url($file_path['path']);
		$size = get_image_size('item_image_sizes');
		foreach ($size as $new_size) {
			$this->fileCrop($orginal_path, $new_size['width'], $new_size['height']);
		}
	}

	public function update_menu_item(Request $request)
	{

		$locale = $request->locale;

		$restaurant_id = get_current_restaurant_id();

		try {
			\DB::beginTransaction();

			$update_data = [
				'price' => $request->menu_item_price,
				'tax_percentage' => $request->menu_item_tax,
				'type' => $request->menu_item_type,
				'status' => $request->menu_item_status,
			];

			if($locale == 'en') {
				$update_data['name'] = $request->menu_item_name;
				$update_data['description'] = $request->menu_item_desc;
				if ($request->menu_item_id) {
					$menu_item = MenuItem::where('id',$request->menu_item_id)
						->update($update_data);
					$menu_item_id = $request->menu_item_id;
					$menu_item = MenuItem::find($menu_item_id);

					$data['edit_menu_item_image'] = is_object($menu_item->menu_item_thump_image) ? '' : $menu_item->menu_item_thump_image;
					$data['edit_menu_item_name'] = $menu_item->name;
				}
				else {
					$update_data['menu_id'] = $request->menu_id;
					$update_data['menu_category_id'] = $request->category_id;
					$menu_item_id = MenuItem::insertGetId($update_data);
					$menu_item = MenuItem::find($menu_item_id);

					$data = [
						'menu_item_id' => $menu_item->id,
						'menu_item_name' => $menu_item->name,
						'menu_item_desc' => $menu_item->description,
						'menu_item_org_name' => $menu_item->org_name,
						'menu_item_org_desc' => $menu_item->org_description,
						'menu_item_price' => $menu_item->price,
						'menu_item_status' => $menu_item->status,
						'menu_item_type' => $menu_item->type,
						'menu_item_tax' => $menu_item->tax_percentage,
						'item_image' => is_object($menu_item->menu_item_thump_image) ? '' : $menu_item->menu_item_thump_image,
					];
				}
			}
			else {

				if(!$request->menu_item_id) {
					return [
						'status' => false,
						'status_message' => __('messages.add_english_lang_first'),
					];
				}

				$menu_item_id = $request->menu_item_id;

				MenuItem::where('id',$menu_item_id)->update($update_data);

				$menu_item = MenuItem::find($request->menu_item_id);
				$translation = $menu_item->getTranslationById($locale,$menu_item->id);
				$translation->name = $request->menu_item_name;
				$translation->description = $request->menu_item_desc;
				$translation->save();
				
				$data['edit_menu_item_image'] = is_object($menu_item->menu_item_thump_image) ? '' : $menu_item->menu_item_thump_image;
				$data['edit_menu_item_name'] = $request->menu_item_name;
			}

			if ($request->file('file')) {
				$this->uploadRestaurantImage($request->file('file'),$menu_item->id);
			}

			$data['edit_menu_item_image'] = is_object($menu_item->menu_item_thump_image) ? '' : $menu_item->menu_item_thump_image;
					$data['edit_menu_item_name'] = $request->menu_item_name;
			
			$item_modifiers = json_decode($request->item_modifiers,true);
			if(!isset($item_modifiers)) {
				$item_modifiers = array();
			}
			$modifier_update_ids = array();
			foreach ($item_modifiers as $modifier) {
				if($locale == 'en') {
					if(isset($modifier['id']) && $modifier['id'] != '') {
						$menu_modifier = MenuItemModifier::find($modifier['id']);
					}
					else {
						$menu_modifier = new MenuItemModifier;
					}

					$menu_modifier->menu_item_id = $menu_item->id;
					$menu_modifier->count_type = $modifier['count_type'];
					$menu_modifier->is_multiple = $modifier['is_multiple'] ?? 0;
					$menu_modifier->is_required = $modifier['is_required'] ?? 1;
					if($modifier['count_type'] == 0) {
						$menu_modifier->min_count = 0;
						$menu_modifier->max_count = $modifier['max_count'];
					}
					else {
						$menu_modifier->min_count = $modifier['min_count'];
						$menu_modifier->max_count = $modifier['max_count'] ?? null;	
					}
				}
				else {
					if(!isset($modifier['id']) || $modifier['id'] == '') {
						return [
							'status' => false,
							'status_message' => __('messages.add_english_lang_first'),
						];
					}
					$menu_modifier = MenuItemModifier::find($modifier['id']);
					$menu_modifier->menu_item_id = $menu_item->id;
					$menu_modifier->count_type = $modifier['count_type'];
					$menu_modifier->is_multiple = $modifier['is_multiple'] ?? 0;
					$menu_modifier->is_required = $modifier['is_required'] ?? 1;
					if($modifier['count_type'] == 0) {
						$menu_modifier->min_count = 0;
						$menu_modifier->max_count = $modifier['max_count'];
					}
					else {
						$menu_modifier->min_count = $modifier['min_count'];
						$menu_modifier->max_count = $modifier['max_count'] ?? null;	
					}
					$menu_modifier->save();
					$menu_modifier = $menu_modifier->getTranslationById($locale, $menu_modifier->id);
				}
				$menu_modifier->name = $modifier['name'];
				$menu_modifier->save();

				if($locale == 'en') {
					$menu_modifier_id = $menu_modifier->id;
				}
				else {
					$menu_modifier_id = $menu_modifier->menu_item_modifier_id;
				}

				array_push($modifier_update_ids, $menu_modifier_id);

				$modifier_item_update_ids = array();

				foreach ($modifier['menu_item_modifier_item'] as $modifier_item) {
					if($locale == 'en') {
						if(isset($modifier_item['id']) && $modifier_item['id'] != '') {
							$menu_modifier_item = MenuItemModifierItem::find($modifier_item['id']);
						}
						else {
							$menu_modifier_item = new MenuItemModifierItem;
						}
						$menu_modifier_item->menu_item_modifier_id = $menu_modifier_id;
						$menu_modifier_item->price = $modifier_item['price'] ?? 0;
						$menu_modifier_item->is_visible = $modifier_item['is_visible'] ?? '1';
					}
					else {
						if(!isset($modifier_item['id']) || $modifier_item['id'] == '') {
							return [
								'status' => false,
								'status_message' => __('messages.add_english_lang_first'),
							];
						}
						
						$menu_modifier_item = MenuItemModifierItem::find($modifier_item['id']);
						$menu_modifier_item->menu_item_modifier_id = $menu_modifier_id;
						$menu_modifier_item->price = $modifier_item['price'] ?? 0;
						$menu_modifier_item->is_visible = $modifier_item['is_visible'] ?? '1';
						$menu_modifier_item->save();

						$menu_modifier_item = $menu_modifier_item->getTranslationById($locale, $menu_modifier_item->id);
					}

					$menu_modifier_item->name = $modifier_item['name'];				
					$menu_modifier_item->save();
					if($locale == 'en') {
						$menu_modifier_item_id = $menu_modifier_item->id;
					}
					else {
						$menu_modifier_item_id = $menu_modifier_item->menu_item_modifier_item_id;
					}
					array_push($modifier_item_update_ids, $menu_modifier_item_id);
				}
				MenuItemModifierItem::where('menu_item_modifier_id',$menu_modifier_id)->whereNotIn('id',$modifier_item_update_ids)->delete();
			}

			// Delete Menu Item Modifier
			$menu_modifier_ids = MenuItemModifier::where('menu_item_id',$menu_item_id)->whereNotIn('id',$modifier_update_ids)->pluck('id')->toArray();
			MenuItemModifierItem::whereIn('menu_item_modifier_id',$menu_modifier_ids)->delete();
			MenuItemModifier::where('menu_item_id',$menu_item_id)->whereNotIn('id',$modifier_update_ids)->delete();
			
			$data['status'] = 'true';
			$menu_item = MenuItem::MenuRelations()->find($menu_item->id);
			$data['menu_item_modifier'] = $menu_item->menu_item_modifier;

			\DB::commit();
			return $data;
		}
		catch (\Exception $e) {
			\DB::rollback();

			if($request->menu_item_id) {
				$menu_item = MenuItem::with('menu_item_modifier.menu_item_modifier_item')->where('id',$request->menu_item_id)->limit(1)->get();
			
				$data['menu_item'] = $menu_item->map(function ($item)  use ($locale) {
					$item->setDefaultLocale($locale);

					$menu_item_modifier = $item->menu_item_modifier->map(function ($item)  use ($locale) {
						$item->setDefaultLocale($locale);
						
						$menu_item_modifier_item = $item->menu_item_modifier_item->map(function($item)  use ($locale) {
							$item->setDefaultLocale($locale);
							return [
								'id' 	=> $item->id,
								'name' 	=> $item->name,
								'price' => $item->price,
							];
						})->toArray();

						return [
							'id' 	=> $item->id,
							'name'		=> $item->name,
							'count_type'=> $item->count_type,
							'is_multiple'=> $item->is_multiple,
							'min_count'	=> $item->min_count,
							'max_count'	=> $item->max_count,
							'is_required'	=> $item->is_required,
							'menu_item_modifier_item' => $menu_item_modifier_item
						];
					})->toArray();

					return [
						'menu_item_id' 		=> $item->id,
						'menu_item_name' 	=> $item->name,
						'menu_item_desc' 	=> $item->description,
						'menu_item_org_name'=> $item->org_name,
						'menu_item_org_desc'=> $item->org_description,
						'menu_item_price' 	=> $item->price,
						'menu_item_tax' 	=> $item->tax_percentage,
						'menu_item_type' 	=> $item->type,
						'menu_item_status' 	=> $item->status,
						'item_image' 		=> is_object($item->menu_item_thump_image) ? '' : $item->menu_item_thump_image,
						'menu_item_modifier'=> $menu_item_modifier,
					];
				})->first();
			}
			$data['status'] = false;
			$data['error_messages'] = $e->getMessage();
			$data['status_message'] = trans('messages.restaurant.this_item_use_in_order_so_cant_delete');
			return $data;
		}
	}

	public function remove_menu_time(Request $request)
	{
		$menu_time = MenuTime::find($request->id);
		if($menu_time) {
			$menu_time->delete();
		}
	}

	public function delete_menu(Request $request)
	{
		try {
			\DB::beginTransaction();
			if ($request->category == 'item') {
				$key = $request->key;
				$menu_item_id = $request->menu['menu_category'][$request->category_index]['menu_item'][$key]['menu_item_id'];

				$menu_modifier_ids = MenuItemModifier::whereIn('menu_item_id',[$menu_item_id])->pluck('id')->toArray();
				MenuItemModifierItem::whereIn('menu_item_modifier_id',$menu_modifier_ids)->delete();
				MenuItemModifier::whereIn('menu_item_id',[$menu_item_id])->delete();
				MenuItem::find($menu_item_id)->delete();
				$data['status'] = 'true';
				\DB::commit();
				return $data;
			}
			else if ($request->category == 'category') {
				$key = $request->key;
				$menu_category_id = $request->menu['menu_category'][$key]['menu_category_id'];
				$delete_menu_item = MenuItem::where('menu_category_id', $menu_category_id)->get();
				
				foreach ($delete_menu_item as $key => $value) {
					MenuItem::find($value->id)->delete();
				}
				$delete_menu_item = MenuCategory::find($menu_category_id)->delete();
				\DB::commit();
			}
			else if ($request->category == 'modifier') {
				$key = $request->key;
				$menu_modifier = MenuItemModifier::find($key);
				$menu_modifier_item = MenuItemModifierItem::where('menu_item_modifier_id',$menu_modifier->id)->get();

				MenuItemModifierItem::where('menu_item_modifier_id',$menu_modifier->id)->delete();
				$menu_modifier->delete();

				$data['status'] = 'true';
				\DB::commit();
				return $data;
			}
			else {
				$key = $request->key;
				$menu_id = $request->menu['menu_id'];
				$delete_menu_item = MenuItem::where('menu_id', $menu_id)->get();
				
				foreach ($delete_menu_item as $key => $value) {
					MenuItem::find($value->id)->delete();
				}
				$delete_category = MenuCategory::where('menu_id', $menu_id)->get();
				foreach ($delete_category as $key => $value) {
					MenuCategory::find($value->id)->delete();
				}
				MenuTime::where('menu_id', $menu_id)->delete();
				Menu::find($menu_id)->delete();
				MenuTranslations::where('menu_id',$menu_id)->delete();
				$data['status'] = 'true';
				\DB::commit();
				return $data;
			}
		}
		catch (\Exception $e) {
			\DB::rollback();
			$data['status'] = 'false';
			if ($request->category == 'modifier') {
				$data['status_message'] = __('messages.restaurant.modifier_delete_warning');
			}
			else {
				$data['status_message'] = __('messages.restaurant.this_item_use_in_order_so_cant_delete');

			}
			return $data;
		}
	}

	/**
	* forget password page
	*/
	public function forget_password()
	{
		session::forget('otp_confirm');
		session::forget('password_code');
		return view('restaurant/forgot_password');
	}

	/**
	* otp confirm from mail
	*/
	public function mail_confirm()
	{
		$email = request()->email;
		if ($email == '' && session::has('email') == '') {
			$rules = [
				'email' => 'required|email',
			];
			$messages =
				[
				'email.required' => trans('messages.restaurant_dashboard.please_enter_your_email_address'),
			];
			$validator = Validator::make(request()->all(), $rules, $messages);
			if ($validator->fails()) {
				return back()->withErrors($validator)->withInput();
			}
		}
		$user_email = User::where(['type' => 1, 'email' => $email])->count();
		if ($user_email == 0) {
			flash_message('warning', trans('messages.driver.no_account_exist_for_email'));
			return redirect()->route('restaurant.forget_password');
		}
		if (session::has('email')) {
			$email = session::get('email');
		}
		$user_details = User::where(['type' => 1, 'email' => $email])->first();

		if (is_null($user_details)) {
			flash_message('warning', trans('messages.driver.no_account_exist_for_email'));
			return view('restaurant/forgot_password');
		}
		
		$otp = random_num(4);
		$user_details->otp = $otp;
		$user_details->save();
		$check_email = otp_for_forget_eater($email, $otp);
		if($check_email['status'] =='false'){
			flash_message("danger",$check_email['error']);
			return back();
		}

		session::put('email', $email);
		if ($user_details) {
			session::put('user_id', $user_details->id);
		}
		$this->view_data['user_id'] = session::get('user_id');
		return view('restaurant/forgot_password2', $this->view_data);
	}

	/**
	* set new password page
	*/
	public function set_password()
	{
		if (request()->code_confirm == '' && session::has('code_confirm') == '') {
			$rules = [
				'code_confirm' => 'required',
			];
			$messages =
				[
				'code_confirm.required' => trans('messages.restaurant_dashboard.please_enter_your_code'),
			];
			$validator = Validator::make(request()->all(), $rules, $messages);
			if ($validator->fails()) {
				flash_message('warning', trans('messages.restaurant_dashboard.please_enter_your_code'));
				return view('restaurant/forgot_password2');
			}
		}
		if (session::has('code_confirm')) {
			$code = session::get('code_confirm');
		}
		else {
			$code = request()->code_confirm;
		}

		$user_id = session::get('user_id');
		$session_code = User::find($user_id)->otp;
		if ($session_code != $code) {
			flash_message('warning', trans('messages.restaurant_dashboard.code_is_incorrect'));
			$this->view_data['user_id'] = $user_id;
			return view('restaurant/forgot_password2', $this->view_data);
		}
		$this->view_data['user_id'] = $user_id;
		if(Session::has('message')) {
			Session::forget('message');
		}
		return view('restaurant/reset_password', $this->view_data);
	}

	/**
	* set new password response
	*/
	public function change_password()
	{
		$user_id = request()->id;
		$password = request()->password;
		$user = User::find($user_id);
		$user->password = bcrypt($password);
		$user->save();
		if (Auth::guard('restaurant')->attempt(['email' => $user->email, 'password' => $password, 'type' => 1])) {
			$data = auth()->guard('restaurant')->user();
			return json_encode(['success' => 'true', 'data' => $data]);
		}
		return json_encode(['success' => 'none', 'data' => '']);
	}

	/**
	Payout details page
	*/
	public function payout_preference()
	{
		$this->view_data['user_id'] = auth()->guard('restaurant')->user()->id;
		$this->view_data['country'] = Country::all()->pluck('name', 'code');
		$this->view_data['country_list'] = Country::getPayoutCoutries();
		$this->view_data['stripe_data'] = site_setting('stripe_publish_key');
		$this->view_data['currency'] = Currency::where('status', '=', '1')->pluck('code', 'code');
		$this->view_data['iban_supported_countries'] = Country::getIbanRequiredCountries();
		$this->view_data['country_currency'] = Country::getCurrency();
		$this->view_data['mandatory'] = PayoutPreference::getAllMandatory();
		$this->view_data['mandatory_field'] = PayoutPreference::getMandatoryField();
		//dd($this->view_data['mandatory'], $this->view_data['country_list']);
		$this->view_data['branch_code_required'] = Country::getBranchCodeRequiredCountries();
		$weekly_payout = Payout::userId([$this->view_data['user_id']])
			->get()
			->groupBy(function ($date) {
				return Carbon::parse($date->created_at)->format('W');
			});
		$count = 0;
		$week = [];
		foreach ($weekly_payout as $key => $value) {
			$total = 0;
			$tax = 0;
			$subtotal = 0;
			$order_total = 0;
			$gofer_fee = 0;

			$total_owe_amount = 0;

			$count = count($value);
			$order_status = [];
			$payout_status = [];
			$i = 0;
			$order_data = [];
			$penalty = 0;
			$paid_penalty = 0;
			foreach ($value as $payout) {
				$total += (float) $payout->amount;
				$currency_code = $payout->order->currency->OriginalSymbol;
				// if ($payout->status == 0) {
				$payout_status[$i] = $payout->status_text;
				$order_status[$i] = $payout->order->status_text;
				// }
				$tax += $payout->order->tax;
				$paid_penalty += $payout->order->res_applied_penality;
				$penalty += ($payout->order->penality_details) ? ($payout->order->penality_details->restaurant_penality) : 0;
				$subtotal += (float) $payout->order->subtotal;
				$order_total += (float) $payout->order->restaurant_total;
				$gofer_fee += (float) $payout->order->restaurant_commision_fee;

				$total_owe_amount += (float) $payout->order->applied_owe;

				$year = date('Y', strtotime($payout->created_at));
				$order_data[$i] = $payout;
				$i++;
			}

			$total_penalty = $penalty - $paid_penalty > 0 ? ($penalty - $paid_penalty) : 0;
			$date = getWeekDates($year, $key);
			$format_date = date('d', strtotime($date['week_start'])).' '.trans('messages.driver.'.date('M', strtotime($date['week_start']))) . ' - ' . date('d', strtotime($date['week_end'])).' '.trans('messages.driver.'.date('M', strtotime($date['week_end'])));
			$table_date = date('Y-m-d', strtotime($date['week_start'])) . ' , ' . date('Y-m-d', strtotime($date['week_end']));
			$status = array_intersect(["pending","accepted","delivery"], array_unique($order_status)) ? "Pending" : "Completed";
			$pay_status = in_array("pending", array_unique($payout_status)) ? "Pending" : "Completed";
			$week[] = ['week' => $format_date,
				'table_week' => $table_date,
				'total_owe_amount' => numberFormat($total_owe_amount),
				'total_payout' => numberFormat($total),
				'year' => $year,
				'tax' => numberFormat($tax),
				'currency_symbol' => $currency_code,
				'subtotal' => numberFormat($subtotal),
				'status' =>  trans('messages.restaurant.'.$status),
				'total_amount' => numberFormat($order_total),
				'payout_status' => trans('messages.restaurant.'.$pay_status),
				'gofer_fee' => numberFormat($gofer_fee),
				'penalty' => numberFormat($total_penalty),
				'paid_penalty' => numberFormat($paid_penalty),
				'count' => $count,
				'order_detail' => $order_data,
				'date' => $date['week_start']];
		}
		$current_week = date('d M', strtotime('last monday')) . ' - ' . date('d M', strtotime('next sunday'));
		$this->view_data['current_week_orders'] = array_sum(array_column($week, 'count'));
		$this->view_data['current_week_symbol'] = @$currency_code;
		$this->view_data['current_week_profit'] = numberFormat(array_sum(array_column($week, 'total_payout')));
		$this->view_data['current_week'] = $current_week;
		$this->view_data['paginate'] = $weekly_payout;
		$this->view_data['weekly_payouts'] = $week;
		$this->view_data['payout_preference'] = PayoutPreference::where('user_id', $this->view_data['user_id'])->first();

		$restaurant_id = get_current_restaurant_id();
		$this->view_data['current_week_owe_amount'] = DriverOweAmount::where('user_id', $this->view_data['user_id'])
			->get()
			->sum('amount');

		// Cash Total Earnings
		$cash_total_earnings = 0;
		$cash_orders = Order::where('restaurant_id', $restaurant_id)
			->where('payment_type', 0)
			->where('status', 6)
			->get();

		logger( json_encode($cash_orders) );
		if($cash_orders) {
			foreach ($cash_orders as $key => $value) {
				$cashTotal = $value->subtotal + $value->tax + $value->delivery_fee - $value->restaurant_commision_fee;
				logger('cashTotal : '.$cashTotal);
				$cash_total_earnings += $cashTotal;
			}
		}
		logger('cash_total_earnings : '.$cash_total_earnings);

		$this->view_data['cash_total_earnings'] = $cash_total_earnings;
		// if($cash_total_earnings>0) {
			// $this->view_data['current_week_symbol'] = default_currency_symbol();
			// $this->view_data['current_week_profit'] += $cash_total_earnings;
		// }

		return view('restaurant/payout_preference', $this->view_data);
	}

	/**
	Payout daywise details page
	*/
	public function payout_daywise_details()
	{
		$week_data = request()->week;
		$start_end = explode(',', $week_data);
		$this->view_data['user_id'] = auth()->guard('restaurant')->user()->id;
		$this->view_data['country'] = Country::all()->pluck('name', 'code');
		$this->view_data['country_list'] = Country::getPayoutCoutries();
		$this->view_data['stripe_data'] = site_setting('stripe_publish_key');
		$this->view_data['currency1'] = Currency::where('status', '1')->pluck('code', 'code');
		$this->view_data['iban_supported_countries'] = Country::getIbanRequiredCountries();
		$this->view_data['country_currency'] = Country::getCurrency();
		$this->view_data['mandatory'] = PayoutPreference::getAllMandatory();
		$this->view_data['branch_code_required'] = Country::getBranchCodeRequiredCountries();
		$daily_payout = Payout::userId([$this->view_data['user_id']])
			->whereBetween('created_at', [$start_end[0], $start_end[1]])
			->get()
			->groupBy(function ($date) {
				return Carbon::parse($date->created_at)->format('D');
			});
		$count = 0;
		$week = [];

		foreach ($daily_payout as $key => $value) {
			$total = 0;
			$tax = 0;
			$subtotal = 0;
			$order_total = 0;
			$gofer_fee = 0;

			$total_owe_amount = 0;

			$count = count($value);
			$payout_status = [];
			$order_status = [];
			$i = 0;
			$paid_penalty = 0;
			$penalty = 0;
			$order_data = [];
			foreach ($value as $payout) {
				$total += (float) $payout->amount;
				$amount[$i] = $payout->amount;
				$currency_code = $payout->order->currency->symbol;
				$payout_status[$i] = $payout->status_text;
				$order_status[$i] = $payout->order->status_text;

				$tax += (float) $payout->order->tax;
				$subtotal += (float) $payout->order->subtotal;
				$order_total += (float) $payout->order->restaurant_total;
				$gofer_fee += (float) $payout->order->restaurant_commision_fee;

				$total_owe_amount += (float) $payout->order->applied_owe;

				$day_val = date('d M', strtotime($payout->created_at));
				$table_date = date('Y-m-d', strtotime($payout->created_at));
				$order_data[$i] = $payout;
				$paid_penalty += @$payout->order->res_applied_penality;
				$penalty += ($payout->order->penality_details) ? ($payout->order->penality_details->restaurant_penality) : 0;
				$i++;
			}
			$total_penalty = $penalty - $paid_penalty > 0 ? ($penalty - $paid_penalty) : 0;
			$format_date = $day_val;
			$status = array_intersect(["pending","accepted","delivery"], array_unique($order_status)) ? "Pending" : "Completed";
			$pay_status = in_array("pending", array_unique($payout_status)) ? "Pending" : "Completed";
			$week[] = [
				'week' =>	date('d', strtotime($format_date)).' '.trans('messages.driver.'.date('M', strtotime($format_date))),
				'table_date' => $table_date,

				'total_owe_amount' => numberFormat($total_owe_amount),

				'total_payout' => numberFormat($total),
				'day' => $day_val,
				'tax' => numberFormat($tax),
				'currency_symbol' => $currency_code,
				'subtotal' => numberFormat($subtotal),
				'status' => trans('messages.restaurant.'.$status),
				'total_amount' => numberFormat($order_total),
				'payout_status' =>  trans('messages.restaurant.'.$pay_status),
				'gofer_fee' => numberFormat($gofer_fee),
				'paid_penalty' => numberFormat($paid_penalty),
				'penalty' => numberFormat($total_penalty),
				'count' => $count,
				'order_detail' => $order_data
			];
		}
		$current_week = date('d M', strtotime('last monday')) . ' - ' . date('d M', strtotime('next sunday'));
		$this->view_data['current_week_orders'] = array_sum(array_column($week, 'count'));
		$this->view_data['current_week_symbol'] = @$currency_code;
		$this->view_data['current_week_profit'] = numberFormat(array_sum(array_column($week, 'total_payout')));
		$this->view_data['current_week'] = $current_week;
		$this->view_data['paginate'] = $daily_payout;
		$this->view_data['weekly_payouts'] = $week;
		$this->view_data['payout_preference'] = PayoutPreference::where('user_id', $this->view_data['user_id'])->first();

		return view('restaurant/payout_preference1', $this->view_data);
	}

	/**
	* export table data for payout as weekly
	*/
	public function get_export()
	{
		$user_id = auth()->guard('restaurant')->user()->id;
		$week_data = request()->week;
		$start_end = explode(',', $week_data);
		$daily_payout = Payout::userId([$user_id])
			->whereBetween('created_at', [$start_end[0], $start_end[1]])
			->get()
			->groupBy(function ($date) {
				return Carbon::parse($date->created_at)->format('D');
			});
		$count = 0;
		$week = [];
		foreach ($daily_payout as $key => $value) {
			$total = 0;
			$tax = 0;
			$subtotal = 0;
			$order_total = 0;
			$gofer_fee = 0;

			$total_owe_amount = 0;

			$count = count($value);
			$status = 0;
			$paid_penalty = 0;
			$penalty = 0;
			$i = 0;
			$order_data = [];
			foreach ($value as $payout) {
				$total += (float) $payout->amount;
				$amount[$i] = $payout->amount;
				$currency_code = $payout->order->currency->symbol;
				$status_text = $payout->status_text;
				$tax += (float) $payout->order->tax;
				$subtotal += (float) $payout->order->subtotal;
				$order_total += (float) $payout->order->restaurant_total;
				$gofer_fee += (float) $payout->order->restaurant_commision_fee;

				$total_owe_amount += (float) $payout->order->applied_owe;

				$day_val = date('d M', strtotime($payout->created_at));
				$paid_penalty += $payout->order->res_applied_penality;
				$penalty += ($payout->order->penality_details) ? ($payout->order->penality_details->restaurant_penality) : 0;
				$order_data[$i] = $payout;
				$i++;
			}
			$total_penalty = $penalty - $paid_penalty > 0 ? ($penalty - $paid_penalty) : 0;
			$format_date = $day_val;
			$week[] = [
				'Date' 			=> $format_date,
				'Orders' 		=> $count,
				'Sale' 			=> $subtotal,
				'Tax' 			=> $tax,
				'Total' 		=> $order_total,
				site_setting('site_name')." fee" => $gofer_fee,
				'Applied Owe Amount' 	=> $total_owe_amount,
				'Net Payout'	=> $total,
				'Payout Status' => $status_text,
				'Penalty' 		=> $total_penalty,
				'Paid penalty'	=> $paid_penalty,
			];
		}

		$filename = 'Payout_' . time().'-report.csv';
		return \Excel::download(new ArrayExport($week),$filename);
	}

	/**
	* export table data for payout as day wise
	*/
	public function get_order_export()
	{
		$user_id = auth()->guard('restaurant')->user()->id;
		$week_data = request()->date;
		$daily_payout = Payout::userId([$user_id])
			->whereDate('created_at', $week_data)
			->get()
			->groupBy('order_id');
		$count = 0;
		$week = [];

		foreach ($daily_payout as $key => $value) {
			$total = 0;
			$tax = 0;
			$subtotal = 0;
			$order_total = 0;
			$gofer_fee = 0;

			$total_owe_amount = 0;

			$i = 0;
			$order_data = [];
			foreach ($value as $payout) {
				$total = (float) $payout->amount;
				$amount[$i] = $payout->amount;
				$currency_code = $payout->order->currency->symbol;
				$notes = $payout->order->restaurant_notes;
				$status_text = $payout->status_text;
				$tax = (float) $payout->order->tax;
				$count = $payout->order->id;
				$subtotal = (float) $payout->order->subtotal;
				$order_total = (float) $payout->order->restaurant_total;
				$gofer_fee = (float) $payout->order->restaurant_commision_fee;

				$total_owe_amount = (float) $payout->order->applied_owe;

				$day_val = date('d M', strtotime($payout->created_at));
				$paid_penalty = @$payout->order->res_applied_penality;
				$penalty = ($payout->order->penality_details) ? ($payout->order->penality_details->restaurant_penality) : 0;
				$order_data[$i] = $payout;
				$i++;
			}
			$total_penalty = $penalty - $paid_penalty > 0 ? ($penalty - $paid_penalty) : 0;
			$format_date = $day_val;
			$week[] = [
				'Date' 			=> $format_date,
				'OrderId' 		=> $count,
				'Sale' 			=> $subtotal,
				'Tax' 			=> $tax,
				'Total' 		=> $order_total,
				site_setting('site_name')." fee"  	=> $gofer_fee,
				'Applied Owe Amount' 	=> $total_owe_amount,
				'Net Payout'	=> $total,
				'Payout Status' => $status_text,
				'Penalty' 		=> $total_penalty,
				'Paid penalty' 	=> $paid_penalty,
				'Notes' 		=> $notes,
			];
		}

		$filename = 'Payout_' . time().'-report.csv';
		return \Excel::download(new ArrayExport($week),$filename);
	}

	/**
	* stripe account creation and payout details store
	*/
	public function update_payout_preferences(Request $request)
	{
		$stripe_payout = resolve('App\Repositories\StripePayout');
		$validate_data = $stripe_payout->validateRequest($request);
		if($validate_data) {
            return $validate_data;
        }

		$user = auth()->guard('restaurant')->user();
		$user_id = $user->id;
        $stripe_document 	= '';
        $payout_method 		= 'Manual';
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

		if ($request->payout_country != 'OT') {

			if ($request->payout_country == 'JP') {
				$address_kanji = array(
					'owner_name'   => $request->account_owner_name,
					'line1'         => $request->kanji_address1,
					'town'         	=> $request->kanji_address2,
					'city'          => $request->kanji_city,
					'state'         => $request->kanji_state,
					'postal_code'   => $request->kanji_postal_code,
					'country'       => $request->payout_country,
				);
			}
			$payout_method = 'Stripe';
			$stripe_preference = $stripe_payout->createPayoutPreference($request);
			if(!$stripe_preference['status']) {
				flash_message('danger', $stripe_preference['status_message']);
				return back();
			}
			$recipient = $stripe_preference['recipient'];
			if(isset($document_path)) {
				$document_result = $stripe_payout->uploadDocument($document_path,$recipient->id);
				if(!$document_result['status']) {
					flash_message('danger', $document_result['status_message']);
					return back();
				}
				$stripe_document = $document_result['stripe_document'];
			}

			if(isset($a_document_path)) {
				$stripe_document = (isset($document_path)) ? $stripe_document : '';
				$document_result = $stripe_payout->uploadAdditonalDocument($stripe_document,$a_document_path,$recipient->id,$recipient->individual->id);
				if(!$document_result['status']) {
					flash_message('danger', $document_result['status_message']);
					return back();
				}
			}
		}
		
		$payout_preference = PayoutPreference::firstOrNew(['user_id' => $user_id]);
		$payout_preference->country 		= $request->payout_country;
		$payout_preference->currency_code 	= ($request->payout_country != 'OT') ? $request->currency : '';
		$payout_preference->routing_number 	= $request->routing_number ?? $request->bank_code ?? '';
		$payout_preference->account_number 	= $request->account_number;
		$payout_preference->holder_name 	= $request->account_holder_name;
		$payout_preference->holder_type 	= $account_holder_type;
		$payout_preference->paypal_email 	= isset($recipient->id) ? $recipient->id : $user->email;
		$payout_preference->address1 	= $request->address1;
		$payout_preference->address2 	= $request->address2;
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
		$payout_preference->ssn_last_4 		= $request->payout_country == 'US' ? $request->ssn_last_4 : '';
		$payout_preference->payout_method 	= $payout_method;
		$payout_preference->address_kanji 	= isset($address_kanji) ? json_encode($address_kanji) : json_encode([]);
		$payout_preference->save();
		
		$payout_check = PayoutPreference::where('user_id', auth()->guard('restaurant')->user()->id)->where('default', 'yes')->count();
		if ($payout_check == 0) {
			$payout_preference->default = 'yes';
			$payout_preference->save();
		}
		
		flash_message('success', trans('admin_messages.updated_successfully'));
		return back();
	}

	/**
	* get payout preference data
	*
	* @return data
	*/
	public function get_payout_preference(Request $request)
	{
		$data = PayoutPreference::where('user_id', $request->id)->first();
		return $data;
	}

	/**
	* Logout restaurant and redirect to login page
	*
	* @return \Illuminate\Http\Response
	*/
	public function logout()
	{
		Auth::guard('restaurant')->logout();
		return redirect()->route('restaurant.login'); // Redirect to login page
	}

	public function review_details($restaurant_id = 1)
	{
		$review = DB::table('menu_item')
			->join('menu', 'menu_item.menu_id', '=', 'menu.id')
			->join('review', 'review.reviewee_id', '=', 'menu_item.id')
			->leftjoin('review_issue', 'review_issue.review_id', '=', 'review.id')
			->leftjoin('issue_type', 'review_issue.issue_id', '=', 'issue_type.id')
			->select('review.reviewee_id as reviewee_id_', 'menu_item.name', 'menu_item.id', DB::raw('sum(review.is_thumbs) as thumbs'), DB::raw('(SELECT count(reviewee_id) as count  from review where reviewee_id_ = review.reviewee_id) as count_thumbs'), DB::raw('GROUP_CONCAT(issue_type.name )as issues'), DB::raw('GROUP_CONCAT(issue_type.id ) as issues_id'), DB::raw('GROUP_CONCAT(review.comments ) as review_comments'))
			->where('menu.restaurant_id', $restaurant_id)
			->whereRaw("date(review.created_at) <= date_sub(now(), interval -3 month)")
			->where('review.type', 0)
			->groupBy('menu_item.id')
			->get();
			
		return $review;
	}
}