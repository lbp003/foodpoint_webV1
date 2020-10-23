<?php

/**
 * Restaurant Model
 *
 * @package    GoferEats
 * @subpackage Model
 * @category   Restaurant
 * @author     Trioangle Product Team
 * @version    1.2
 * @link       http://trioangle.com
 */

namespace App\Models;

use App\Models\Currency;
use Auth;
use DB;
use Illuminate\Database\Eloquent\Model;
use JWTAuth;

class Restaurant extends Model {

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */

	protected $table = 'restaurant';
	protected $appends = ['convert_mintime', 'convert_maxtime', 'restaurant_image', 'restaurant_logo', 'banner', 'currency_symbol', 'wishlist_count', 'restaurant_next_opening','owe_amount' ,'app_delivery_mode', 'app_delivery_mode_text','check_restaurant_logo'];
	public $timestamps = false;

	public $statusArray = [
		'offline' => 0,
		'online' => 1,
	];
	public $fileArray = [
		'restaurant_banner' => 3,
		'restaurant_logo' 	=> 4,
	];
	public $userstatusArray = [
		'inactive' => 0,
		'active' => 1,
	];

	public $image_size = [

		0 => '520x280',
		1 => '480x320',
		2 => '520x320',
		3 => '100x100',

	];

	public $logo_size = [
		0 => '370x230'
	];

	public function scopeAuthUser($query) {
		$user_id = '';
		if (request()->segment(1) == 'api') {
			$user_id = JWTAuth::parseToken()->authenticate()->id;
		}
		return $query->user($user_id);
	}

	/**
	 * To check the status
	 */
	public function scopeStatus($query, $status = 'online') {
		$status_value = $this->statusArray[$status];
		return $query->where('status', $status_value);
	}

	public function scopeDeliveryMode($query, $mode = '2') {
		return $query->whereRaw("find_in_set('".$mode."', delivery_mode)");
	}

	public function scopeUserStatus($query, $status = 'active') {

		$status_value = $this->userstatusArray[$status];

		$result = $query->whereHas('user', function ($query) {

			$query->status();
		});

		return $result;

	}

	public function scopeUser($query, $user_id)
	{
		return $query->where('user_id', $user_id);
	}

	public function scopeMenuRelations($query)
	{
		return $query->with(
			[
				'restaurant_menu' => function ($query) {
					$query->menuRelations();
				},
			]
		);
	}

	protected function updateRestaurantTimeZone()
	{
		if (isset($this->user_address)) {
			$timezone = $this->user_address->default_timezone;
			date_default_timezone_set($timezone);
		}
		return true;
	}

	// Join with restaurant tablehasMany
	public function restaurant_cuisine()
	{
		return $this->hasMany('App\Models\RestaurantCuisine', 'restaurant_id', 'id');
	}
	// Join with restaurant document table
	public function restaurant_document()
	{
		return $this->hasMany('App\Models\RestaurantDocument', 'restaurant_id', 'id');
	}

	// Join with restaurant_preparation_time table
	public function restaurant_preparation_time()
	{
		return $this->hasMany('App\Models\RestaurantPreparationTime', 'restaurant_id', 'id');
	}

	// Join with restaurant_time table
	public function restaurant_all_time()
	{
		return $this->hasMany('App\Models\RestaurantTime', 'restaurant_id', 'id');
	}

	// Join with restaurant_time table
	public function restaurant_time()
	{
		$this->updateRestaurantTimeZone();
		return $this->belongsTo('App\Models\RestaurantTime', 'id', 'restaurant_id')->isActive()->where('day', date('N'));
	}

	public  function getAppDeliveryModeAttribute()
	{

		$delivery_mode = explode(',',$this->attributes['delivery_mode']);
        if(sizeof(array_filter($delivery_mode)) > 0){
			foreach($delivery_mode as $mode){
				$data[] = [
					'mode_id' => $mode,
					'mode_name'=>($mode == 1)? 'Pickup' : 'Delivery'
				];
			}
	    } 

		return  $data ?? [];
	}

	public  function getAppDeliveryModeTextAttribute()
	{
		$data = '-';

		if($this->app_delivery_mode) {
			$new_data = [];
			foreach ($this->app_delivery_mode as $mode) {
				$new_data[] = $mode['mode_name'];
			}
			$data = implode(', ', $new_data);
		}
		return  $data;
	}
	
	public function getRestaurantNextOpeningAttribute()
	{
		$user_id = get_current_login_user_id();
		$address = get_user_address($user_id);
		$this->updateRestaurantTimeZone();

		if (isset($address) && $address->order_type == 1) {
			$date = strtotime($address->delivery_time);
		}
		else {
			$date = time();
		}

		$cur_Date = date('N', $date);

		$restaurant = RestaurantTime::where('restaurant_id', $this->id)->where('status', '1')->orderBy('day', 'ASC')->get()->toArray();

		if ($restaurant) {
			$days = array_column($restaurant, 'day');
			$match = array_search($cur_Date, $days);
			if (isset($match) && $match != false) {

				if (strtotime($restaurant[$match]['end_time']) >= $date) {
					return trans('api_messages.restaurant.opens_at') . $restaurant[$match]['start_time'];
				}

				if ($match == count($days) - 1) {
					return trans('api_messages.restaurant.opens_on') . $restaurant[0]['day_name'];
				}

				if ($match != count($days) - 1) {
					return trans('api_messages.restaurant.opens_on') . $restaurant[$match + 1]['day_name'];
				}
			}
			else {

				$match = array_search($cur_Date + 1, $days);
				if ($match) {
					return trans('api_messages.restaurant.opens_on') . $restaurant[$match]['day_name'];
				}
				$match = array_search($cur_Date + 2, $days);
				if ($match) {
					return trans('api_messages.restaurant.opens_on') . $restaurant[$match]['day_name'];
				}
				return trans('api_messages.restaurant.opens_on') . $restaurant[0]['day_name'];
			}
		}
	}

	public function user()
	{
		return $this->belongsTo('App\Models\User', 'user_id', 'id');
	}

	public function user_address()
	{
		return $this->belongsTo('App\Models\UserAddress', 'user_id', 'user_id');
	}

	//recommend_status
	public function getRecommendStatusAttribute()
	{
		return get_status_yes($this->attributes['recommend']);
	}
	public function scopelocation($query, $latitude, $longitude)
	{
		$km = site_setting('restaurant_km');

		if ($latitude != '') {
			return $query->whereHas('user_address',function ($query) use ($latitude, $longitude, $km) {
				$query->select(DB::raw('*,( 6371 * acos( cos( radians(' . $latitude . ') ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(' . $longitude . ') ) + sin( radians(' . $latitude . ') ) * sin( radians( latitude ) ) ) ) as distance'))
					->having('distance', '<=', $km);
			});
		}
	}

	public function getWishlistCountAttribute()
	{
		return Wishlist::where('restaurant_id', $this->restaurant_id)->count();
	}

	//restaurant images

	public function getBannerAttribute()
	{
		$restaurant_id = $this->attributes['id'];

		$image = File::where('type', $this->fileArray['restaurant_banner'])->where('source_id', $restaurant_id)->first();

		if ($image) {

			$name = explode("/", $image->image_name);
			$filename = end($name);

			$url = explode('/', $image->image_name);
			array_pop($url);
			$url = implode('/', $url);

			$name = explode('.', $filename);
			$filename = $name[0];
			$extension = @$name[1] ?? 'png';
			$image = [

				'small' => $url . '/' . $filename . '_' . $this->image_size['0'] . '.' . $extension,
				'medium_x' => $url . '/' . $filename . '_' . $this->image_size['1'] . '.' . $extension,
				'medium' => $url . '/' . $filename . '_' . $this->image_size['2'] . '.' . $extension,
				'original' => $image->image_name,
				'smallest' => $url . '/' . $filename . '_' . $this->image_size['3'] . '.' . $extension,

			];

			return $image;
		} else {
			return $image = [

				'small' => getEmptyRestaurantImage(),
				'medium_x' => getEmptyRestaurantImage(),
				'medium' => getEmptyRestaurantImage(),
				'original' => getEmptyRestaurantImage(),
				'smallest' => getEmptyRestaurantImage(),

			];
		}
	}
	public function getRestaurantImageAttribute()
	{

		$restaurant_id = $this->attributes['id'];

		$image = File::where('type', $this->fileArray['restaurant_banner'])->where('source_id', $restaurant_id)->first();

		if ($image) {

			$image = $image->image_name;

			return $image;
		} else {
			return getEmptyRestaurantImage();
		}
	}

	public function getRestaurantLogoAttribute()
	{
		$restaurant_id = $this->attributes['id'];
		$image = File::where('type', $this->fileArray['restaurant_logo'])->where('source_id', $restaurant_id)->first();
		if ($image) {

			$url = explode('/', $image->image_name);
			$filename = end($url);
			array_pop($url);
			$url = implode('/', $url);
			$name = explode('.', $filename);
			$filename = $name[0];
			$extension = @$name[1] ?? 'png';
			$image_logo = $url . '/' . $filename . '_' . $this->logo_size['0'] . '.' . $extension;
			// $image = $image->image_name;
			return $image_logo;
		} else {
			return getEmptyRestaurantLogo();
		}
	}

	public function getcheckRestaurantLogoAttribute()
	{
		$restaurant_id = $this->attributes['id'];
		$image = File::where('type', $this->fileArray['restaurant_logo'])->where('source_id', $restaurant_id)->first();
		if ($image) {
			return $image;
		} else {
			return false;
		}
	}

	// Join with restaurant_offer table

	public function restaurant_offer()
	{

		$date = \Carbon\Carbon::today();

		return $this->hasMany('App\Models\RestaurantOffer', 'restaurant_id', 'id')->where('status', '1')
			->where('start_date', '<=', $date)->where('end_date', '>=', $date)->orderBy('id', 'desc');
	}

	// Join with file table

	public function file()
	{
		return $this->hasMany('App\Models\File', 'source_id', 'id');
	}

	// Join with Menu table

	public function restaurant_menu()
	{
		return $this->hasMany('App\Models\Menu', 'restaurant_id', 'id');
	}

	// Join with Review table

	public function review()
	{
		return $this->belongsTo('App\Models\Review', 'id', 'reviewee_id')->withDefault();

	}

	// Join with all Review table

	public function all_review()
	{
		return $this->hasMany('App\Models\Review', 'reviewee_id', 'id')->where('type', 2);

	}

	// Join with Order table

	public function order()
	{
		return $this->hasMany('App\Models\Order', 'restaurant_id', 'id');

	}

	// Join with wished table

	public function wished()
	{
		return $this->hasMany('App\Models\Wishlist', 'restaurant_id', 'id');
	}

	// Get Restaurnat Wishlist

	public function wishlist($user_id, $restaurant_id) {
		$wishlist = Wishlist::where('user_id', $user_id)->where('restaurant_id', $restaurant_id)->first();

		if ($wishlist) {
			return 1;
		}
		return 0;
	}

	public function getConvertMintimeAttribute()
	{
		$time = $this->preparationTime();
		return convert_minutes($time);
	}

	public function getConvertMaxtimeAttribute()
	{
		$time = $this->preparationTime();
		return convert_minutes($time) + 10;
	}

	public function getStatusTextAttribute()
	{

		return array_search($this->status, $this->statusArray);
	}

	public function preparationTime()
	{
		$user_id = get_current_login_user_id();
		$address = get_user_address($user_id);
		if (isset($address) && $address->order_type == 1) {
			$date = strtotime($address->delivery_time);
		}
		else {
			$date = time();
		}

		$day = date('N', $date);
		$restaurant_preparation_time = $this->restaurant_preparation_time()->isActive()->where('day', $day)->first();

		if ($restaurant_preparation_time) {
			$preparation_time = $restaurant_preparation_time->attributes['max_time'];
		} else {
			$preparation_time = $this->attributes['max_time'];
		}

		return $preparation_time;
	}

	public function getRestaurantPreparationTime($date)
	{
		$date = strtotime($date);
		$day = date('N', $date);

		$restaurant_preparation_time = $this->restaurant_preparation_time()->isActive()->where('day', $day)->first();

		if ($restaurant_preparation_time) {
			$preparation_time = $restaurant_preparation_time->attributes['max_time'];
		}
		else {
			$preparation_time = $this->attributes['max_time'];
		}

		return $preparation_time;
	}

	public function currency()
	{
		return $this->belongsTo('App\Models\Currency', 'currency_code', 'code');
	}

	public function getCurrencySymbolAttribute()
	{

		$currency_code = $this->attributes['currency_code'];

		$symbol = Currency::where('code', $currency_code)->first()->symbol;

		return $symbol;
	}

	public function getProfileStepAttribute()
	{
		$image = File::where('type', $this->fileArray['restaurant_banner'])->where('source_id', $this->attributes['id'])->first();
		if ($this->user->mobile_no_verify == 1 && $this->user->date_of_birth && $this->attributes['description']  && $this->attributes['price_rating'] && $image) {
			return true;
		} else {
			return false;
		}

	}

	public function getOweAmountAttribute() {

		$owe_amount = DriverOweAmount::where('user_id', get_current_login_user_id())
			->where('restaurant_id', $this->restaurant_id)
			->first();

		if ($owe_amount) {
			return $owe_amount->amount;
		}

		return '0';

	}
}
