<?php

use Illuminate\Database\Seeder;

class SiteSettingTableSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		DB::table('site_setting')->delete();

		DB::table('site_setting')->insert(array(
			array('name' => 'site_name', 'value' => 'GoferEats'),
			array('name' => 'site_url', 'value' => ''),
			array('name' => 'site_date_format', 'value' => 'd-m-Y'),
			array('name' => 'site_time_format', 'value' => '12'),
			array('name' => 'default_currency', 'value' => 'USD'),
			array('name' => 'default_language', 'value' => 'en'),
			array('name' => 'version', 'value' => '1.1'),
			array('name' => 'join_us_facebook', 'value' => 'https://www.facebook.com/Trioangle.Technologies/'),
			array('name' => 'join_us_twitter', 'value' => 'https://twitter.com/trioangle'),
			array('name' => 'join_us_youtube', 'value' => 'https://www.youtube.com/channel/UC2EWcEd5dpvGmBh-H4TQ0wg'),
			array('name' => 'eater_apple_link', 'value' => 'https://apps.apple.com/us/app/gofereats/id1449261057?ls=1'),
			array('name' => 'restaurant_apple_link', 'value' => 'https://apps.apple.com/us/app/gofereats-restaurant/id1508280920?ls=1'),
			array('name' => 'driver_apple_link', 'value' => 'https://apps.apple.com/us/app/gofereats-driver/id1508211269?ls=1'),
			array('name' => 'eater_android_link', 'value' => 'https://play.google.com/store/apps/details?id=com.trioangle.gofereats'),
			array('name' => 'restaurant_android_link', 'value' => 'https://play.google.com/store/apps/details?id=com.trioangle.gofereatsrestaurant'),
			array('name' => 'driver_android_link', 'value' => 'https://play.google.com/store/apps/details?id=com.trioangle.gofereatsdriver'),
			array('name' => 'google_api_key', 'value' => 'AIzaSyBo67LSkcBL1C-RZ8fKzSNOwG7tojMqRGg'),
			array('name' => 'stripe_publish_key', 'value' => 'pk_test_YaHLphrPuBFCI6ZzgCZkVEfX0076ZLmQ2N'),
			array('name' => 'stripe_secret_key', 'value' => 'sk_test_prrC6cMpVlePIl94ofWEverR0041bId7UA'),
			array('name' => 'stripe_api_version', 'value' => '2019-08-14'),
			array('name' => 'nexmo_key', 'value' => '8ff1c8ec'),
			array('name' => 'nexmo_secret_key', 'value' => '155a3P1Yx3x5P8d7'),
			array('name' => 'nexmo_from_number', 'value' => 'Nexmo'),
			array('name' => 'delivery_fee_type', 'value' => '1'),
			array('name' => 'delivery_fee', 'value' => '10'),
			array('name' => 'booking_fee', 'value' => '10'),
			array('name' => 'restaurant_commision_fee', 'value' => '10'),
			array('name' => 'driver_commision_fee', 'value' => '10'),
			array('name' => 'pickup_fare', 'value' => '15'),
			array('name' => 'drop_fare', 'value' => '20'),
			array('name' => 'distance_fare', 'value' => '3'),
			array('name' => 'email_driver', 'value' => 'smtp'),
			array('name' => 'email_host', 'value' => 'smtp.gmail.com'),
			array('name' => 'email_port', 'value' => '25'),
			array('name' => 'email_to_address', 'value' => 'trioangle1@gmail.com'),
			array('name' => 'email_from_address', 'value' => 'trioangle1@gmail.com'),
			array('name' => 'email_from_name', 'value' => 'GoferEats'),
			array('name' => 'email_encryption', 'value' => 'tls'),
			array('name' => 'email_user_name', 'value' => 'trioangle1@gmail.com'),
			array('name' => 'email_password', 'value' => 'hismljhblilxdusd'),
			array('name' => 'email_domain', 'value' => 'sandboxcc51fc42882e46ccbffd90316d4731e7.mailgun.org'),
			array('name' => 'email_secret', 'value' => 'key-3160b23116332e595b861f60d77fa720'),
			array('name' => 'fcm_server_key', 'value' => 'AIzaSyB2HQAjsOtED0ZoVBFICb8YJtweklpFGs0'),
			array('name' => 'fcm_sender_id', 'value' => '157445205846'),
			array('name' => 'site_support_phone', 'value' => '1800-00-2568'),
			array('name' => 'restaurant_km', 'value' => '10'),
			array('name' => 'driver_km', 'value' => '10'),
			array('name' => 'admin_prefix', 'value' => 'admin'),
			array('name' => 'site_translation_name', 'value' => 'GoferEats Arabic'),
			array('name' => 'locale', 'value' => ''),
			array('name' => 'site_pt_translation', 'value' => 'GoferEats Portugeues'),
			array('name' => 'ios_link', 'value' => 'https://apps.apple.com/in/app/gofereats/id1449261057'),
			array('name' => 'analystics', 'value' => ''),
			array('name' => 'defaulty_curreny_name', 'value' => 'USD Currenncy'),
			array('name' => 'defaulty_curreny_symbol', 'value' => '$'),
		));
	}
}