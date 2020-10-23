<?php

/**
 * ProfileController
 *
 * @package    GoferEats
 * @subpackage  Controller
 * @category    Profile
 * @author      Trioangle Product Team
 * @version     1.3
 * @link        http://trioangle.com
 */

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Cuisine;
use App\Models\File;
use App\Models\Restaurant;
use App\Models\RestaurantCuisine;
use App\Models\RestaurantDocument;
use App\Models\RestaurantOffer;
use App\Models\RestaurantTime;
use App\Models\User;
use App\Models\UserAddress;
use App\Traits\FileProcessing;
use Auth;
use Illuminate\Http\Request;
use Storage;
use Validator;

class ProfileController extends Controller
{
	use FileProcessing;

	public function index(Request $request)
	{
		$restaurant_id = get_current_restaurant_id();
		$data['restaurant'] = $restaurant = Restaurant::where('id', $restaurant_id)->first();
		$data['cuisine'] = Cuisine::Active()->pluck('name', 'id');
		$data['delivery_mode'] = array('1'=>trans('admin_messages.pickup_rest'),'2'=>trans('admin_messages.delievery_door'));
		$data['restaurant_cuisine'] = $restaurant->restaurant_cuisine()->pluck('cuisine_id', 'id')->toArray();
		$data['address'] = UserAddress::where('user_id', $restaurant->user_id)->where('default', 1)->first();
		$data['basic'] = User::where('id', $restaurant->user_id)->first();
		$data['open_time'] = (count($restaurant->restaurant_all_time) > 0) ? $restaurant->restaurant_all_time()->orderBy('day', 'ASC')->get() : [array('day' => '')];

		$data['country'] = Country::Active()->get();
		$data['documents'] = RestaurantDocument::with('file')->where('restaurant_id', $restaurant_id)->get();
		if (count($data['documents']) < 1) {
			$data['documents'] = array(array('name' => ''));
		}
		$data['banner_image'] = File::where('type', 3)->where('source_id', $restaurant_id)->first();
		$data['restaurant_logo'] = File::where('type', 4)->where('source_id', $restaurant_id)->first();

		$data['map_key'] = site_setting('google_api_key');
   		$data['time_options'] = array();
        for($i=0; $i < 24;) {
            if ((int) $i == $i)
                $a=$i.":00";
            else
                $a=($i-0.5).":30";
            $data['time_options'][date("H:i:s", strtotime($a))] = date("g:i", strtotime($a)).' '.trans('messages.driver.'.date("a", strtotime($a)));
            $i= $i+0.5;
        }

		if ($request->getMethod() == 'GET') {
			return view('restaurant.profile', $data);
		}

		$all_request = $request->all();
		if ($request->dob) {
			$all_request['dob'] = date('m/d/Y', strtotime($request->dob));
		}
		$rules = array(
			'restaurant_name' 	=> 'required',
			'first_name' 		=> 'required',
			'last_name' 		=> 'required',
			'address' 			=> 'required',
			'email' 			=> 'required|email',
			'dob' 				=> 'required|date_format:m/d/Y|before:18 years ago',
			'price_rating' 		=> 'required',
			'mobile_number'		=> 'required|regex:/[0-9]{6}/',
			'banner_image' 		=> 'image|max:10240',

			'restaurant_logo' 	=> 'image|max:10240',

			'is_free' 			=> 'required',
			'delivery_mode' 	=> 'required',
		);

		if(!$data['restaurant_logo']) {
			$rules['restaurant_logo'] = 'required|image|max:10240';
		}

		if($request->is_free=='0') {
			$rules['delivery_fee'] = 'required';
		}

		$attributes = array(
			'name' => 'Restaurant name',
			'dob' => 'Date of birth',
			'email' => 'Email',
			'is_free' 		=> 'Delivery Fee',
			'delivery_fee' 	=> 'Delivery Fee',
			'restaurant_logo' 	=> trans('messages.restaurant.restaurant_logo'),
			'delivery_mode' => trans('admin_messages.delivery_mode'),
		);

		$messages = array(
			'banner_image.max' => trans('messages.restaurant.the_banner_image_may_not_greater_than'),
			'restaurant_logo.max' => trans('messages.restaurant.the_restaurant_logo_may_not_greater_than'),
			'dob.before' => trans('messages.restaurant.age_must_be_or_older'),
		);

		$validator = Validator::make($request->all(), $rules, $messages, $attributes);

		if ($validator->fails()) {
			return back()->withErrors($validator)->withInput();
		}

		$restaurant->name = $request->restaurant_name;
		$restaurant->description = $request->description;
		$restaurant->price_rating = $request->price_rating;

		$restaurant->is_free 		= $request->is_free;
		$restaurant->delivery_fee 	= 0;
		if($request->is_free=='0') {
			$restaurant->delivery_fee = $request->delivery_fee;
		}
		
		$restaurant->delivery_mode = implode(',',$request->delivery_mode);

		$restaurant->save();

		$user_address = UserAddress::where('user_id', $restaurant->user_id)->where('default', 1)->first();
		if ($user_address == '') {
			$user_address = new UserAddress;
		}
		$country = Country::where('code', $request->country_code)->first();
		$user_address->address = $request->address;
		$user_address->country = $country->name;
		$user_address->country_code = $country->code;
		$user_address->postal_code = $request->postal_code;
		$user_address->city = $request->city;
		$user_address->state = $request->state;
		$user_address->street = $request->street;
		$user_address->latitude = $request->latitude;
		$user_address->longitude = $request->longitude;
		$user_address->user_id = $restaurant->user_id;
		$user_address->default = 1;
		$user_address->save();

		//Restaurant Cuisine
		foreach ($request->cuisine as $value) {
			if ($value) {

				$cousine = RestaurantCuisine::where('restaurant_id', $restaurant_id)->where('cuisine_id', $value)->first();
				if ($cousine == '') {
					$cousine = new RestaurantCuisine;
				}

				$cousine->restaurant_id = $restaurant_id;
				$cousine->cuisine_id = $value;
				$cousine->status = 1;
				$cousine->save();
			}
		}
		//delete cousine
		RestaurantCuisine::where('restaurant_id', $restaurant_id)->whereNotIn('cuisine_id', $request->cuisine)->delete();

		$user = User::find($restaurant->user_id);
		$user->name = $request->first_name . '~' . $request->last_name;
		$user->email = $request->email;
		$user->date_of_birth = date('Y-m-d', strtotime($request->dob));
		if ($user->mobile_number != $request->mobile_number || $user->country_code != $request->phone_code) {
			$user->mobile_no_verify = 0;
			$user->status = 4;
		}
		$user->mobile_number = $request->mobile_number;
		$user->country_code = $request->phone_code;

		$user->save();

		if ($request->file('banner_image')) {

			$file = $request->file('banner_image');

			$file_path = $this->fileUpload($file, 'public/images/restaurant/' . $restaurant_id);

			$this->fileSave('restaurant_banner', $restaurant_id, $file_path['file_name'], '1');
			$orginal_path = Storage::url($file_path['path']);
			$size = get_image_size('restaurant_image_sizes');
			foreach ($size as $value) {
				$this->fileCrop($orginal_path, $value['width'], $value['height']);
			}

		}

		// restaurant_logo
		if ($request->file('restaurant_logo')) {
			$file = $request->file('restaurant_logo');

			$file_path = $this->fileUpload($file, 'public/images/restaurant/' . $restaurant_id);

			$this->fileSave('restaurant_logo', $restaurant_id, $file_path['file_name'], '1');
			$orginal_path = Storage::url($file_path['path']);
			$size = get_image_size('restaurant_logo');
			$this->fileCrop($orginal_path, $size['width'], $size['height']);
		}

		flash_message('success', trans('admin_messages.updated_successfully'));
		return redirect()->route('restaurant.profile');

	}

	public function update_open_time(Request $request) {

		$id = get_current_restaurant_id();
		$req_time_id = array_filter($request->time_id);
		if (count($req_time_id)) {
			RestaurantTime::whereNotIn('id', $req_time_id)->where('restaurant_id', $id)->delete();
		}

		foreach ($request->day as $key => $time) {
				if (isset($req_time_id[$key])) {
					$restaurant_insert = RestaurantTime::find($req_time_id[$key]);
				} else {
					$restaurant_insert = new RestaurantTime;
				}
				$restaurant_insert->start_time = ($request->start_time[$key]);
				$restaurant_insert->end_time = ($request->end_time[$key]);
				$restaurant_insert->day = $request->day[$key];
				$restaurant_insert->status = $request->status[$key];
				$restaurant_insert->restaurant_id = $id;
				$restaurant_insert->save();
		}

		flash_message('success', trans('admin_messages.updated_successfully'));
		return redirect()->route('restaurant.profile', '#open_time');
	}

	public function update_documents(Request $request) {
		$restaurant_id = get_current_restaurant_id();
		RestaurantDocument::whereNotIn('document_id', $request->document_id)->delete();

		foreach ($request->document_name as $key => $value) {

			if ($request->document_id[$key] == '') {

				$multiple = 'multiple';
				$restaurant_document = new RestaurantDocument;

			} else {

				$multiple = '';
				$restaurant_document = RestaurantDocument::where('document_id', $request->document_id[$key])->first();

			}
			if (isset($request->document_file[$key])) {
				$file = $request->document_file[$key];
				$file_path = $this->fileUpload($file, 'public/images/restaurant/' . $restaurant_id . '/documents');
				$file_id = $this->fileSave('restaurant_document', $restaurant_id, $file_path['file_name'], '1', $multiple, $request->document_id[$key]);
				$restaurant_document->document_id = $file_id;

			}
			$restaurant_document->name = $request->document_name[$key];
			$restaurant_document->restaurant_id = $restaurant_id;
			$restaurant_document->save();

		}
		flash_message('success', trans('admin_messages.updated_successfully'));
		return redirect()->route('restaurant.profile', '#document');
	}

	public function offers(Request $request) {

		$restaurant_id = get_current_restaurant_id();

		if ($request->getMethod() == 'GET') {

			$data['offer'] = RestaurantOffer::where('restaurant_id', $restaurant_id)->orderBy('id', 'desc')->get();

			return view('restaurant.offers', $data);

		} else {

			if (isset($request->new_offers['id'])) {

				$offer = RestaurantOffer::find($request->new_offers['id']);
				$offer->offer_title = $request->new_offers['offer_title'];
				$offer->offer_description = $request->new_offers['offer_description'];
				$offer->start_date = $request->start_date;
				$offer->end_date = $request->end_date;
				$offer->percentage = $request->new_offers['percentage'];
				$offer->save();

			} else {

				$offer = new RestaurantOffer;
				$offer->offer_title = $request->new_offers['offer_title'];
				$offer->offer_description = $request->new_offers['offer_description'];
				$offer->start_date = $request->start_date;
				$offer->end_date = $request->end_date;
				$offer->percentage = $request->new_offers['percentage'];
				$offer->restaurant_id = $restaurant_id;
				$offer->status = 1;
				$offer->save();

			}

			$data['offer'] = RestaurantOffer::where('restaurant_id', $restaurant_id)->get();

			return $data;

		}

	}

	public function remove_offer(Request $request) {

		$offer = RestaurantOffer::find($request->id);

		if ($offer) {
			$offer->delete();

		}

		return "success";

	}

	public function status_update() {

		$restaurant = Restaurant::find(get_current_restaurant_id());
		$restaurant->status = request()->status;
		$restaurant->save();
		return $restaurant->status;
	}
	public function show_comments() {

		$values = explode(',', request()->comments);

		$comment_array = array_filter($values);
		$comments = '';

		foreach ($comment_array as $value) {
			$comments .= "<li>" .$value. "</li>";
		}
		return $comments != '' ? $comments : '<li>'. trans('messages.restaurant_dashboard.no_comments').'</li>';
	}

	/*
		* status update for offer
		*
	*/

	public function offers_status() {

		$id = request()->id;
		$status = request()->status;

		$offer = RestaurantOffer::find($id);

		$offer->status = $status;

		$offer->save();

		return json_encode(['success' => true, 'offer' => $offer]);

	}

	public function send_message() {
		$code = rand(1000, 9999);
		$rules = [

			'mobile_no' => 'required|regex:/^[0-9]+$/|min:6|unique:user,mobile_number,' . get_current_login_user_id() . ',id,type,1',
		];

		$validator = Validator::make(request()->all(), $rules);

		if ($validator->fails()) {
			return ['status' => 'Failed', 'message' => trans('messages.driver.this_number_already_exists')];
		}

		$to = request()->code . request()->mobile_no;
		$message = trans('api_messages.register.verification_code') . $code;
		$status['status'] = 'Success';
		if(!canDisplayCredentials()){
			$status = send_text_message($to, $message);
		}
		$status['code'] = $code;
		if ($status['status'] != 'Failed') {
			$user = User::find(auth()->guard('restaurant')->user()->id);
			$user->country_code = request()->code;
			$user->mobile_number = request()->mobile_no;
			$user->save();
		}
		return $status;
	}

	public function confirm_phone_no() {
		$user = User::find(auth()->guard('restaurant')->user()->id);
		$user->mobile_no_verify = 1;
		$user->save();

		return '';
	}

}
