<?php
/**
 * PayoutController
 *
 * @package    GoferEats
 * @subpackage  Controller
 * @category    PayoutController
 * @author      Trioangle Product Team
 * @version     1.3
 * @link        http://trioangle.com
 */

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\DataTables\PayoutDataTable;
use App\DataTables\PayoutDayReportDataTable;
use App\DataTables\PreDayPayoutDataTable;
use App\Http\Controllers\Controller;
use App\Http\Controllers\DataTableBase;
use App\Models\Payout;
use App\Models\PayoutPreference;
use App\Models\Restaurant;
use App\Models\User;
use App\Traits\PaymentProcess;
use DataTables;

class PayoutController extends Controller
{	
	use PaymentProcess;
	
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Manage site setting
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function payout()
	{
		$this->view_data['user_type'] = request()->user_type;
		$user_type = User::getType(request()->user_type);
		$this->view_data['form_name'] = trans('admin_messages.payout_management',['user_type'=>ucfirst($user_type)]);
		
		return view('admin.payout.view', $this->view_data);
		return $dataTable->render('admin.payout.view', $this->view_data);
	}

	/**
	 * Manage site setting
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function weekly_payout(PayoutDataTable $dataTable)
	{
		$user = User::findOrFail(request()->user_id);
		if ($user->type_text == 'restaurant') {
			$restaurant = Restaurant::where('user_id', request()->user_id)->firstOrFail();
			$this->view_data['name'] = $restaurant->name;
			$this->view_data['link'] = route('admin.edit_restaurant',$user->id);
		}
		else {
			$this->view_data['name'] = $user->name;
			$this->view_data['link'] = route('admin.edit_driver',$user->id);
		}

		$this->view_data['form_name'] = trans('admin_messages.weekly_payout');
		
		return $dataTable->render('admin.payout.user_week_payout', $this->view_data);
	}

	/**
	 * Manage site setting
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function payout_per_day_report(Request $request,PreDayPayoutDataTable $dataTable)
	{
		$user = User::with('payout_preference')->findOrFail($request->user_id);
		if ($user->type_text == 'restaurant') {
			$restaurant = Restaurant::where('user_id', $request->user_id)->firstOrFail();
			$this->view_data['name'] = $restaurant->name;
			$this->view_data['link'] = route('admin.edit_restaurant',$user->id);
		}
		else {
			$this->view_data['name'] = $user->name;
			$this->view_data['link'] = route('admin.edit_driver',$user->id);
		}
		$from = date('Y-m-d'.' 00:00:00', strtotime($request->start_date));
		$to = date('Y-m-d'.' 23:59:59', strtotime($request->end_date));

		$payment = Payout::with('order')
			->where('user_id', $request->user_id)
			->whereHas('order', function ($query) {
				$query->history();
			})
			->where('status', '!=', 1)
			->whereBetween('created_at', array($from, $to))
			->get();

		$this->view_data['week_payment'] = $payment->sum('amount');
		$this->view_data['payout_id'] = implode(',', $payment->pluck('id')->toArray());
		$this->view_data['payout_account_id'] = $user->payout_id;
		$this->view_data['payout_method'] = is_null($user->payout_preference) ? "" : $user->payout_preference->payout_method ; 
		if($this->view_data['payout_method'] == 'Manual') {
			$payout_details = $user->payout_preference->only(['paypal_email','account_number','holder_name','bank_name','branch_name','branch_code']);

			$payout_details = array_combine(
			    array_map(function($string) {
			    	return ucwords(str_replace('_', ' ', $string));
			    }, array_keys($payout_details)), 
			    array_values($payout_details)
			);
			$this->view_data['payout_details'] = json_encode($payout_details);
		}
		$this->view_data['start_date'] = $request->start_date;
		$this->view_data['end_date'] = $request->end_date;
		$this->view_data['user_id'] = $request->user_id;
		$this->view_data['form_name'] = trans('admin_messages.date_to',['from'=>$this->view_data['start_date'],'to'=>$this->view_data['end_date']]);
		return $dataTable->render('admin.payout.payout_per_day_report', $this->view_data);
	}

	/**
	 * Manage site setting
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function payout_day(Request $request,PayoutDayReportDataTable $dataTable)
	{
		$user = User::with('payout_preference')->findOrFail($request->user_id);
		if ($user->type_text == 'restaurant') {
			$restaurant = Restaurant::where('user_id', $request->user_id)->firstOrFail();
			$this->view_data['name'] = $restaurant->name;
			$this->view_data['link'] = route('admin.edit_restaurant',$user->id);
		}
		else {
			$this->view_data['name'] = $user->name;
			$this->view_data['link'] = route('admin.edit_driver',$user->id);
		}

		$date = date('Y-m-d', strtotime($request->date));

		$payment = Payout::with('order')
			->where('user_id', $request->user_id)
			->whereHas('order', function ($query) {
				$query->history();
			})
			->where('status', '!=', 1)
			->whereDate('created_at', $date)
			->get();

		$this->view_data['week_payment'] = $payment->sum('amount');
		$this->view_data['payout_id'] = implode(',', $payment->pluck('id')->toArray());
		$this->view_data['payout_method'] =  is_null($user->payout_preference) ? "" : $user->payout_preference->payout_method ;
		if($this->view_data['payout_method'] == 'Manual') {
			$payout_details = $user->payout_preference->only(['paypal_email','account_number','holder_name','bank_name','branch_name','branch_code']);

			$payout_details = array_combine(
			    array_map(function($string) {
			    	return ucwords(str_replace('_', ' ', $string));
			    }, array_keys($payout_details)), 
			    array_values($payout_details)
			);
			$this->view_data['payout_details'] = json_encode($payout_details);
		}
		$this->view_data['payout_account_id'] = $user->payout_id;
		$this->view_data['date'] = $request->date;
		$this->view_data['user_id'] = $request->user_id;
	
		$this->view_data['form_name'] = trans('admin_messages.payout_date',['date'=>$request->date]);
		return $dataTable->render('admin.payout.payout_day', $this->view_data);
	}

	/**
	 * Manage site setting
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function week_amount_payout(Request $request)
	{
		if(isset($request->date)) {
			$redirect_url = route('admin.payout_day', ['user_id' => $request->user_id,'date' => $request->date]);
		}
		else {
			$redirect_url = route('admin.payout_per_day', ['user_id' => $request->user_id, 'start_date' => $request->start_date, 'end_date' => $request->end_date]);
		}
		
		$payout_id = explode(',', $request->payout_id);
		$amount = $request->amount;
		$payout_account_id = $request->payout_account_id;
		$payout_preference = PayoutPreference::where('user_id',$request->user_id)->first();

		if ($amount <= 0) {
			flash_message('danger', 'Already payout this user');
			return redirect($redirect_url);
		}

		if($payout_preference->payout_method == 'Manual') {
			Payout::whereIn('id',$payout_id)->update([
				'status' => 1,
				'transaction_id' => "",
			]);

			flash_message('success', trans('admin_messages.payment_sent_successfully'));
			return redirect($redirect_url);
		}

		$data = $this->payout_to_users(floatval($amount), currency_symbol(), $payout_account_id);

		if ($data['success'] != true) {
			flash_message('danger', $data['message']);
			return redirect($redirect_url);	
		}

		foreach ($payout_id as $id) {
			$payout = Payout::find($id);
			$payout->status = 1;
			$payout->transaction_id = $data['transaction_id'];
			$payout->save();
		}

		flash_message('success', trans('admin_messages.payment_sent_successfully'));
		return redirect($redirect_url);
	}

	/**
	 * Manage site setting
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function amount_payout(Request $request)
	{
		$payout_preference = PayoutPreference::where('user_id',$request->user_id)->first();
		if($payout_preference->payout_method == 'Manual') {
			$payout = Payout::where('user_id', $request->user_id)->where('order_id', $request->order_id)->first();

			$payout->status = 1;
			$payout->transaction_id = "";
			$payout->save();

			flash_message('success', trans('admin_messages.payment_sent_successfully'));
			return redirect()->route('admin.view_order', $request->order_id);
		}

		$response = $this->admin_payout_to_user($request->user_id,$request->order_id);
		$success_class = ($response['success']) ? 'success' : 'danger';
		flash_message($success_class, $response['message']);
		return redirect()->route('admin.view_order', $request->order_id);
	}

	/**
	 * Manage
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function all_payout()
	{ 
		$users = User::with('payout')->where('type', request()->user_type);
		$filter_type = request()->filter_type;
		$to = '';
		$from = date('Y-m-d', strtotime(change_date_format(request()->from_dates)));
		if (request()->to_dates != '') {
			$to = date('Y-m-d', strtotime(change_date_format(request()->to_dates)));
		}
		$users = $users->whereHas('payout',function($query) use($from,$to){
				if (request()->to_dates != '') {
					$query->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to);
				}
			});

		$file_name = (request()->user_type == 1)?'Restaurant-Payouts':'Driver-Payouts';
		$users = $users->get();
		$datatable = DataTables::of($users)
			->addColumn('id', function ($users) {
				return @$users->id;
			})
			->addColumn('name', function ($users) {
				if (request()->user_type == 1) {
					return @$users->restaurant->name;
				} else {
					return @$users->name;
				}

			})
			->addColumn('total_paid_amount', function ($users) {
				return @currency_symbol() . ' ' . @$users->total_paid_amount;
			})
			->addColumn('total_earnings_amount', function ($users) {
				return @currency_symbol() . ' ' . @$users->total_earnings_amount;
			})
			->addColumn('status_text', function ($users) {
				return @$users->status_text;
			})
			->addColumn('action', function ($users) {
				return '<a title="' . trans('admin_messages.weekly_payout') . '" href="' . route('admin.weekly_payout', $users->id) . '" ><i class="material-icons">library_books</i></a>';

			});
		$columns = ['id', 'name', 'total_paid_amount', 'total_earnings_amount', 'status_text'];
		$base = new DataTableBase($users, $datatable, $columns,$file_name);
		return $base->render(null);

	}

}
