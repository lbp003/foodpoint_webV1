@extends('template')

@section('main')
<main id="site-content" role="main" ng-controller="payout_preferences1" ng-cloak>
	<div class="partners">
		@include ('restaurant.navigation')
		<div class="partner-payments mt-4 mb-5">
			<h1>{{trans('messages.restaurant.payouts')}}</h1>

			<div class="payment-history my-5">
				<h5>{{trans('messages.restaurant.payout_history')}}</h5>
				<div class="table-responsive">
					<table>
						<thead>
							<tr>
								<th></th>
								<th>{{trans('messages.restaurant.day_of')}}</th>
								<th>{{trans('messages.restaurant.orders')}}</th>
								<th>{{trans('messages.restaurant.sale')}}</th>
								<th>{{trans('messages.restaurant.tax')}}</th>
								<th>{{trans('messages.restaurant.total')}}</th>
								<th>{{site_setting('site_name')}} {{trans('messages.restaurant.fee')}}</th>

								<th>{{trans('messages.new_changes.applied_owe_amount')}}</th>

								<th>{{trans('messages.restaurant.net_payout')}}</th>
								<th>{{trans('messages.restaurant.payout_status')}}</th>
								<th>{{trans('messages.restaurant.penalty')}}</th>
								<th>{{trans('messages.restaurant.paid_penalty')}}</th>
								<th class="notes-row">{{trans('messages.restaurant.notes')}}</th>
								<th>{{trans('messages.restaurant.status')}}</th>
								<th>{{trans('messages.restaurant.day_statement')}}</th>
							</tr>
						</thead>
						<tbody>
							@foreach($weekly_payouts as $key=>$value)
							<tr class="main-list">
								<td><i class="icon icon-angle-arrow-pointing-to-right-1 theme-color history-toggle"></i></td>
								<td><span class="theme-color text-nowrap">{{$value['week']}}</span></td>
								<td>{{$value['count']}}</td>
								<td>{!!$value['currency_symbol']!!} {{$value['subtotal']}}</td>
								<td>{!!$value['currency_symbol']!!} {{$value['tax']}}</td>
								<td>{!!$value['currency_symbol']!!} {{$value['total_amount']}}</td>
								<td>{!!$value['currency_symbol']!!} {{$value['gofer_fee']}}</td>

								<td>{!!$value['currency_symbol']!!} {{$value['total_owe_amount']}}</td>

								<td>{!!$value['currency_symbol']!!} {{$value['total_payout']}}</td>
								<td> {{$value['payout_status']}}</td>
								<td>{!!$value['currency_symbol']!!} {{$value['penalty']}}</td>
								<td>{!!$value['currency_symbol']!!} {{$value['paid_penalty']}}</td>
								<td></td>
								<td class="status pending"><label>{{$value['status']}}</label></td>
								<td class="text-center">
									<a href="{{url('restaurant/get_order_export/')}}/{{$value['table_date']}}" class="icon icon-download-button theme-color"></a>
								</td>
							</tr>

							@foreach($value['order_detail'] as $key1=>$value1)

							<tr class="history-view">
								<td></td>
								<td>{{trans('messages.profile_orders.order_id')}}:</td>
								<td>{{$value1->order->id}}</td>
								<td>{!!$value['currency_symbol']!!} {{$value1->order->subtotal}}</td>
								<td>{!!$value['currency_symbol']!!} {{$value1->order->tax}}</td>
								<td>{!!$value['currency_symbol']!!} {{$value1->order->restaurant_total}}</td>
								<td>{!!$value['currency_symbol']!!} {{($value1->order->restaurant_commision_fee)?$value1->order->restaurant_commision_fee:0}}</td>

								<td>{!!$value['currency_symbol']!!} {{($value1->order->applied_owe)?$value1->order->applied_owe:0}}</td>

								<td>{!!$value['currency_symbol']!!} {{$value1['amount']}}</td>
								<td> {{trans('messages.restaurant.'.$value1->status_text)}}</td>
								<td>{!!$value['currency_symbol']!!} {{((@$value1->order->penality_details->restaurant_penality-$value1->order->res_applied_penality)>0) ? ($value1->order->penality_details->restaurant_penality-$value1->order->res_applied_penality):'0.00'}}</td>
								<td>{!!$value['currency_symbol']!!} {{$value1->order->res_applied_penality?:'0.00'}}</td>
								<td class="notes-row">{{$value1->order->restaurant_notes}}</td>
								<td> {{ trans('messages.restaurant.'.$value1->order['status_text'])}}</td>
								<td class="status pending"></td>
							</tr>

							@endforeach
							@endforeach
						</tbody>
					</table>
					@if(count($weekly_payouts)==0)
					<div class="text-center">
						<h4>No payouts are available for you !</h4>
					</div>
					@endif
				</div>
				{{--
				<div class="d-flex align-items-center justify-content-end">
					<span>1 of 1</span>
					<nav aria-label="Page navigation example" class="my-3 ml-3">
						<ul class="pagination">
							<li class="page-item disabled">
								<a class="page-link" href="#" tabindex="-1">Previous</a>
							</li>
							<li class="page-item">
								<a class="page-link" href="#">Next</a>
							</li>
						</ul>
					</nav>
				</div>
				--}}
			</div>

		</div>
	</div>
</main>

@stop
