<?php
/**
 * RestaurantController
 *
 * @package    GoferEats
 * @subpackage  Controller
 * @category    RestaurantController
 * @author      Trioangle Product Team
 * @version     1.3
 * @link        http://trioangle.com
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\DataTableBase;
use App\Models\Cuisine;
use App\Models\File;
use App\Models\Menu;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\MenuItemModifier;
use App\Models\MenuItemModifierItem;
use App\Models\MenuTime;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PayoutPreference;
use App\Models\Restaurant;
use App\Models\RestaurantCuisine;
use App\Models\RestaurantDocument;
use App\Models\RestaurantOffer;
use App\Models\RestaurantPreparationTime;
use App\Models\RestaurantTime;
use App\Models\User;
use App\Models\SiteSettings;
use App\Models\UserAddress;
use App\Models\UserPaymentMethod;
use App\Models\UsersPromoCode;
use App\Models\Wishlist;
use App\Traits\FileProcessing;
use DataTables;
use Hash;
use Illuminate\Http\Request;
use Storage;
use Validator;
use App\Models\MenuTranslations;
use App\Models\MenuCategoryTranslations;
use App\Models\MenuItemTranslations;

class RestaurantController extends Controller
{

	use FileProcessing;

	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Manage site setting
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function add_restaurant(Request $request) {
		if ($request->getMethod() == 'GET') {
			
			
			$this->view_data['form_action'] = route('admin.add_restaurant');
			$this->view_data['form_name'] = trans('admin_messages.add_restaurant');
			$this->view_data['cuisine'] = Cuisine::where('status', 1)->pluck('name', 'id');
			$this->view_data['restaurant_cuisine'] = array();
			$this->view_data['delivery_mode'] = array('1'=>trans('admin_messages.pickup_rest'),'2'=>trans('admin_messages.delievery_door'));
			return view('admin/restaurant/add_restaurant', $this->view_data);
		} else {
			
			$all_variables = request()->all();
			if ($all_variables['date_of_birth']) {
				$all_variables['convert_dob'] = date('Y-m-d', strtotime($all_variables['date_of_birth']));
			}

			$rules = array(
				'first_name' => 'required',
				'last_name' => 'required',
				'restaurant_name' => 'required',
				'restaurant_description' => 'required',
				// 'min_time' => 'required',
				// 'max_time' => 'required|after:min_time',
				'cuisine' => 'required',
				'password' => 'required|min:6',
				'convert_dob' => 'required|before:18 years ago',
				'phone_country_code' => 'required',
				'restaurant_status' => 'required',
				'user_status' => 'required',
				'price_rating' => 'required',
				'address' => 'required',
				//'banner_image' => 'required|image|mimes:jpg,png,jpeg,gif',

				'restaurant_logo' => 'required|image|mimes:jpg,png,jpeg,gif',

				'email' => 'required|email|unique:user,email,NULL,id,type,1',
				'mobile_number' => 'required|regex:/^[0-9]+$/|min:6|unique:user,mobile_number,NULL,id,type,1',
				'delivery_mode' => 'required',
				'is_free' => 'required',
			);

			if(
				$request->is_free=='0' &&
				@$request->delivery_mode &&
				in_array('2' , @$request->delivery_mode)
			) {
				$rules['delivery_fee'] = 'required|min:1|numeric';
			}

			// Add Admin User Validation Custom Names
			$niceNames = array(
				'first_name' => trans('admin_messages.first_name'),
				'last_name' => trans('admin_messages.last_name'),
				'restaurant_name' => trans('admin_messages.restaurant_name'),
				'restaurant_description' => trans('admin_messages.restaurant_description'),
				'delivery_mode' => trans('admin_messages.delivery_mode'),
				// 'min_time' => trans('admin_messages.min_time'),
				// 'max_time' => trans('admin_messages.max_time'),
				'email' => trans('admin_messages.email'),
				'password' => trans('admin_messages.password'),
				'convert_dob' => trans('admin_messages.date_of_birth'),
				'phone_country_code' => trans('admin_messages.country_code'),
				'mobile_number' => trans('admin_messages.mobile_number'),
				'restaurant_status' => trans('admin_messages.restaurant_status'),
				'price_rating' => trans('admin_messages.price_rating'),
				'user_status' => trans('admin_messages.user_status'),
				//'banner_image' => trans('admin_messages.banner_image'),

				'restaurant_logo' => trans('admin_messages.restaurant_logo'),

				'address' => trans('admin_messages.address'),

				'delivery_fee' => trans('admin_messages.delivery_fee'),
				'is_free' => trans('admin_messages.delivery_fee'),
			);

			if ($request->document) {
				foreach ($request->document as $key => $value) {
					$rules['document.' . $key . '.name'] = 'required';
					$rules['document.' . $key . '.document_file'] = 'required|mimes:jpg,png,jpeg,pdf';

					$niceNames['document.' . $key . '.name'] = trans('admin_messages.name');
					$niceNames['document.' . $key . '.document_file'] = 'Please upload the file like jpg,png,jpeg,pdf format';
				}
			}
			$messages = array(
				'convert_dob.before' => 'Age must be 18 or older',
			);
			
			$validator = Validator::make($all_variables, $rules,$messages);
			$validator->setAttributeNames($niceNames);

			$validator->after(function ($validator) use ($request) {
				if ($request->latitude == '' || $request->longitude == '') {
		            $validator->errors()->add('address', 'Invalid address');
		        }
		    });

			if ($validator->fails()) {
				return back()->withErrors($validator)->withInput(); // Form calling with Errors and Input values
			} else {
				$restaurant = new User;
				$restaurant->user_first_name = $request->first_name;
				$restaurant->user_last_name = $request->last_name;
				$restaurant->name = $request->first_name."~".$request->last_name;
				$restaurant->email = $request->email;
				$restaurant->password = Hash::make($request->password);
				$restaurant->date_of_birth = date('Y-m-d', strtotime($request->date_of_birth));
				$restaurant->country_code = $request->phone_country_code;
				$restaurant->mobile_number = $request->mobile_number;
				$restaurant->type = 1;
				$restaurant->status = $request->user_status;
				$restaurant->save();

				$new_restaurant = new Restaurant;
				$new_restaurant->user_id = $restaurant->id;
				$new_restaurant->name = $request->restaurant_name;
				$new_restaurant->description = $request->restaurant_description;
				// $new_restaurant->min_time = $request->min_time;
				$new_restaurant->max_time = '00:50:00';
				$new_restaurant->delivery_mode = implode(',',$request->delivery_mode); 
				$new_restaurant->currency_code = DEFAULT_CURRENCY;
				$new_restaurant->price_rating = $request->price_rating;
				$new_restaurant->status = $request->restaurant_status;

				$new_restaurant->is_free 		= $request->is_free;
				$new_restaurant->delivery_fee 	= 0;
				if(
					$request->is_free=='0' &&
					@$request->delivery_mode &&
					in_array('2' , @$request->delivery_mode)
				) {
					$new_restaurant->delivery_fee = $request->delivery_fee;
				}

				$new_restaurant->save();

				foreach ($request->cuisine as $value) {
					if ($value) {

						$cousine = RestaurantCuisine::where('restaurant_id', $new_restaurant->id)->where('cuisine_id', $value)->first();
						if ($cousine == '') {
							$cousine = new RestaurantCuisine;
						}

						$cousine->restaurant_id = $new_restaurant->id;
						$cousine->cuisine_id = $value;
						$cousine->status = 1;
						$cousine->save();
					}
				}

				$address = new UserAddress;
				$address->user_id = $restaurant->id;
				$address->address = $request->address;
				$address->country_code = $request->country_code;
				$address->postal_code = $request->postal_code;
				$address->city = $request->city;
				$address->state = $request->state;
				$address->street = $request->street;
				$address->latitude = $request->latitude;
				$address->longitude = $request->longitude;
				$address->default = 1;
				$address->save();

				//file
				if ($request->file('banner_image')) {
					$file = $request->file('banner_image');

					$file_path = $this->fileUpload($file, 'public/images/restaurant/' . $new_restaurant->id);

					$this->fileSave('restaurant_banner', $new_restaurant->id, $file_path['file_name'], '1');
					$orginal_path = Storage::url($file_path['path']);
					$size = get_image_size('restaurant_image_sizes');
					foreach ($size as $value) {
						$this->fileCrop($orginal_path, $value['width'], $value['height']);
					}
				}

				// restaurant_logo
				if ($request->file('restaurant_logo')) {
					$file = $request->file('restaurant_logo');

					$file_path = $this->fileUpload($file, 'public/images/restaurant/' . $new_restaurant->id);

					$this->fileSave('restaurant_logo', $new_restaurant->id, $file_path['file_name'], '1');
					$orginal_path = Storage::url($file_path['path']);
					$size = get_image_size('restaurant_logo');					
					$this->fileCrop($orginal_path, $size['width'], $size['height']);
				}

				//documents
				if ($request->document) {
					foreach ($request->document as $key => $value) {

						$file = $value['document_file'];
						$file_path = $this->fileUpload($file, 'public/images/restaurant/' . $new_restaurant->id . '/documents');
						$file_id = $this->fileSave('restaurant_document', $new_restaurant->id, $file_path['file_name'], '1', 'multiple');
						$restaurant_document = new RestaurantDocument;
						$restaurant_document->name = $value['name'];
						$restaurant_document->document_id = $file_id;
						$restaurant_document->restaurant_id = $new_restaurant->id;
						$restaurant_document->save();
					}
				}

				flash_message('success', trans('admin_messages.added_successfully'));
				return redirect()->route('admin.view_restaurant');
			}

		}
	}

	/**
	 * Manage site setting
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function view() {
		$this->view_data['form_name'] = trans('admin_messages.restaurant_management');
		return view('admin.restaurant.view', $this->view_data);
	}

	/**
	 * Manage
	 *
	 * @return \Illuminate\Http\Response
	 */

	public function all_restaurants() {

		$restaurants = User::where('type', 1);
		$filter_type = request()->filter_type;

		$from = date('Y-m-d' . ' 00:00:00', strtotime(change_date_format(request()->from_dates)));
		if (request()->to_dates != '') {
			$to = date('Y-m-d' . ' 23:59:59', strtotime(change_date_format(request()->to_dates)));

			// $restaurants = $restaurants->whereBetween('created_at', array($from, $to));
			$restaurants = $restaurants->where('created_at', '>=', $from)->where('created_at', '<=', $to);
		}
		$restaurants = $restaurants->get();
		// dd($restaurants);
		$datatable = DataTables::of($restaurants)
			->addColumn('id', function ($restaurants) {
				return @$restaurants->restaurant->id;
			})
			->addColumn('name', function ($restaurants) {
				return @$restaurants->name;
			})
			->addColumn('restaurant_name', function ($restaurants) {
				return @$restaurants->restaurant->name;
			})
			->addColumn('email', function ($restaurants) {
				return @$restaurants->email;
			})
			->addColumn('user_status', function ($restaurants) {
				return @$restaurants->status_text;
			})
			->addColumn('restaurant_status', function ($restaurants) {
				return @$restaurants->restaurant->status_text;
			})
			->addColumn('app_delivery_mode_text', function ($restaurants) {
				return @$restaurants->restaurant->app_delivery_mode_text;
			})
			->addColumn('created_at', function ($restaurants) {
				return @$restaurants->created_at;
			})
			->addColumn('recommend', function ($restaurants) {
				if ($restaurants->status && $restaurants->status!=4&& $restaurants->status!=5 ) {
					$class = @$restaurants->restaurant->recommend == 1 ? "success" : "danger";
					return '<a class="' . $class . '"  href="' . route('admin.recommend', ['id' => @$restaurants->restaurant->id]) . '" ><span>' . @$restaurants->restaurant->recommend_status . '</span></a>';
				}
				return @$restaurants->restaurant->recommend_status;
			})
			->addColumn('action', function ($restaurants) {
				return '<a title="' . trans('admin_messages.edit_preparation_time') . '" href="' . route('admin.edit_preparation_time', $restaurants->id) . '" ><i class="material-icons">alarm_add</i></a>&nbsp;<a title="' . trans('admin_messages.menu_category') . '" href="' . route('admin.menu_category', $restaurants->id) . '" ><i class="material-icons">category</i></a>&nbsp;<a title="' . trans('admin_messages.edit_open_time') . '" href="' . route('admin.edit_open_time', $restaurants->id) . '" ><i class="material-icons">alarm_on</i></a>&nbsp;<a title="' . trans('admin_messages.edit') . '" href="' . route('admin.edit_restaurant', $restaurants->id) . '" ><i class="material-icons">edit</i></a>&nbsp;<a title="' . trans('admin_messages.delete') . '" href="javascript:void(0)" class="confirm-delete" data-href="' . route('admin.delete_restaurant', $restaurants->id) . '"><i class="material-icons">close</i></a>';
			})
			->escapeColumns('recommend');
		$columns = ['id', 'name', 'restaurant_name', 'email', 'user_status', 'restaurant_status', 'recommend', 'created_at'];
		$base = new DataTableBase($restaurants, $datatable, $columns, 'Restaurants');
		return $base->render(null);

	}

	public function recommend() {
		$restaurant = Restaurant::find(request()->id);
		if ($restaurant->recommend == 1) {
			$restaurant->recommend = 0;
		} else {
			$restaurant->recommend = 1;
		}

		$restaurant->save();
		flash_message('success', trans('admin_messages.updated_successfully'));
		return redirect()->route('admin.view_restaurant');
	}

	/**
	 * Manage site setting
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function delete(Request $request) {
	
 		$restaurant_id = Restaurant::where('user_id', $request->id)->first();
		$order = Order::where('restaurant_id', $restaurant_id->id)->get();
		if ($order->count() > 0 ) {
			flash_message('danger', 'You can\'t delete this restaurant user. This restaurant has some orders');
			return redirect()->route('admin.view_restaurant');
		}

		if (!empty($restaurant_id)) {

			$menu_item_modifier_item = [];
			$menu_item_modifier = [];
			$menu_item = [];
			$menu_time = [];
			$menu_category = [];
			//details fetch

			//menu and menu category
			$menu = Menu::where('restaurant_id', $restaurant_id->id)->get();
			if ($menu->count() > 0) {
				foreach ($menu as $key => $value) {
					$menu_category[$key] = MenuCategory::where('menu_id', $value->id)->get();
				}
			}

			//menu time
			if (!empty($menu)) {
				foreach ($menu as $key => $value) {
					$menu_time[$key] = MenuTime::where('menu_id', $value->id)->get();
				}
			}
			// dd($menu_category);	
			//menu item
			if (!empty($menu_category)) {
				
				foreach ($menu_category as $key => $value) {

					foreach ($value as $key1 => $value1) {

						$menu_item[$key][$key1] = MenuItem::where('menu_category_id', $value1->id)->get();
					}
				}
			}
			//menu item modifier
			if (!empty($menu_item)) {
				foreach ($menu_item as $key => $value) {

					foreach ($value as $key1 => $value1) {

						foreach ($value1 as $key2 => $value2) {

							$menu_item_modifier[$key][$key1][$key2] = MenuItemModifier::where('menu_item_id', $value2->id)->get();
						}
					}
				}
			}
			//menu item modifier item
			if (!empty($menu_item_modifier) ) {
				foreach ($menu_item_modifier as $key => $value) {
					foreach ($value as $key1 => $value1) {
						foreach ($value1 as $key2 => $value2) {
							foreach ($value2 as $key3 => $value3) {
								$menu_item_modifier_item[$key][$key1][$key2][$key3] = MenuItemModifierItem::where('menu_item_modifier_id', $value3->id)->get();
							}
						}
					}
				}
			}

			$restaurant_cuisine = RestaurantCuisine::where('restaurant_id', $restaurant_id->id)->get();
			$restaurant_document = RestaurantDocument::where('restaurant_id', $restaurant_id->id)->get();
			$restaurant_offer = RestaurantOffer::where('restaurant_id', $restaurant_id->id)->get();
			$restaurant_preparation_time = RestaurantPreparationTime::where('restaurant_id', $restaurant_id->id)->get();
			$restaurant_time = RestaurantTime::where('restaurant_id', $restaurant_id->id)->get();
			$wishlist = Wishlist::where('restaurant_id', $restaurant_id->id)->get();

			//delete fetched details

			//menu item modifier item
			if (!empty($menu_item_modifier_item) ) {
				foreach ($menu_item_modifier_item as $key => $value) {

					foreach ($value as $key1 => $value1) {

						foreach ($value1 as $key2 => $value2) {

							foreach ($value2 as $key3 => $value3) {

								foreach ($value3 as $key4 => $value4) {

									if (isset($value4)) {
										$value4->delete($value4->id);
									}

								}
							}

						}
					}
				}
			}

			//menu item modifier
			if (!empty($menu_item_modifier) ) {
				foreach ($menu_item_modifier as $key => $value) {

					foreach ($value as $key1 => $value1) {

						foreach ($value1 as $key2 => $value2) {

							foreach ($value2 as $key3 => $value3) {

								if (!empty($value3) ) {
									$value3->delete($value3->id);
								}
							}
						}
					}
				}
			}

			//menu item
			if (isset($menu_item) > 0) {
				foreach ($menu_item as $key => $value) {
					foreach ($value as $key1 => $value1) {
						foreach ($value1 as $key2 => $value2) {
							if (!empty($value2) ) {
								$value2->delete($value2->id);
							}
						}
					}
				}
			}

			//menu time
			if (isset($menu_time) ) {
				foreach ($menu_time as $key => $value) {
					foreach ($value as $key1 => $value1) {

						if (!empty($value1)) {
							$value1->delete($value1->id);
						}
					}
				}
			}

			// menu category
			if (isset($menu_category) ) {
				foreach ($menu_category as $key => $value) {
					foreach ($value as $key1 => $value1) {
						if (!empty($value1)) {
							$value1->delete($value1->id);
						}
					}
				}
			}

			// menu
			if (isset($menu)) {
				foreach ($menu as $key => $value) {
					if (!empty($value)) {
						$value->delete($value->id);
					}
				}
			}

			// restaurant cuisine
			if (!empty($restaurant_cuisine)) {
				foreach ($restaurant_cuisine as $key => $value) {
					if (!empty($value)) {
						$value->delete($value->id);
					}
				}
			}
			
			// restaurant document
			if (!empty($restaurant_document)) {
				foreach ($restaurant_document as $key => $value) {
					if (!empty($value)) {
						$value->delete($value->id);
					}
				}
			}

			// restaurant offer
			if ($restaurant_offer->count() > 0) {
				foreach ($restaurant_offer as $key => $value) {

					if (!empty($value) ) {
						$value->delete($value->id);
					}
				}
			}
			
			// restaurant preparation time
			if (!empty($restaurant_preparation_time)) {
				foreach ($restaurant_preparation_time as $key => $value) {

					if (!empty($value)) {
						$value->delete($value->id);
					}
				}
			}

			// restaurant time
			if (isset($restaurant_time)) {
				foreach ($restaurant_time as $key => $value) {
					if (!empty($value) ) {
						$value->delete($value->id);
					}
				}
			}

			//wishlist
			if ($wishlist->count() > 0) {
				foreach ($wishlist as $key => $wish) {
					$wish->delete($wish->id);
				}
			}

			// restaurant
			if (isset($restaurant_id)) {
				$restaurant_id->delete($restaurant_id->id);
			}
		}

		//user details
		$user = User::whereId($request->id)->first();

		if (!empty($user) ) {

			$payout_preference = PayoutPreference::where('user_id', $request->id)->get();
			$user_payment_method = UserPaymentMethod::where('user_id', $request->id)->get();
			$user_promo_code = UsersPromoCode::where('user_id', $request->id)->get();
			$user_address = UserAddress::where('user_id', $request->id)->get();

			//payout preference
			if ($payout_preference->count() > 0) {
				foreach ($payout_preference as $key => $value) {

					if (!empty($value)) {
						$value->delete($value->id);
					}
				}
			}

			//user payment method
			if ($user_payment_method->count() > 0) {
				foreach ($user_payment_method as $key => $value) {

					if (!empty($value)) {
						$value->delete($value->id);
					}
				}
			}

			//user promo code
			if ($user_promo_code->count() > 0) {
				foreach ($user_promo_code as $key => $value) {

					if (!empty($value)) {
						$value->delete($value->id);
					}
				}
			}

			//user address
			if ($user_address->count() > 0) {
				foreach ($user_address as $key => $value) {

					if (!empty($value)) {
						$value->delete($value->id);
					}
				}
			}

			$user->delete($request->id);
			flash_message('success', trans('admin_messages.deleted_successfully'));
			return redirect()->route('admin.view_restaurant');

		}

	}

	/**
	 * Manage site setting
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function edit_restaurant(Request $request) {

		if ($request->getMethod() == 'GET') {
			$this->view_data['form_name'] = trans('admin_messages.edit_restaurant');
			$this->view_data['form_action'] = route('admin.edit_restaurant', $request->id);
			$this->view_data['restaurant'] = User::where('id', $request->id)->firstOrFail();

			$this->view_data['cuisine'] = Cuisine::where('status', 1)->pluck('name', 'id');
			$this->view_data['restaurant']->restaurant()->firstOrFail();
			
			$this->view_data['delivery_mode'] = array('1'=>trans('admin_messages.pickup_rest'),'2'=>trans('admin_messages.delievery_door'));

			$this->view_data['restaurant_document'] = $this->view_data['restaurant']->restaurant->restaurant_document()->with('file')->get();
			$this->view_data['restaurant_cuisine'] = $this->view_data['restaurant']->restaurant->restaurant_cuisine()->pluck('cuisine_id', 'id')->toArray();

			// dump($this->view_data, $this->view_data['restaurant']->last_name,, $this->view_data['restaurant']->first_name);

			return view('admin/restaurant/add_restaurant', $this->view_data);
		} else {
			
			$all_variables = request()->all();
			if ($all_variables['date_of_birth']) {
				$all_variables['convert_dob'] = date('Y-m-d', strtotime($all_variables['date_of_birth']));
			}

			$rules = array(
				'first_name' => 'required',
				'last_name' => 'required',
				'restaurant_name' => 'required',
				'restaurant_description' => 'required',
				// 'min_time' => 'required',
				// 'max_time' => 'required|after:min_time',
				'cuisine' => 'required',
				'email' => 'required|email|unique:user,email,' . $request->id,
				'convert_dob' => 'required|before:18 years ago',
				'restaurant_status' => 'required',
				'price_rating' => 'required',
				'user_status' => 'required',
				'phone_country_code' => 'required',
				'address' => 'required',
				//'banner_image' => 'image|mimes:jpg,png,jpeg,gif',

				'restaurant_logo' => 'image|mimes:jpg,png,jpeg,gif',

				'email' => 'required|email|unique:user,email,' . $request->id . ',id,type,1',
				'mobile_number' => 'required|regex:/^[0-9]+$/|min:6|unique:user,mobile_number,' . $request->id.',id,type,1',

				'is_free' => 'required',
				'delivery_mode' => 'required',
			);
			if ($request->password) {
				$rules['password'] = 'min:6';
			}
			if(
				$request->is_free=='0' &&
				@$request->delivery_mode &&
				in_array('2' , @$request->delivery_mode)
			) {
				$rules['delivery_fee'] = 'required|min:1|numeric';
			}

			// Add Admin User Validation Custom Names
			$niceNames = array(
				'first_name' => trans('admin_messages.first_name'),
				'last_name' => trans('admin_messages.last_name'),
				'restaurant_name' => trans('admin_messages.restaurant_name'),
				'restaurant_description' => trans('admin_messages.restaurant_description'),
				'delivery_mode' => trans('admin_messages.delivery_mode'),
				// 'min_time' => trans('admin_messages.min_time'),
				// 'max_time' => trans('admin_messages.max_time'),
				'email' => trans('admin_messages.email'),
				'password' => trans('admin_messages.password'),
				'convert_dob' => trans('admin_messages.date_of_birth'),
				'phone_country_code' => trans('admin_messages.country_code'),
				'mobile_number' => trans('admin_messages.mobile_number'),
				'restaurant_status' => trans('admin_messages.restaurant_status'),
				'price_rating' => trans('admin_messages.price_rating'),
				'user_status' => trans('admin_messages.user_status'),
				'address' => trans('admin_messages.address'),
				//'banner_image' => trans('admin_messages.banner_image'),
				
				'restaurant_logo' => trans('admin_messages.restaurant_logo'),

				'delivery_fee' => trans('admin_messages.delivery_fee'),
				'is_free' => trans('admin_messages.delivery_fee'),
			);

			if ($request->document) {
				// dd($request->document);
				foreach ($request->document as $key => $value) {

					$rules['document.' . $key . '.name'] = 'required';
					if (@$value['document_file'] && $value['id'] != '') {
						$rules['document.' . $key . '.document_file'] = 'mimes:jpg,png,jpeg,pdf';
					} elseif ($value['id'] == '') {
						$rules['document.' . $key . '.document_file'] = 'required|mimes:jpg,png,jpeg,pdf';
					}

					$niceNames['document.' . $key . '.name'] = trans('admin_messages.document_name');
					$niceNames['document.' . $key . '.document_file'] = 'Please upload the file like jpg,png,jpeg,pdf format';
				}
			}
			$messages = array(
				'convert_dob.before' => 'Age must be 18 or older',
			);
			
			$validator = Validator::make($all_variables, $rules,$messages);
			$validator->setAttributeNames($niceNames);

			$validator->after(function ($validator) use ($request) {
				if ($request->latitude == '' || $request->longitude == '') {
		            $validator->errors()->add('address', 'Invalid address');
		        }
		    });
		    
			if ($validator->fails()) {
				// dd($validator);
				return back()->withErrors($validator)->withInput(); // Form calling with Errors and Input values
			} else {
				$restaurant = User::find($request->id);
				$restaurant->user_first_name = $request->first_name;
				$restaurant->user_last_name = $request->last_name;
				$restaurant->name = $request->first_name."~".$request->last_name;
				$restaurant->email = $request->email;
				if ($request->password) {
					$restaurant->password = Hash::make($request->password);
				}

				$restaurant->date_of_birth = $all_variables['convert_dob'];
				$restaurant->country_code = $request->phone_country_code;
				$restaurant->mobile_number = $request->mobile_number;
				$restaurant->status = $request->user_status;
				$restaurant->type = 1;
				$restaurant->save();

				$new_restaurant = Restaurant::where('user_id', $restaurant->id)->first();
				$new_restaurant->name = $request->restaurant_name;
				$new_restaurant->description = $request->restaurant_description;
				$new_restaurant->delivery_mode = implode(',',$request->delivery_mode);
				// $new_restaurant->min_time = $request->min_time;
				// $new_restaurant->max_time = $request->max_time;
				$new_restaurant->currency_code = DEFAULT_CURRENCY;
				$new_restaurant->price_rating = $request->price_rating;
				$new_restaurant->status = $request->restaurant_status;
				if ($request->user_status == 0) {
					$new_restaurant->recommend = 0;
				}

				$new_restaurant->is_free 		= $request->is_free;
				$new_restaurant->delivery_fee 	= 0;
				if(
					$request->is_free=='0' &&
					@$request->delivery_mode &&
					in_array('2' , @$request->delivery_mode)
				) {
					$new_restaurant->delivery_fee = $request->delivery_fee;
				}

				$new_restaurant->save();

				foreach ($request->cuisine as $value) {
					if ($value) {

						$cousine = RestaurantCuisine::where('restaurant_id', $new_restaurant->id)->where('cuisine_id', $value)->first();
						if ($cousine == '') {
							$cousine = new RestaurantCuisine;
						}

						$cousine->restaurant_id = $new_restaurant->id;
						$cousine->cuisine_id = $value;
						$cousine->status = 1;
						$cousine->save();
					}
				}
				//delete cousine
				$restaurant_time = RestaurantCuisine::where('restaurant_id', $new_restaurant->id)->whereNotIn('cuisine_id', $request->cuisine)->delete();

				$address = UserAddress::where('user_id', $restaurant->id)->default()->first();
				if ($address == '') {
					$address = new UserAddress;
				}

				$address->user_id = $restaurant->id;
				$address->address = $request->address;
				$address->country_code = $request->country_code;
				$address->postal_code = $request->postal_code;
				$address->city = $request->city;
				$address->state = $request->state;
				$address->street = $request->street;
				$address->latitude = $request->latitude;
				$address->longitude = $request->longitude;
				$address->default = 1;
				$address->save();

				//file

				if ($request->file('banner_image')) {

					$file = $request->file('banner_image');

					$file_path = $this->fileUpload($file, 'public/images/restaurant/' . $new_restaurant->id);

					$this->fileSave('restaurant_banner', $new_restaurant->id, $file_path['file_name'], '1');
					$orginal_path = Storage::url($file_path['path']);
					$size = get_image_size('restaurant_image_sizes');
					foreach ($size as $value) {
						$this->fileCrop($orginal_path, $value['width'], $value['height']);
					}

				}

				// restaurant_logo
				if ($request->file('restaurant_logo')) {
					$file = $request->file('restaurant_logo');

					$file_path = $this->fileUpload($file, 'public/images/restaurant/' . $new_restaurant->id);

					$this->fileSave('restaurant_logo', $new_restaurant->id, $file_path['file_name'], '1');
					$orginal_path = Storage::url($file_path['path']);
					$size = get_image_size('restaurant_logo');
					$this->fileCrop($orginal_path, $size['width'], $size['height']);
				}

				//documents
				if ($request->document) {
					$avaiable_id = array_column($request->document, 'id');
				} else {
					$avaiable_id = array();
				}

				$avaiable_id = array_filter($avaiable_id);
				RestaurantDocument::whereNotIn('id', $avaiable_id)->where('restaurant_id',$new_restaurant->id)->delete();
				//documents
				if ($request->document) {
					foreach ($request->document as $key => $value) {

						if ($value['id']) {
							$restaurant_document = RestaurantDocument::find($value['id']);
						} else {
							$restaurant_document = new RestaurantDocument;
						}

						if (@$value['document_file']) {
							$file = $value['document_file'];
							$file_path = $this->fileUpload($file, 'public/images/restaurant/' . $new_restaurant->id . '/documents');
							$file_id = $this->fileSave('restaurant_document', $new_restaurant->id, $file_path['file_name'], '1', 'multiple');
							$restaurant_document->document_id = $file_id;
						}
						$restaurant_document->name = $value['name'];
						$restaurant_document->restaurant_id = $new_restaurant->id;
						$restaurant_document->save();

					}
				}

				flash_message('success', trans('admin_messages.updated_successfully'));
				return redirect()->route('admin.view_restaurant');
			}

		}
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

	/**
	 * Manage
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function menu_category()
	{
		if(env("APP_ENV")=='live'){
			flash_message('danger', 'Data add,edit & delete Operation are restricted in live.');
		}
		$this->view_data['form_name'] = trans('admin_messages.restaurant_menu');
		$restaurant_id = request()->id;

		$this->view_data['restaurant'] = $restaurant = Restaurant::where('user_id', $restaurant_id)->first();

		$this->view_data['menu'] = $this->getMenu($restaurant->id,'en');

		return view('admin.menu_category', $this->view_data);
	}

	public function menu_locale(Request $request)
	{
		$menu = $this->getMenu($request->restaurant_id,$request->locale);

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
		$data['menu_time'] = MenuTime::where('menu_id', $request->id)->get();
		$data['translations'] = Menu::where('id', $request->id)->get();
		return $data;
	}

	public function update_menu_time()
	{

		$request = request();
		$locale = $request->locale;
		$restaurant_id = $request->restaurant_id;
		$menu_time = $request->menu_time;
		$menu_id = $request->menu_id;
		if($locale == 'en') {
			if($menu_id) {
				$restaurant_menu = Menu::where('restaurant_id', $restaurant_id)->where('id', $menu_id)->first();
			}
			else {
				$restaurant_menu = new Menu;
				$restaurant_menu->restaurant_id = $restaurant_id;
			}
		}
		else {
			if(!$menu_id) {
				return [
					'status' => false,
					'status_message' => __('messages.add_english_lang_first'),
				];
			}
			$restaurant_menu = Menu::where('restaurant_id', $restaurant_id)->where('id', $menu_id)->first();
			$restaurant_menu = $restaurant_menu->getTranslationById($locale, $restaurant_menu->id);
		}
		$restaurant_menu->name = $request->menu_name;
		$restaurant_menu->save();
		$in_day = array_column($menu_time, 'day');
		foreach ($menu_time as $time) {				
			if ($time['id'] != '') {
				$menu_time = MenuTime::find($time['id']);
			}
			else {
				$menu_time = new MenuTime;
				if($request->menu_id == '')
				{
					$menu_time->menu_id=$restaurant_menu->id;
				}
				else
				{
					$menu_time->menu_id = $menu_id;
				}		
				$menu_time->restaurant_id = $restaurant_id;
			}
			$menu_time->day = $time['day'];
			$menu_time->start_time = $time['start_time'];
			$menu_time->end_time = $time['end_time'];
			$menu_time->save();
		}
		MenuTime::where('menu_id', $menu_id)->whereNotIn('day', $in_day)->delete();
		if ($request->menu_id) {
			return ['message' => 'success', 'menu_name' => $restaurant_menu->name];
		}
		$data['menu'] = $this->getMenu($restaurant_id,$locale);
		return $data;
	}

	public function update_menu_item(Request $request)
	{
		$user_id = $request->restaurant_id;
		$locale = $request->locale;
		$restaurant_id = Restaurant::where('user_id', $user_id)->first()->id;
		try {
			\DB::beginTransaction();
			$update_data =[
				'price' => $request->menu_item_price,
				'tax_percentage' => $request->menu_item_tax,
				'type' => $request->menu_item_type,
				'status' => $request->menu_item_status,
			];
			$update_data['name'] = $request->menu_item_name;
					$update_data['description'] = $request->menu_item_desc;

			if($locale == 'en') {
				if ($request->menu_item_id) {
					$update_data['name'] = $request->menu_item_name;
					$update_data['description'] = $request->menu_item_desc;

					$menu_item = MenuItem::where('id',$request->menu_item_id)
						->update($update_data);

					$menu_item_id = $request->menu_item_id;
					$menu_item = MenuItem::find($request->menu_item_id);
				    $data['edit_menu_item_image'] = is_object($menu_item->menu_item_thump_image) ? '' : $menu_item->menu_item_thump_image;
					
					
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
				// MenuItem::where('id',$menu_item_id)->update($update_data);
				$menu_item = MenuItem::find($menu_item_id);
				$translation = $menu_item->getTranslationById($locale,$menu_item->id);
				$translation->name = $request->menu_item_name;
				$translation->description = $request->menu_item_desc;
				$translation->save();

				$data['edit_menu_item_image'] = is_object($menu_item->menu_item_thump_image) ? '' : $menu_item->menu_item_thump_image;


				$data['edit_menu_item_image'] = is_object($menu_item->menu_item_thump_image) ? '' : $menu_item->menu_item_thump_image;
					$data['edit_menu_item_name'] = $translation->name;
			}

			if ($request->file('file')) {
				$this->uploadRestaurantImage($request->file('file'),$menu_item->id,$restaurant_id);
			}

			$data['edit_menu_item_image'] = is_object($menu_item->menu_item_thump_image) ? '' : $menu_item->menu_item_thump_image;
					$data['edit_menu_item_name'] = $menu_item->name;

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
						$menu_modifier_id = $menu_modifier_item->id;
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
			$data['status'] = true;
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
			$data['error_message'] = $e->getMessage();
			$data['status_message'] = trans('messages.restaurant.this_item_use_in_order_so_cant_delete');
			return $data;
		}
	}

	public function remove_menu_time(Request $request)
	{
		$menu_time = MenuTime::find($request->id);

		if ($menu_time) {
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

				//delete item
				MenuItem::whereIn('id', $delete_menu_item->pluck('id'))->delete();
				//delete category
				MenuCategory::where('menu_id', $menu_id)->delete();
				//delete time
				MenuTime::where('menu_id', $menu_id)->delete();
				//delete menu
				Menu::where('id', $menu_id)->delete();
				MenuTranslations::where('menu_id',$menu_id)->delete();
				$data['status'] = 'true';
				\DB::commit();
				return $data;
			}
			\DB::commit();
		}
		catch (\Exception $e) {
			\DB::rollback();
			$data['status'] = 'false';
			$data['error_message'] = $e->getMessage();
			if ($request->category == 'modifier') {
				$data['status_message'] = 'This Modifier used in order so can\'t delete this';
			}
			else {
				$data['status_message'] = 'Some modifiers used in order so can\'t Modify/delete.';

			}
			return $data;
		}

	}

	/**
	 * Manage site setting
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function open_time() {
		$request = request();
		$this->view_data['restaurant'] = Restaurant::where('user_id', $request->restaurant_id)->firstOrFail();
		if ($request->getMethod() == 'GET') {

			$this->view_data['form_action'] = route('admin.edit_open_time', $request->restaurant_id);
			$this->view_data['form_name'] = trans('admin_messages.edit_open_time');
			$this->view_data['open_time'] = RestaurantTime::where('restaurant_id', $request->restaurant_id)->first();

			$this->view_data['open_time'] = (count($this->view_data['restaurant']->restaurant_all_time) > 0) ? $this->view_data['restaurant']->restaurant_all_time()->get()->toArray() : [array('day' => '')];
			// dd($this->view_data['open_time'] );
			return view('admin/restaurant/open_time', $this->view_data);
		} else {

			$req_time_id = array_filter($request->time_id);
			if (count($req_time_id)) {
				RestaurantTime::whereNotIn('id', $req_time_id)->where('restaurant_id', $this->view_data['restaurant']->id)->delete();
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
				$restaurant_insert->restaurant_id = $this->view_data['restaurant']->id;
				$restaurant_insert->save();
			}

		}

		flash_message('success', 'Updated successfully');
		return redirect()->route('admin.view_restaurant');

	}

	/**
	 * Manage site setting
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function preparation_time() {
		$request = request();
		$this->view_data['restaurant'] = Restaurant::where('user_id', $request->restaurant_id)->firstOrFail();
		if ($request->getMethod() == 'GET') {

			$this->view_data['preparation'] = RestaurantPreparationTime::where('restaurant_id', $this->view_data['restaurant']->id)->get();

			$this->view_data['max_time'] = convert_minutes(Restaurant::where('id', $this->view_data['restaurant']->id)->first()->max_time);

			$this->view_data['form_action'] = route('admin.edit_preparation_time', $request->restaurant_id);
			$this->view_data['form_name'] = trans('admin_messages.edit_preparation_time');

			// dd($this->view_data['open_time'] );
			return view('admin/restaurant/preparation_time', $this->view_data);
		} else {

			$restaurant = Restaurant::find($this->view_data['restaurant']->id);
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
					$restaurant_update->restaurant_id = $this->view_data['restaurant']->id;
					$restaurant_update->save();
					$available_id[] = $restaurant_update->id;
				}

				if (isset($available_id)) {
					RestaurantPreparationTime::whereNotIn('id', $available_id)->delete();
				}

				flash_message('success', 'Updated successfully');
			}
			else{
				$store = RestaurantPreparationTime::where('restaurant_id',$this->view_data['restaurant']->id)->delete();
				flash_message('success', 'Updated successfully');
			}
			return redirect()->route('admin.view_restaurant');

		}

	}

	protected function uploadRestaurantImage($file,$menu_item_id,$restaurant_id)
	{
		$file_path = $this->fileUpload($file, 'public/images/restaurant/' . $restaurant_id . '/menu_item');		$this->fileSave('menu_item_image', $menu_item_id, $file_path['file_name'], '1');
		$orginal_path = Storage::url($file_path['path']);

		$size = get_image_size('item_image_sizes');
		foreach ($size as $new_size) {
			$this->fileCrop($orginal_path, $new_size['width'], $new_size['height']);
		}
	}


}
