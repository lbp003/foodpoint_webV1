<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
 */

//Before login
Route::group(['middleware' => ['guest:admin','clear_cache']], function () {
	Route::get('/', 'AdminController@login')->name('login');
	Route::get('/login', 'AdminController@login')->name('login');
	Route::post('/authenticate', 'AdminController@authenticate')->name('authenticate');
});

//After login
Route::group(['middleware' => ['auth:admin','clear_cache']], function () {

	//admin Mnagement
	Route::get('/dashboard', 'AdminController@dashboard')->name('dashboard');
	Route::get('/logout', 'AdminController@logout')->name('logout');

	// Admin Users Management
    Route::get('admin_user', 'AdminController@index')->name('view_admin')->middleware('permission:view-admin');
    Route::match(array('GET', 'POST'),'add_admin_user', 'AdminController@add')->name('create_admin')->middleware('permission:create-admin');
    Route::match(array('GET', 'POST'),'edit_admin_users/{id}', 'AdminController@update')->name('update_admin')->middleware('permission:update-admin');
    Route::match(array('GET', 'POST'),'delete_admin_user/{id}', 'AdminController@delete')->name('delete_admin')->middleware('permission:delete-admin');

    Route::get('roles', 'RolesController@index')->name('view_role')->middleware('permission:view-role');
    Route::match(array('GET', 'POST'), 'add_role', 'RolesController@add')->name('create_role')->middleware('permission:create-role');
    Route::match(array('GET', 'POST'), 'edit_role/{id}', 'RolesController@update')->where('id', '[0-9]+')->name('update_role')->middleware('permission:update-role');
    Route::get('delete_role/{id}', 'RolesController@destroy')->where('id', '[0-9]+')->name('delete_role')->middleware('permission:delete-role');

	//Site setting
	Route::match(array('GET', 'POST'), '/site_setting', 'SiteSettingController@site_setting')->name('site_setting')->middleware('permission:manage-site_setting');

	//User Management
	Route::get('/view_user', 'UserController@view')->name('view_user')->middleware('permission:view-eater');
	Route::match(array('GET', 'POST'), '/add_user', 'UserController@add_user')->name('add_user')->middleware('permission:create-eater');
	Route::match(array('GET', 'POST'), '/edit_user/{id}', 'UserController@edit_user')->name('edit_user')->middleware('permission:update-eater');
	Route::get('/delete_user/{id}', 'UserController@delete')->name('delete_user')->middleware('permission:delete-eater');
	Route::get('/all_users', 'UserController@all_users')->name('all_users');
	Route::get('/penality', 'UserController@penality')->name('penality')->middleware('permission:manage-penality');

	//restaurant Management
	Route::get('/view_restaurant', 'RestaurantController@view')->name('view_restaurant')->middleware('permission:view-restaurant');
	Route::match(array('GET', 'POST'), '/add_restaurant', 'RestaurantController@add_restaurant')->name('add_restaurant')->middleware('permission:create-restaurant');
	Route::match(array('GET', 'POST'), '/edit_restaurant/{id}', 'RestaurantController@edit_restaurant')->name('edit_restaurant')->middleware('permission:update-restaurant');
	Route::get('/delete_restaurant/{id}', 'RestaurantController@delete')->name('delete_restaurant')->middleware('permission:delete-restaurant');
	Route::get('/all_restaurants', 'RestaurantController@all_restaurants')->name('all_restaurants')->middleware('permission:view-restaurant');
	Route::get('/recommend/{id}', 'RestaurantController@recommend')->name('recommend')->middleware('permission:update-restaurant');

	//driver Management
	// Route::get('/view_driver', 'DriverController@view')->name('view_driver')->middleware('permission:view-driver');
	// Route::match(array('GET', 'POST'), '/add_driver', 'DriverController@add_driver')->name('add_driver')->middleware('permission:create-driver');
	// Route::match(array('GET', 'POST'), '/edit_driver/{id}', 'DriverController@edit_driver')->name('edit_driver')->middleware('permission:update-driver');
	// Route::get('/delete_driver/{id}', 'DriverController@delete')->name('delete_driver')->middleware('permission:delete-driver');
	// Route::get('/all_drivers', 'DriverController@all_drivers')->name('all_drivers');
	// Route::get('/driver_request/{id}', 'DriverController@driver_request')->name('driver_request');
	// Route::get('/owe_amount', 'DriverController@oweAmount')->name('owe_amount')->middleware('permission:manage-owe_amount');

	//promo management
	Route::get('/promo', 'PromoCodeController@view')->name('promo')->middleware('permission:view-promo');
	Route::match(array('GET', 'POST'), '/add_promo', 'PromoCodeController@add')->name('add_promo')->middleware('permission:create-promo');
	Route::match(array('GET', 'POST'), '/edit_promo/{id}', 'PromoCodeController@edit')->name('edit_promo')->middleware('permission:update-promo');
	Route::match(array('GET', 'POST'), '/delete_promo/{id}', 'PromoCodeController@delete')->name('delete_promo')->middleware('permission:delete-promo');

	//Cuisine management
	Route::get('/cuisine', 'CuisineController@view')->name('cuisine')->middleware('permission:view-cuisine');
	Route::match(array('GET', 'POST'), '/add_cuisine', 'CuisineController@add')->name('add_cuisine')->middleware('permission:create-cuisine');
	Route::match(array('GET', 'POST'), '/edit_cuisine/{id}', 'CuisineController@edit')->name('edit_cuisine')->middleware('permission:update-cuisine');
	Route::match(array('GET', 'POST'), '/delete_cuisine/{id}', 'CuisineController@delete')->name('delete_cuisine')->middleware('permission:delete-cuisine');
	Route::get('/is_top/{id}/{column}', 'CuisineController@change_status')->name('is_top');
	Route::get('/most_popular/{id}/{column}', 'CuisineController@change_status')->name('most_popular');
	Route::get('/home_page/{id}/{column}', 'CuisineController@change_status')->name('home_page');

	// Manage Help Routes
    Route::get('help_category', 'HelpCategoryController@index')->name('help_category')->middleware('permission:view-help_category');
    Route::match(array('GET', 'POST'), 'add_help_category', 'HelpCategoryController@add')->name('add_help_category')->middleware('permission:create-help_category');
    Route::match(array('GET', 'POST'), 'edit_help_category/{id}', 'HelpCategoryController@update')->where('id', '[0-9]+')->name('edit_help_category')->middleware('permission:update-help_category');
    Route::get('delete_help_category/{id}', 'HelpCategoryController@delete')->where('id', '[0-9]+')->name('delete_help_category')->middleware('permission:delete-help_category');
    Route::get('help_subcategory', 'HelpSubCategoryController@index')->name('help_subcategory')->middleware('permission:view-help_subcategory');
    Route::match(array('GET', 'POST'), 'add_help_subcategory', 'HelpSubCategoryController@add')->name('add_help_subcategory')->middleware('permission:create-help_subcategory');
    Route::match(array('GET', 'POST'), 'edit_help_subcategory/{id}', 'HelpSubCategoryController@update')->where('id', '[0-9]+')->name('edit_help_subcategory')->middleware('permission:update-help_subcategory');
    Route::get('delete_help_subcategory/{id}', 'HelpSubCategoryController@delete')->where('id', '[0-9]+')->name('delete_help_subcategory')->middleware('permission:delete-help_subcategory');
    Route::get('help', 'HelpController@index')->name('help')->middleware('permission:view-help');
    Route::match(array('GET', 'POST'), 'add_help', 'HelpController@add')->name('add_help')->middleware('permission:create-help');
    Route::match(array('GET', 'POST'), 'edit_help/{id}', 'HelpController@update')->where('id', '[0-9]+')->name('edit_help')->middleware('permission:update-help');
    Route::get('delete_help/{id}', 'HelpController@delete')->where('id', '[0-9]+')->name('delete_help')->middleware('permission:delete-help');
    Route::get('ajax_help_subcategory/{id}', 'HelpController@ajax_help_subcategory')->where('id', '[0-9]+')->name('ajax_help_subcategory');


	//Static Page management
	Route::get('/static_page', 'PagesController@view')->name('static_page')->middleware('permission:view-static_page');
	Route::match(array('GET', 'POST'), '/add_static_page', 'PagesController@add')->name('add_static_page')->middleware('permission:create-static_page');
	Route::match(array('GET', 'POST'), '/edit_static_page/{id}', 'PagesController@edit')->name('edit_static_page')->middleware('permission:update-static_page');
	Route::match(array('GET', 'POST'), '/delete_static_page/{id}', 'PagesController@delete')->name('delete_static_page')->middleware('permission:delete-static_page');

	//country Page management
	Route::get('/country', 'CountryController@view')->name('country')->middleware('permission:view-country');
	Route::match(array('GET', 'POST'), '/add_country', 'CountryController@add')->name('add_country')->middleware('permission:create-country');
	Route::match(array('GET', 'POST'), '/edit_country/{id}', 'CountryController@edit')->name('edit_country')->middleware('permission:update-country');
	Route::match(array('GET', 'POST'), '/delete_country/{id}', 'CountryController@delete')->name('delete_country')->middleware('permission:delete-country');

	//currency Page management
	/*Route::get('/currency', 'CurrencyController@view')->name('currency')->middleware('permission:view-currency');
	Route::match(array('GET', 'POST'), '/add_currency', 'CurrencyController@add')->name('add_currency')->middleware('permission:create-currency');
	Route::match(array('GET', 'POST'), '/edit_currency/{id}', 'CurrencyController@edit')->name('edit_currency')->middleware('permission:update-currency');
	Route::match(array('GET', 'POST'), '/delete_currency/{id}', 'CurrencyController@delete')->name('delete_currency')->middleware('permission:delete-currency');
	*/

	// Manage Language
    Route::get('languages', 'LanguageController@index')->name('languages')->middleware('permission:view-language');
    Route::get('add_language', 'LanguageController@create')->name('create_language')->middleware('permission:create-language');
    Route::post('add_language', 'LanguageController@store')->name('store_language')->middleware('permission:create-language');
    Route::GET('edit_language/{id}', 'LanguageController@edit')->where('id', '[0-9]+')->name('edit_language')->middleware('permission:update-language');
    Route::POST('edit_language/{id}', 'LanguageController@update')->where('id', '[0-9]+')->name('update_language')->middleware('permission:update-language');
    Route::get('delete_language/{id}', 'LanguageController@destroy')->where('id', '[0-9]+')->name('delete_language')->middleware('permission:delete-language');

	//Metas
	Route::get('/metas', 'MetasController@view')->name('metas')->middleware('permission:manage-metas');
	Route::match(array('GET', 'POST'), '/metas/edit/{id}', 'MetasController@edit')->name('meta_edit')->middleware('permission:manage-metas');

	//order_cancel_reson  management
	Route::get('/cancel_reason', 'OrderCancelReasonController@view')->name('order_cancel_reason')->middleware('permission:view-cancel_reason');
	Route::match(array('GET', 'POST'), '/add_cancel_reason', 'OrderCancelReasonController@add')->name('add_cancel_reason')->middleware('permission:create-cancel_reason');
	Route::match(array('GET', 'POST'), '/edit_cancel_reason/{id}', 'OrderCancelReasonController@edit')->name('edit_cancel_reason')->middleware('permission:update-cancel_reason');
	Route::match(array('GET', 'POST'), '/delete_cancel_reason/{id}', 'OrderCancelReasonController@delete')->name('delete_cancel_reason')->middleware('permission:delete-cancel_reason');

	//review_issue_types  management
	Route::get('/review_issue_type', 'IssueTypeController@view')->name('issue_type')->middleware('permission:view-review_issue_type');
	Route::match(array('GET', 'POST'), '/add_issue_type', 'IssueTypeController@add')->name('add_issue_type')->middleware('permission:create-review_issue_type');
	Route::match(array('GET', 'POST'), '/edit_issue_type/{id}', 'IssueTypeController@update')->name('edit_issue_type')->middleware('permission:update-review_issue_type');
	Route::match(array('GET', 'POST'), '/delete_issue_type/{id}', 'IssueTypeController@delete')->name('delete_issue_type')->middleware('permission:delete-review_issue_type');

	//Recipient  management
	Route::get('/recipient', 'FoodReceiverController@view')->name('food_receiver')->middleware('permission:view-recipient');
	Route::match(array('GET', 'POST'), '/add_recipient', 'FoodReceiverController@add')->name('add_food_receiver')->middleware('permission:create-recipient');
	Route::match(array('GET', 'POST'), '/edit_recipient/{id}', 'FoodReceiverController@add')->name('edit_food_receiver')->middleware('permission:update-recipient');
	Route::match(array('GET', 'POST'), '/delete_recipient/{id}', 'FoodReceiverController@delete')->name('delete_food_receiver')->middleware('permission:delete-recipient');

	//home_slider  management
	Route::get('/home_slider', 'SliderController@view_home_slider')->name('view_home_slider')->middleware('permission:view-home_slider');
	Route::match(array('GET', 'POST'), '/add_home_slider', 'SliderController@home_slider')->name('add_home_slider')->middleware('permission:create-home_slider');
	Route::match(array('GET', 'POST'), '/edit_home_slider/{id}', 'SliderController@home_slider')->name('edit_home_slider')->middleware('permission:update-home_slider');
	Route::match(array('GET', 'POST'), '/delete_home_slider/{id}', 'SliderController@delete_home_slider')->name('delete_home_slider')->middleware('permission:delete-home_slider');

	//review_vehicle_types  management
	Route::get('review_vehicle_type', 'VehicleTypeController@view')->name('vehicle_type')->middleware('permission:view-vehicle_type');
	Route::match(array('GET', 'POST'), '/add_vehicle_type', 'VehicleTypeController@add')->name('add_vehicle_type')->middleware('permission:create-vehicle_type');
	Route::match(array('GET', 'POST'), '/edit_vehicle_type/{id}', 'VehicleTypeController@update')->name('edit_vehicle_type')->middleware('permission:update-vehicle_type');
	Route::match(array('GET', 'POST'), '/delete_vehicle_type/{id}', 'VehicleTypeController@delete')->name('delete_vehicle_type')->middleware('permission:delete-vehicle_type');

	//Restaurant order  management
	Route::group(['middleware' => 'permission:manage-orders'], function () {
		Route::get('/order', 'OrderController@orders')->name('order');
		Route::match(array('GET', 'POST'), '/view_order/{order_id}', 'OrderController@view_order')->name('view_order');
		Route::match(array('GET', 'POST'), '/all_orders', 'OrderController@all_orders')->name('all_orders');
		Route::match(array('GET', 'POST'), '/sort_order', 'OrderController@sort_order')->name('sort_order');
		Route::post('cancel_order', 'OrderController@cancel_order')->name('cancel_order');
	});

	//week payout
	Route::group(['middleware' => 'permission:manage-payouts'], function () {
		Route::post('admin_payout', 'OrderController@admin_payout')->name('admin_payout');

		Route::get('payout/{user_type}', 'PayoutController@payout')->name('payout')->where('user_type', '1');
		// |2
		Route::get('all_payout', 'PayoutController@all_payout')->name('all_payout');
		Route::get('weekly_payout/{user_id}', 'PayoutController@weekly_payout')->name('weekly_payout');
		Route::get('driver_payout/{driver_id}', 'PayoutController@driver_payout')->name('driver_payout');
		Route::get('per_day_report/{user_id}/{start_date}/{end_date}', 'PayoutController@payout_per_day_report')->name('payout_per_day');
		Route::get('payout_day/{user_id}/{date}', 'PayoutController@payout_day')->name('payout_day');
		Route::get('payout_to/{user_id}/{order_id}', 'PayoutController@amount_payout')->name('amount_payout');
		Route::post('week_amount_payout', 'PayoutController@week_amount_payout')->name('week_amount_payout');
	});

	Route::group(['middleware' => 'permission:update-restaurant'], function () {
		//category
		Route::match(array('GET', 'POST'), '/menu/{id}', 'RestaurantController@menu_category')->name('menu_category');
		Route::post('update_category', 'RestaurantController@update_category')->name('update_category');
		Route::post('menu_locale', 'RestaurantController@menu_locale')->name('menu_locale');
		Route::get('menu_time/{id}', 'RestaurantController@menu_time')->name('menu_time');
		Route::post('update_menu_time', 'RestaurantController@update_menu_time')->name('update_menu_time');
		Route::get('menu/remove_menu_time/{id}', 'RestaurantController@remove_menu_time')->name('remove_menu_time');
		Route::post('update_menu_item', 'RestaurantController@update_menu_item')->name('update_menu_item');
		Route::post('delete_menu', 'RestaurantController@delete_menu')->name('delete_menu');
		Route::get('preparation', 'RestaurantController@preparation')->name('preparation');
		Route::post('update_preparation_time', 'RestaurantController@update_preparation_time')->name('update_preparation_time');
		
		//open time
		Route::match(array('GET','POST'),'edit_open_time/{restaurant_id}', 'RestaurantController@open_time')->name('edit_open_time');
		//preparation time
		Route::match(array('GET','POST'),'edit_preparation_time/{restaurant_id}', 'RestaurantController@preparation_time')->name('edit_preparation_time');
	});
	
	// Email and Push Notification message
	Route::get('send_message', 'SendMessageController@index')->name('send_message')->middleware('permission:manage-send_message');
	Route::post('send_message', 'SendMessageController@sendMessage')->name('send_message')->middleware('permission:manage-send_message');
	Route::post('need_payout_info', 'SendMessageController@need_payout_info')->name('need_payout_info');
});