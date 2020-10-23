<?php 

/**
 * PaymentProcess Trait
 *
 * @package     Gofereats
 * @subpackage  PaymentProcess Trait
 * @category    PaymentProcess
 * @author      Trioangle Product Team
 * @version     1.3
 * @link        http://trioangle.com
 */

namespace App\Traits;

use App\Models\Wallet;
use App\Models\Payout;

trait PaymentProcess
{
	/**
	 * Payout yo user
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function admin_payout_to_user($user_id,$order_id)
	{
		$payout = Payout::with('user.payout_preference')->where('user_id', $user_id)->where('order_id', $order_id)->first();
		$data = $this->payout_to_users(floatval($payout->amount), currency_symbol(), $payout->user->payout_id);
		if ($data['success'] == true) {
			$payout->status = 1;
			$payout->transaction_id = $data['transaction_id'];
			$payout->save();
			$response['success'] = true;
			$response['message'] = trans('admin_messages.payment_sent_successfully');
		}
		else {
			$response['success'] = false;
			$response['message'] = $data['message'];
		}

		return $response;
	}

	public function payout_to_users($amount, $currency, $payout_account)
	{
		$stripe_payout = resolve('App\Repositories\StripePayout');

		$pay_data = array(
			"amount" 		=> ($amount * 100),
			"currency" 		=> $currency,
			"destination" 	=> $payout_account,
			"transfer_group"=> "ORDER_95",
		);

		$transfer = $stripe_payout->makeTransfer($pay_data);
		
		if(!$transfer['status']) {
			return array(
                'success' => false,
                'message' => $transfer['status_message'],
            );
		}

		$pay_data = array(
			"amount" 	=> ($amount * 100),
			"currency" 	=> $currency,
		);

		$payout = $stripe_payout->makePayout($payout_account,$pay_data);

		if(!$payout['status']) {
			return array(
                'success' => false,
                'message' => $payout['status_message'],
            );
		}

		return array(
            'success' => true,
            'transaction_id' => $payout['transaction_id'],
        );
	}

	public function refund_to_users($amount, $transaction_id)
	{
		$stripe_payment = resolve('App\Repositories\StripePayment');
		$amount = $amount * 100;

		$refund = $stripe_payment->refundPayment($transaction_id,$amount);

		if ($refund->status == 'success') {
			$data['success'] = true;
			$data['message'] = true;
			$data['transaction_id'] = $refund->intent_id;
		}
		else {
			$data['success'] = false;
			$data['message'] = $refund->status_message;
		}
		return $data;
	}

	public function refund_to_wallet($user_id, $amount)
	{
		$wallet = Wallet::where('user_id', $user_id)->first();

		if ($wallet == '') {
			$wallet = new Wallet;
		}
		$wallet->user_id = $wallet->user_id;
		$wallet->amount = $wallet->amount + $amount;
		$wallet->save();
	}
}