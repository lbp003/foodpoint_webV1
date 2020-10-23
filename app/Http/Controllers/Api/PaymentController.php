<?php

/**
 * Payment Controller
 *
 * @package    GoferEats
 * @subpackage Controller
 * @category   Payment
 * @author     Trioangle Product Team
 * @version    1.2
 * @link       http://trioangle.com
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Payout;
use App\Models\Restaurant;
use App\Models\User;
use App\Models\Wallet;
use App\Traits\PlaceOrder;
use App\Traits\FileProcessing;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use JWTAuth;
use Stripe;

class PaymentController extends Controller
{
	use PlaceOrder,FileProcessing;

	/**
	 * Eater Place order and payment
	 *
	 * @param Get method request inputs
	 *
	 * @return Response Json
	 */

	public function place_order(Request $request)
	{
		$user_details = JWTAuth::parseToken()->authenticate();
		$this->static_map_track($request->order_id);
		return $this->PlaceOrder($request, $user_details);
	}


	public function static_map_track($order_id)
	{
		$order = Order::findOrFail($order_id);
		$user_id = get_restaurant_user_id($order->restaurant_id);

		$res_address = get_restaurant_address($user_id);

		$user_address = get_user_address($order->user_id);

		$origin = $res_address->latitude . ',' . $res_address->longitude;
		$destination = $user_address->latitude . ',' . $user_address->longitude;

		$map_url = getStaticGmapURLForDirection($origin, $destination);

		$directory = storage_path('app/public/images/map_image');

		if (!is_dir($directory = storage_path('app/public/images/map_image'))) {
			mkdir($directory, 0755, true);
		}

		$time = time();
		$imageName = 'map_' . $time . '.PNG';
		$imagePath = $directory . '/' . $imageName;
		if ($map_url) {
			file_put_contents($imagePath, file_get_contents($map_url));
			$this->fileSave('map_image', $order_id, $imageName, '1');
		}
	}


	/**
	 * Refund when the restaurant not accept the order
	 *
	 * @param Get method request inputs
	 *
	 * @return Response Json
	 */
	public function cron_refund(Request $request)
	{
		$orders = Order::with('user.user_address')->whereIn('status', ['1'])->get();

		if ($orders->count() > 0) {
			foreach ($orders as $order) {

				date_default_timezone_set($order->user->user_address->default_timezone);

				$before_minutes = Carbon::now()->subMinutes(2)->format('Y-m-d H:i');
				$updated_at = date('Y-m-d H:i', strtotime($order->updated_at));
				if (strtotime($updated_at) <= strtotime($before_minutes)) {
					$refund = $this->refundOrder($order->id, '', $order->user_id);
					logger('refund '.$refund);
				}
			}
		}
	}

	public function refundOrder($order_id,$status = '', $user_id = '',$cancel_by='')
	{
		if ($status == 'Cancelled') {
			$order = Order::find($order_id);
		}
		else {
			$order = Order::where('id', $order_id)->whereIn('status', ['1'])->first();
		}

		if ($order == '') {
			return response()->json([
				'status_message' => 'invalid order',
				'status_code' => '1',
			]);
		}
		$user = $order->user;
		$wallet_amount = $order->wallet_amount;
		logger('wallet_amount '.$wallet_amount);
		if ($wallet_amount != 0) {
			$this->wallet_amount($wallet_amount, $order->user_id);
			if($order->payment_type == 0) {
				$push_notification_title = trans('api_messages.refund.wallet_amount_refunded') . $order->id;
				$push_notification_data = [
					'type' => 'Amount Refund',
					'order_id' => $order->id,
				];

				push_notification($user->device_type, $push_notification_title, $push_notification_data, 0, $user->device_id);
			}			
		}

		$update_order_details = $order;

		//Revert Penality amount if exists
		$penality_Revert = revertPenality($order->id);

		$cancelled_reason = trans('api_messages.orders.your_order_id').$order->id .trans('api_messages.orders.has_been_cancelled').ucfirst($cancel_by);

		if ($order->payment_type == 1) {
			$stripe_payment = resolve('App\Repositories\StripePayment');
			
			try {
				$payment = Payment::where('order_id', $order->id)->first();
				$amount = $payment->amount;
				if($payment->transaction_id != '1'){
					$refund = $stripe_payment->refundPayment($payment->transaction_id);
					if ($refund->status != 'success') {
						return response()->json([
							'status_code' => '1',
							'status_message' => $refund->status_message,
						]);
					}
				}

				$payout = new Payout;
				$payout->amount = $amount;
				$payout->transaction_id = $payment->transaction_id;
				$payout->currency_code = ($payment->transaction_id != '1') ? $refund->currency : $payment->currency_code;
				$payout->order_id = $order_id;
				$payout->user_id = $order->user_id;
				$payout->status = 1;
				$payout->save();

				if ($order->status == $order->statusArray['pending']) {
					$update_order_details->status = $order->statusArray['declined'];
				}

				/* Refund Notification */
				$push_notification_title = trans('api_messages.refund.amount_refunded') . $order->id;
				$push_notification_data = [
					'type' => 'Amount Refund',
					'order_id' => $order->id,
				];

				push_notification($user->device_type, $push_notification_title, $push_notification_data, 0, $user->device_id);

				/* Cancel Notification */
				$user = $order->user;

				$push_notification_title = ($status == 'Cancelled') ? $cancelled_reason : __('api_messages.refund.restaurant_not_accepted');
				$push_notification_data = [
					'type' => 'order_cancelled',
					'order_id' => $update_order_details->id,
					'order_data' => [
						'id' => $update_order_details->id,
						'user_name' => $update_order_details->user->name,
						'status_text' => $update_order_details->status_text,
					],
				];

				$update_order_details->declined_at = date('Y-m-d H:i:s');
				$update_order_details->schedule_status = 0;
				$update_order_details->save();

				push_notification($user->device_type, $push_notification_title, $push_notification_data, 0, $user->device_id);
				return response()->json([
					'status_code' => '1',
					'status_message' => trans('api_messages.refund.refund_successfully'),
					'refund' => $refund,
				]);

			}
			catch (\Exception $e) {
				return response()->json([
					'status_code' => '0',
					'status_message' => $e->getMessage(),
				]);
			}
		}

		if ($order->status == $order->statusArray['pending']) {
			$update_order_details->declined_at = date('Y-m-d H:i:s');
			$update_order_details->status = $order->statusArray['declined'];
		}

		$update_order_details->schedule_status = 0;
		$update_order_details->save();

		/* Cancel Notification */
		if($cancel_by != "eater") {
			$user = $order->user;
			$push_notification_title = ($status == 'Cancelled') ? $cancelled_reason : __('api_messages.refund.restaurant_not_accept');
			
			$push_notification_data = [
				'type' 		=> 'order_cancelled',
				'order_id' 	=> $update_order_details->id,
				'order_data'=> [
					'id' 			=> $update_order_details->id,
					'user_name'		=> $update_order_details->user->name,
					'status_text'	=> $update_order_details->status_text,
				],
			];

			push_notification($user->device_type, $push_notification_title, $push_notification_data, 0, $user->device_id);
		}

		return response()->json([
			'status_code' => '1',
			'status_message' => trans('api_messages.refund.cash_order'),
		]);
	}

	/**
	 * Refund when the restaurant not accept the order
	 *
	 * @param Get method request inputs
	 *
	 * @return Response Json
	 */
	public function refund(Request $request, $status = '', $user_id = '',$cancel_by='')
	{
		$order_id = $request->order_id;

		return $this->refundOrder($order_id,$status, $user_id,$cancel_by);
	}

	/**
	 * Return amount to wallet when the restaurant not accept the order
	if using wallet amount
	 *
	 * @param Get method request inputs
	 *
	 * @return Response Json
	 */

	public function wallet_amount($amount, $user_id) {

		$wallet = Wallet::where('user_id', $user_id)->first();

		if ($wallet) {
			$wallet->amount = $wallet->amount + $amount;
			$wallet->save();
		}

		return;
	}
}
