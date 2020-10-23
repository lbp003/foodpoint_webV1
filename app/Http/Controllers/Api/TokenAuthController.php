<?php

/**
 * TokenAuth Controller
 *
 * @package    GoferEats
 * @subpackage Controller
 * @category   TokenAuth
 * @author     Trioangle Product Team
 * @version    1.2
 * @link       http://trioangle.com
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Models\Driver;
use App\Models\Restaurant;
use App\Models\Currency;
use App\Models\User;
use App\Models\VehicleType;
use App\Traits\AddOrder;
use App;
use JWTAuth;
use Session;
use Validator;
use App\Models\Country;

class TokenAuthController extends Controller
{
	use AddOrder;

	/**
	 * User or restaurant or driver Resister
	 *
	 * @param Get method request inputs
	 *
	 * @return Response Json
	 */
	public function register(Request $request)
	{
		$language = isset($request->language) ? $request->language : "en";
        App::setLocale($language);
		if(isset($request->language)) {
            Session::put('language', $language);
        }

		$rules = array(
			'mobile_number' => 'required|regex:/^[0-9]+$/|min:6',
			'type' => 'required|in:0,1,2',
			'password' => 'required|min:6',
			'first_name' => 'required',
			'last_name' => 'required',
			'country_code' => 'required',
			//'dob' => 'required',
		);
		$messages = array(
            'required' => ':attribute '.trans('api_messages.register.field_is_required').'', 
        );

		$attributes = array(
			'mobile_number' => trans('api_messages.register.mobile_number'),
			'type'=>trans('api_messages.register.type'),
			'password'=>trans('api_messages.register.password'),
			'first_name' => trans('api_messages.register.first_name'),
			'last_name' => trans('api_messages.register.last_name'),
			'country_code' => trans('api_messages.register.country_code'),
			//'dob' => trans('api_messages.register.dob'),
		);

		$validator = Validator::make($request->all(), $rules, $messages, $attributes);
		
		if ($validator->fails()) {
			return response()->json([
			    'status_code' => '0',
			    'status_message' => $validator->messages()->first(),
			]);
		}
		$mobile_number = $request->mobile_number;

		$check_user = User::validateUser($request->type,$request->country_code,$request->mobile_number,$request->email)->count();

		if ($check_user > 0) {
			return response()->json([
				'status_code' => '0',
				'status_message' => trans('api_messages.register.already_account'),
			]);
		}

		$name = html_entity_decode($request->first_name) .'~'. html_entity_decode($request->last_name);
		$user = new User;
		$user->mobile_number = $request->mobile_number;
		$user->name = $name;
		$user->user_first_name = html_entity_decode($request->first_name);
		$user->user_last_name = html_entity_decode($request->last_name);
		$user->type = $request->type;
		$user->password = bcrypt($request->password);
		$user->country_code = $request->country_code;
		$user->email = $request->email;
		$user->status = $request->type == 2 ? $user->statusArray['vehicle_details'] : $user->statusArray['active'];
		$user->language = $language;
		if(isset($request->dob)){
			if($request->dob !=''){
				$user->date_of_birth =date('Y-m-d',strtotime($request->dob));
			}
		}
		$user->save();
		if($request->type == 2) {
			$driver = new Driver;
			$driver->user_id = $user->id;
			$driver->save();
		}

		$credentials = $request->only('mobile_number', 'password', 'type');

		try {
			if (!$token = JWTAuth::attempt($credentials)) {
				return response()->json(['error' => 'invalid_credentials']);
			}
		}
		catch (JWTException $e) {
			return response()->json(['error' => 'could_not_create_token']);
		}

		// if no errors are encountered we can return a JWT
		$vehicle_type = VehicleType::status()->get()->map(function ($type) {
			return [
				'id' => $type->id,
				'name' => $type->name,
			];
		});

		if($request->order) {
			$request['token'] = $token;
			$data =  $this->add_cart_item($request,0);

			if($data['status_code']!=1) {
					return response()->json([
						'status_message' => $data['status_message'],
						'status_code' => $data['status_code'],
					]);
	       	}
		}

		$register = array(
			'status_code' 	=> '1',
			'status_message'=>  trans('api_messages.register.register_success'),
			'access_token' 	=> $token,
			'user_details' 	=> $user,
			'user_data' 	=> $user,
			'vehicle_type' 	=> $vehicle_type,
		);

		return response()->json($register);
	}

	public function number_validation(Request $request)
	{
        $language = $request->language ?? "en";
        App::setLocale($language);

		$rules = array(
			'mobile_number' => 'required|regex:/^[0-9]+$/|min:6',
			'type' => 'required|in:0,2',
			'country_code' => 'required',
		);

		$messages = array(
            'required'                => ':attribute '.trans('api_messages.register.field_is_required').'', 
        );

		$attributes = array(
			'mobile_number' => trans('api_messages.register.mobile_number'),
			'type'=>trans('api_messages.register.type'),			
			'country_code' => trans('api_messages.register.country_code'),
		);

		$validator = Validator::make($request->all(), $rules, $messages,$attributes);

		if ($validator->fails()) {
			return response()->json([
                'status_code' => '0',
                'status_message' => $validator->messages()->first(),
            ]);
		}

		$mobile_number = $request->mobile_number;
		$user = User::validateUser($request->type,$request->country_code,$request->mobile_number,$request->email)->count();

		if ($user > 0) {
			return response()->json([
				'status_code' => '0',
				'status_message' => trans('api_messages.register.already_account'),
			]);
		}

		$otp = rand(1000, 9999);

		$message = trans('api_messages.register.verification_code') . $otp;

		$phone_number = $request->country_code . $request->mobile_number;
		$message_send = send_text_message($phone_number, $message);
		
		return response()->json([
			'status_code' => '1',
			'status'=>true,
			'status_message'=>trans('api_messages.message_send'),
			'otp' => $otp,
		]);
		/*if ($message_send['status'] == 'Success') {
			return json_encode(['status'=>$message_send['status'],'status_message'=>$message_send['message'],'otp' => $otp, 'status_code' => '1']);
		} else {
			return json_encode(['status'=>$message_send['status'],'status_message'=>$message_send['message'], 'otp' => $otp,'status_code' => '0']);
		}*/
	}

	public function forgot_password(Request $request)
	{
		$language = (isset($request->language)) ? $request->language : "en";
        App::setLocale($language);

		$rules = array(
			'mobile_number' => 'required|regex:/^[0-9]+$/|min:6',
			'type' => 'required|in:0,2',
			'country_code' => 'required',
		);

		$messages = array(
            'required' => ':attribute '.trans('api_messages.register.field_is_required').'', 
        );		

		$attributes = array(
			'mobile_number' => trans('api_messages.register.mobile_number'),
			'type'=>trans('api_messages.register.type'),			
			'country_code' => trans('api_messages.register.country_code'),
		);

		$validator = Validator::make($request->all(), $rules, $messages, $attributes);

		if ($validator->fails()) {
			return response()->json([
			    'status_code' => '0',
			    'status_message' => $validator->messages()->first(),
			]);
		}
		$mobile_number = $request->mobile_number;

		$user = User::where('mobile_number', $mobile_number)->where('type', $request->type)->count();

		if ($user == 0) {
			return response()->json([
				'status_code' => '0',
				'status_message' => trans('api_messages.register.number_not_found'),
			]);
		}

		$otp = rand(1000, 9999);

		$message = trans('api_messages.register.verification_code') . $otp;

		$phone_number = $request->country_code . $request->mobile_number;
		$message_send = send_text_message($phone_number, $message);
		return json_encode(['status'=>true,'status_message'=>trans('api_messages.message_send'),'otp' => $otp, 'status_code' => '1']);
		/*if ($message_send['status'] == 'Success') {
			return json_encode(['status' => $message_send['status'], 'status_message' => $message_send['message'], 'otp' => $otp, 'status_code' => '1']);
		} else {
			return json_encode(['status' => $message_send['status'], 'status_message' => $message_send['message'], 'status_code' => '0']);
		}*/
	}

	public function reset_password(Request $request)
	{
		$language = (isset($request->language)) ? $request->language : "en";
        App::setLocale($language);

		$rules = array(
			'mobile_number' => 'required|regex:/^[0-9]+$/|min:6',
			'type' => 'required|in:0,2',
			'country_code' => 'required',
			'password' => 'required',
		);

		$messages = array(
            'required'                => ':attribute '.trans('api_messages.register.field_is_required').'', 
        );
		$attributes = array(
			'mobile_number' => trans('api_messages.register.mobile_number'),
			'type'=>trans('api_messages.register.type'),
			'password'=>trans('api_messages.register.password'),			
			'country_code' => trans('api_messages.register.country_code'),
		);

		$validator = Validator::make($request->all(), $rules,$messages, $attributes);

		if ($validator->fails()) {
			return response()->json([
			    'status_code' => '0',
			    'status_message' => $validator->messages()->first(),
			]);
		}
		$mobile_number = $request->mobile_number;

		$user = User::where('mobile_number', $mobile_number)->where('country_code', $request->country_code)->where('type', $request->type)->first();

		if (!isset($user)) {
			return response()->json([
				'status_code' => '0',
				'status_message' => trans('api_messages.register.reset_password'),
			]);
		}

		$user->password = bcrypt($request->password);
		$user->save();

		return response()->json([
			'status_code' => '1',
			'status_message' => trans('api_messages.register.success'),
		]);
	}

	/**
	 * User Login
	 *
	 * @param Get method request inputs
	 *
	 * @return Response Json
	 */
	public function login(Request $request)
	{
		if(isset($request->language)) {
			$getLanguage = $request->language;
			App::setLocale($request->language);
		}
        else {
        	$getLanguage = 'en';
            App::setLocale('en');
        }

		$user_id = $request->mobile_number;

		$rules = array(
			'type' => 'required|in:0,1,2',
			'password' => 'required|min:6',
		);

		if ($request->type == '1') {
			$rules['email'] = 'required';
			$db_id = 'email';
			$user_id = $request->email;
		}
		else {
			$rules['mobile_number'] = 'required|regex:/^[0-9]+$/|min:6';
			$rules['country_code'] = 'required';
			$db_id = 'mobile_number';
		}

		$messages = array(
            'required'                => ':attribute '.trans('api_messages.register.field_is_required').'', 
            'regex' => ':attribute '.trans('api_messages.register.format_field').''
            
        );
		$attributes = array(
			'mobile_number' => trans('api_messages.register.mobile_number'),
			'type'=>trans('api_messages.register.type'),
			'password'=>trans('api_messages.register.password'),			
			'email'=>trans('api_messages.register.email'),
			'country_code' => trans('api_messages.register.country_code'),
		);

		$validator = Validator::make($request->all(), $rules,$messages, $attributes);
		
		if ($validator->fails()) {
			return [
                'status_code' => '0',
                'status_message' => $validator->messages()->first(),
            ];
		}

		if ($request->type == '1') {
			$credentials = $request->only('email', 'password', 'type');
		}
		else {
			$credentials = $request->only('mobile_number', 'password', 'type', 'country_code');
		}

		try {
			if (!$token = JWTAuth::attempt($credentials)) {
				return response()->json([
					'status_code' => '0',
					'status_message' => trans('api_messages.register.credentials_not_right'),
				]);
			}
		}
		catch (JWTException $e) {
			return response()->json([
				'status_code' => '0',
				'status_message' => trans('api_messages.register.could_not_create_token'),
			]);
		}

		$user_check = User::where($db_id, $user_id)->where('type', $request->type)->first();

		$vehicle_type = VehicleType::status()->get()->map(function ($type) {
			return [
				'id' => $type->id,
				'name' => $type->name,
			];
		});

		if($user_check->status_text == 'inactive') {
			return response()->json([
				'status_code' => '0',
				'status_message' => trans('api_messages.register.account_deactivated'),
			]);
		}

		if($request->order) {
			$request['token'] = $token;
			$data =  $this->add_cart_item($request,0);
			if($data['status_code'] != 1) {
				return response()->json([
					'status_code' => $data['status_code'],
					'status_message' => $data['status_message'],
				]);
	       	}
		}

		$return_data = array(
			'status_code' 		=> '1',
			'status_message' 	=> trans('api_messages.register.login_success'),
			'access_token' 		=> $token,
			'user_data' 		=> $user_check,
			'vehicle_type'		=> $vehicle_type,
		);

		if ($request->type == 1) {
			$restaurant_name = Restaurant::where('user_id', $user_check->id)->first()->name;
			$return_data['restaurant_name'] = $restaurant_name;
		}

		//change Language
		User::where($db_id, $user_id)->update(['language' => $getLanguage]);
		return response()->json($return_data);
	}

	/**
	 * Update Device ID and Device Type
	 *
	 * @param Get method request inputs
	 *
	 * @return Response Json
	 */
	public function update_device_id(Request $request)
	{
		$default_currency_code=DEFAULT_CURRENCY;
		$default_currency_symbol=Currency::where('code',DEFAULT_CURRENCY)->first()->symbol;
		$default_currency_symbol=html_entity_decode($default_currency_symbol);
		
		$user_details = JWTAuth::parseToken()->authenticate();
		$rules = array(
			'type' => 'required|in:0,1,2',
			'device_type' => 'required',
			'device_id' => 'required',

		);

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails()) {
			return response()->json([
                'status_code' => '0',
                'status_message' => $validator->messages()->first(),
                'default_currency_code' => $default_currency_code,
                'default_currency_symbol'=>$default_currency_symbol,
            ]);
		}

		$user = User::where('id', $user_details->id)->first();

		if ($user == '') {
			return response()->json([
				'status_code' => '0',
				'status_message' => "Invalid credentials",
				'default_currency_code'=>$default_currency_code,
				'default_currency_symbol'=>$default_currency_symbol,
			]);
		}

		User::whereId($user_details->id)->update(['device_id' => $request->device_id, 'device_type' => $request->device_type]);

		return response()->json([
			'status_code' => '1',
			'status_message' => "Updated Successfully",
			'default_currency_code'=>$default_currency_code,
			'default_currency_symbol'=>$default_currency_symbol,
		]);
	}

	public function get_profile()
	{
		$user_details = JWTAuth::parseToken()->authenticate();
		$user = User::where('id', $user_details->id)->first();
		if ($user == '') {
			return response()->json([
				'status_code' => '0',
				'status_message' => trans('api_messages.register.invalid_credentials'),
			]);
		}

		$name = explode('~', $user->name);

		$user->name = str_replace('~', ' ', $user->name);

		$data = [array('key'=>trans('api_messages.register.first_name'),'value'=> $name[0]), array('key'=>trans('api_messages.register.sur_name'),'value' => @$name[1]), array('key'=> trans('api_messages.register.phone_number') ,'value' => $user->mobile_number), array('key' => trans('api_messages.register.email_address') ,'value' => $user->email)];

		return response()->json([
			'status_code' => '1',
			'status_message' => trans('api_messages.register.profile_success'),
			'user_details' => $user,
			'user_array' => $data,
		]);
	}

	public function change_mobile(Request $request)
	{
		$user_details = JWTAuth::parseToken()->authenticate();
		$user = User::where('id', $user_details->id)->first();
		
		$rules = array(
			'mobile_number' => 'required|regex:/^[0-9]+$/|min:6',
			'type' => 'required|in:0,2',
			'country_code' => 'required',
		);

		$attributes = array(
			'mobile_number' => 'Mobile Number',
		);

		$validator = Validator::make($request->all(), $rules, [], $attributes);

		if ($validator->fails()) {
			return response()->json([
                'status_code' => '0',
                'status_message' => $validator->messages()->first(),
            ]);
		}

		$mobile_number = $request->mobile_number;
		$country_code = $request->country_code;

		if (!empty($user)) {

			$user->mobile_number = $mobile_number;
			$user->country_code = $country_code;
			$user->save();

			if ($request->type == 2) {

				$driver = Driver::authUser()->first();
				$user = $driver->user;

				$user = collect($user)->except(['user_image', 'eater_image']);

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

				$driver_details = collect($driver)->only(['vehicle_name', 'vehicle_number', 'vehicle_type_name']);

				$driver_profile = $user->merge($user_address)->merge($driver_details)->merge($driver_documents);

				return response()->json(
					[
						'status_message' => 'Driver profile details updated successfully',
						'status_code' => '1',
						'driver_profile' => $driver_profile,
					]
				);
			} else {

				return response()->json(
					[

						'status_message' => "Updated Successfully",

						'status_code' => '1',

						'user_details' => $user,

					]
				);

			}

		} else {

			return response()->json(
				[

					'status_message' => "failed",

					'status_code' => '0',

				]
			);

		}
	}

	public function logout(Request $request)
	{
		$user_details = JWTAuth::parseToken()->authenticate();
		$user = User::where('id', $user_details->id)->first();

		if ($user->type == '2') {
			$driver = Driver::authUser()->first();
			if ($driver->status == 2) {
				return response()->json([
					'status_code' => '2',
					'status_message' => trans('api_messages.register.complete_your_trip'),
				]);
			}
		}

		$user->device_type = '';
		$user->device_id = '';
		$user->save();

		JWTAuth::invalidate($request->token);

		return response()->json([
			'status_code' => '1',
			'status_message' => "Logout Successfully",
		]);
	}

	public function language(Request $request)
    {
    	try {
        	$user_details = JWTAuth::parseToken()->authenticate();
    	}
    	catch(\Exception $e) {
    		return response()->json([
                'status_code' => '0',
                'status_message' => $e->getMessage(),
            ]);
    	}

    	$language = (isset($request->language)) ? $request->language : "en";
        App::setLocale($language);

		$rules = array(			
			'type' => 'required|in:0,1,2',			
		);
		$messages = array(
            'required' => ':attribute '.trans('api_messages.register.field_is_required').'', 
            
        );
		$attributes = array(			
			'type'=>trans('api_messages.register.type'),			
		);

		$validator = Validator::make($request->all(), $rules, $messages, $attributes);

		if ($validator->fails()) {
			return response()->json([
				'status_code' => '0',
				'status_message' => $validator->messages()->first(),
			]);
		}

		$user= User::find($user_details->id);
		$user->language =$request->language;
		$user->type = $request->type;
		$user->save();
            
        return response()->json([
	        'status_code'       =>  '1',
	        'status_message'    => trans('api_messages.register.update_success'),
        ]);
	}

	public function common_data(Request $request)
    {
        $status_code = '1';
        $status_message = 'Listed Successfully';

        $stripe_publish_key = site_setting('stripe_publish_key');

        return response()->json(compact(
    		'status_code',
    		'status_message',
    		'stripe_publish_key'
    	));
    }

    /**
	* Display Country List
	*
	* @param Get method request inputs
	* @return @return Response in Json
	*/
	public function country_list(Request $request)
	{

		$country_data = Country::select(
		'id as country_id',
		'name as country_name',
		'code as country_code'	
		)->where('stripe_country','1')->get();

		$country_list = $country_data->map(function($data){
            return [
                'country_id'    => $data->country_id,
                'country_name'  => $data->country_name,
                'country_code'  => $data->country_code,
                'currency_code' => $this->countryCurrency($data->country_code),
           		 ];
	        });

		return response()->json([
			'status_code' => '1',
			'status_message' => 'Country Listed Successfully',
			'country_list' => $country_list,
			]);
	}


	public  function countryCurrency($data)
	{
		$currency_code=[];
		$data1 = Country::getCurrency();
		foreach ($data1 as $key => $value) {
			if($key == $data)
			{
				$currency_code = $value  ;
			}
		}
		return $currency_code;
	}
	

}