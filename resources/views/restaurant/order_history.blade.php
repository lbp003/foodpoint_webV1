@extends('template')

@section('main')
<main id="site-content" role="main" ng-controller="order_history">
	<div class="partners order_history-page py-5 px-0" >
		@include ('restaurant.navigation')
		<div class="container" ng-init="order_history={{ $order_history }};">
			<div class="my-4 d-flex justify-content-between align-items-center">
				<h1 class="title">{{trans('messages.restaurant_dashboard.order_history')}}</h1>
			</div>
			<div class="order_history-table my-4">
				<div class="table-responsive">
					<table>
						<thead>
							<tr>
								<th>{{trans('admin_messages.order_id')}}</th>
								<th>{{trans('messages.restaurant.method')}}</th>
                                <th>{{trans('admin_messages.total')}}</th>
								<th>{{trans('admin_messages.order_status')}}</th>
								<th>{{trans('messages.restaurant_dashboard.timings')}}</th>
							</tr>
						</thead>
						<tbody>
							<tr ng-repeat="order_history_data in order_history">
								<td>@{{order_history_data.id_text}}</td>
								<td>@{{order_history_data.delivery_mode_text}}</td>
                                <td>@{{order_history_data.total_amount_text}}</td>
								<td>@{{order_history_data.status_text}}</td>
								<td>@{{order_history_data.order_time}}</td>
							</tr>
							<tr ng-hide="order_history.length>0"><td colspan="5">{{trans('messages.profile_orders.no_orders')}}</td> </tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</main>
@stop
