<?php

/*
|--------------------------------------------------------------------------
| Restaurant Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
 */
Route::group(['middleware' => ['guest:restaurant', 'clear_cache','locale']], function () {

	Route::match(array('GET', 'POST'), 'signup', 'RestaurantController@signup')->name('signup');
	Route::match(array('GET', 'POST'), 'login', 'RestaurantController@login')->name('login');
	Route::match(array('GET', 'POST'), '/', 'RestaurantController@signup')->name('signup');
	Route::get('thanks', 'RestaurantController@thanks')->name('thanks');
	Route::match(array('GET', 'POST'), 'password', 'RestaurantController@password')->name('password');

	Route::get('forget_password', 'RestaurantController@forget_password')->name('forget_password');

	Route::match(array('GET', 'POST'), 'mail_confirm', 'RestaurantController@mail_confirm')->name('mail_confirm');

	Route::match(array('GET', 'POST'), 'set_password', 'RestaurantController@set_password')->name('set_password');

	Route::post('change_password', 'RestaurantController@change_password')->name('change_password');
});
//After login

Route::group(['middleware' => ['clear_cache', 'auth:restaurant','locale']], function () {

	Route::get('logout', 'RestaurantController@logout')->name('logout');

	Route::get('dashboard', 'RestaurantController@dashboard')->name('dashboard');
	Route::get('menu', 'RestaurantController@menu')->name('menu');
	Route::post('menu_locale', 'RestaurantController@menu_locale')->name('menu_locale');
	Route::post('update_category', 'RestaurantController@update_category')->name('update_category');
	Route::get('menu_time/{id}', 'RestaurantController@menu_time')->name('menu_time');
	Route::post('update_menu_time', 'RestaurantController@update_menu_time')->name('update_menu_time');
	Route::get('remove_menu_time/{id}', 'RestaurantController@remove_menu_time')->name('remove_menu_time');
	Route::post('update_menu_item', 'RestaurantController@update_menu_item')->name('update_menu_item');
	Route::post('delete_menu', 'RestaurantController@delete_menu')->name('delete_menu');
	Route::get('preparation', 'RestaurantController@preparation')->name('preparation');
	Route::post('update_preparation_time', 'RestaurantController@update_preparation_time')->name('update_preparation_time');
	Route::post('remove_time', 'RestaurantController@remove_time')->name('remove_time');

	Route::post('update_modifier', 'RestaurantController@update_modifier')->name('update_modifier');
	Route::post('delete_modifier', 'RestaurantController@delete_modifier')->name('delete_modifier');

//Profile controller

	Route::match(array('GET', 'POST'), 'profile', 'ProfileController@index')->name('profile');
	Route::post('send_message', 'ProfileController@send_message')->name('send_message');
	Route::post('confirm_phone_no', 'ProfileController@confirm_phone_no')->name('confirm_phone_no');
	Route::match(array('GET', 'POST'), 'documents', 'ProfileController@documents')->name('documents');
	Route::match(array('GET', 'POST'), 'offers', 'ProfileController@offers')->name('offers');
	Route::get('remove_offer/{id}', 'ProfileController@remove_offer')->name('remove_offer');
	Route::post('offers_status', 'ProfileController@offers_status')->name('offers_status');

	Route::get('payout_preference', 'RestaurantController@payout_preference')->name('payout_preference');

	Route::get('export_data/{week}', 'RestaurantController@get_export')->name('export_data');

	Route::get('get_order_export/{date}', 'RestaurantController@get_order_export')->name('get_order_export');

	Route::match(array('GET', 'POST'), '/payout_details/{week}', 'RestaurantController@payout_daywise_details')->name('payout_details');

	Route::post('get_payout_preference', 'RestaurantController@get_payout_preference')->name('get_payout_preference');

	Route::post('update_payout_preferences/{id}', 'RestaurantController@update_payout_preferences')->name('update_payout_preferences');
	Route::post('update_open_time', 'ProfileController@update_open_time')->name('update_open_time');
	Route::post('update_documents', 'ProfileController@update_documents')->name('update_documents');
	Route::match(array('GET', 'POST'), 'show_comments', 'ProfileController@show_comments')->name('show_comments');
	Route::match(array('GET', 'POST'), 'status_update', 'ProfileController@status_update')->name('status_update');

});