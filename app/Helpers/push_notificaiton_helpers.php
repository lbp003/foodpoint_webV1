<?php

use LaravelFCM\Facades\FCM;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;

if (!function_exists('push_notification')) {
	/**
	 * Push notification
	 *
	 * @param  string $device_type Device Type
	 * @param  string $push_title Push Notification Title
	 * @param  array  $data        Array of data for the push notification
	 * @param  string $user_type   User Type
	 * @param  srting $device_id   Device Id for the push notification
	 * @return LaravelFCM\Response\downstreamResponse                   Notification for the device
	 */
	function push_notification($device_type = 1, $push_title, $data, $user_type, $device_id, $is_background = false) {
		if($device_id == '') {
			logger( 'Device ID Empty - '.$device_id.' || device_type: '.$device_type );
			return false;
		}
		if ($device_type == 2) {
			push_notification_android($push_title, $data, $user_type, $device_id, $is_background);
		} else {
			push_notification_ios($push_title, $data, $user_type, $device_id, $is_background);
		}
	}
}

if (!function_exists('push_notification_android')) {

	/**
	 * Push notification for Android
	 *
	 * @param  string $push_title Push Notification Title
	 * @param  array  $data        Array of data for the push notification
	 * @param  string $user_type   User Type
	 * @param  srting $device_id   Device Id for the push notification
	 * @return LaravelFCM\Response\downstreamResponse                   Notification for the device
	 */
	function push_notification_android($push_title, $data, $user_type, $device_id, $is_background = false)
	{

		logger( 'ANDROID >> push_title: '.$push_title.' || device_id: '.$device_id.' || user_type: ' .$user_type);
		logger( json_encode($data) );

		$notificationBuilder = new PayloadNotificationBuilder($user_type);
		$notificationBuilder->setTitle("GoferEats")->setBody($push_title);

		$data['title'] = $push_title;

		$dataBuilder = new PayloadDataBuilder();
		$dataBuilder->addData(['custom' => $data]);

		$optionBuilder = new OptionsBuilder();
		$optionBuilder->setTimeToLive(15);

		$notification = null;
		$data = $dataBuilder->build();
		$option = $optionBuilder->build();

		try {
			$downstreamResponse = FCM::sendTo($device_id, $option, $notification, $data);
			logger( 'downstreamResponse : ' . json_encode($downstreamResponse) );
		}
		catch (\Exception $e) {
			logger( 'Exception : ' . $e->getMessage() );
		}
	}

}
if (!function_exists('push_notification_ios')) {

	/**
	 * Push notification for iOS
	 *
	 * @param  string $push_title Push Notification Title
	 * @param  array  $data        Array of data for the push notification
	 * @param  string $user_type   User Type
	 * @param  srting $device_id   Device Id for the push notification
	 * @return LaravelFCM\Response\downstreamResponse                   Notification for the device
	 */
	function push_notification_ios($push_title, $data, $user_type, $device_id, $is_background = false)
	{

		if(isset($data['custom_message'])) {
			$push_title = $data['custom_message']['message'];
		}

		logger( 'IOS >> push_title: '.$push_title.' || device_id: '.$device_id.' || user_type: ' .$user_type);
		logger( json_encode($data) );

		$notificationBuilder = new PayloadNotificationBuilder();
		$notificationBuilder->setBody($push_title)->setSound('default');

		$dataBuilder = new PayloadDataBuilder();
		$dataBuilder->addData(['custom' => $data]);

		$optionBuilder = new OptionsBuilder();
		$optionBuilder->setTimeToLive(15);

		$notification = $notificationBuilder->build();
		$data = $dataBuilder->build();
		$option = $optionBuilder->build();

		try {
			$downstreamResponse = FCM::sendTo($device_id, $option, $notification, $data);
			logger( 'downstreamResponse : ' . json_encode($downstreamResponse) );
		}
		catch (\Exception $e) {
			logger( 'Exception : ' . $e->getMessage() );
		}
	}
}
