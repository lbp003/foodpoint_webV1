@extends('template2')

@section('main')
<main id="site-content" role="main" ng-controller="restaurants_search" ng-init="postal_code='{{session('postal_code')}}';city='{{session('locality')}}';latitude='{{session('latitude')}}';longitude='{{session('longitude')}}';location='{{session('location')}}';search_key='';schedule_status= '{{session('schedule_data') ? trans('messages.restaurant.'.session('schedule_data')['status']): trans('messages.restaurant.ASAP')}}';schedule_time_value={{json_encode(time_data('schedule_time'))}};delivery_mode_status= '{{ getDeliveryModeText( session('schedule_data')['delivery_mode'] ?? '2' ) }}';delivery_mode= '{{ session('schedule_data')['delivery_mode'] ?? '2' }}';pickup_txt='{{ trans('admin_messages.pickup_rest') }}';delivery_txt='{{ trans('admin_messages.delievery_door') }}';" class="search_page">
	<div class="search-top pt-4 pt-lg-5" >
		<div class="container">
			<div class="d-flex align-items-center">
				<div class="categories-menu d-block d-md-none text-nowrap">
					<i class="icon icon-dots-menu d-flex align-items-center">
						<span>{{trans('messages.store.categories')}}</span>
					</i>
				</div>
				
				<div class="search-field col-6 col-md-12 p-0 d-flex align-items-center" ng-init="langSearch='mytest'">
					<i class="icon icon-search-3"></i>
					<input ng-model="search_key" autocomplete="off" class="search-input w-100" type="text" placeholder= "{{trans('messages.store.search_for_restaurant_cuisine')}}" id="top_category_search" onfocus="this.placeholder = '{{trans('messages.store.search')}}'" />
					<div class="close-search">
						<i class="icon icon-close-2"></i>					
						<span class="d-none d-md-block">							
							{{trans('messages.store.enter_to_search')}}
						</span>
					</div>
				</div>
				<div class="search-category">
					<input type="hidden" id="request_cat" value="{{$request_cat}}">
					<div class="container">
						<div class="search-field col-12 p-0 d-flex d-md-none align-items-center">
							<i class="icon icon-search-3"></i>
							<input ng-model="search_key" autocomplete="off" class="search-input w-100" type="text" placeholder="{{trans('messages.store.search_for_restaurant_cuisine')}}" onfocus="this.placeholder = '{{trans('messages.store.search')}}'" id="top_category_search_mob" />
						</div>
						<div class="search-category-list">
							<div class="search-category-row">
								<h4>{{trans('messages.store.top_categories')}}</h4>
								<div class="search-list clearfix">
									@foreach($top_category_data as $top_category_row)
									<div class="search-item" style="background-image: url('{{$top_category_row->category_image}}');">
										<a href="{{route('search')}}?q={{$top_category_row->name}}" data-id="{{$top_category_row->id}}" class="category_top_val">
											<div class="search-info row d-flex align-items-center h-100">
												<p class="mx-auto">{{$top_category_row->name}}</p>
											</div>
										</a>
									</div>
									@endforeach
								</div>
							</div>
							<div class="search-category-row">
								<h4>{{trans('messages.store.more_categories')}}</h4>
								<div class="search-list clearfix">
									@foreach($cuisine_data as $cuisine_row)
									<div class="search-item" style="background-image: url('{{$cuisine_row->category_image}}');">
										<a href="{{route('search')}}?q={{$cuisine_row->name}}" data-id="{{$cuisine_row->id}}" class="category_more_val">
											<div class="search-info row d-flex align-items-center h-100">
												<p class="mx-auto">{{$cuisine_row->name}}</p>
											</div>
										</a>
									</div>
									@endforeach
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="header-search d-block d-lg-none nn_headersearch">
		<div class="container">
			<div class="mt-4 d-flex align-items-center justify-content-center ">
				<form class="search-form w-100 nn_search_form">
					<div class="search-type d-flex justify-content-center align-items-center flex-wrap flex-md-nowrap">
						<button class="btn btn-primary schedule-btn-sm nn_sm_btn" id="schedule_button" style="text-transform: uppercase;">@{{delivery_mode_status}} - @{{schedule_status}}</button>
						<input class="btn btn-primary schedule-btn" type="hidden" id="schedule_status_session"  style="display: none;" value="{{@session('schedule_data')[status]}}"></input>
						<input class="btn btn-primary schedule-btn" type="hidden" ng-model="schedule_status_clone"  style="display: none;"></input>
						<input type="hidden" id="schedule_data" value="{{json_encode(session('schedule_data'))}}">

						<div class="schedule-popup nn_popup align-items-center justify-content-center">
							<div class="schedule-dropdown nn_dropdown w-100">
								<div class="w-100 text-right">
									<i class="icon icon-close-2" id="schedule-close-sm"></i>
								</div>
								<div class="search-input w-100 ml-0">
									<svg width="16px" height="16px" viewBox="0 0 16 16" version="1.1"><g id="Symbols" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><g id="Icons-/-Semantic-/-Location" stroke="#262626"><g id="Group" transform="translate(1.500000, 0.000000)"><path d="M6.5,15.285929 L10.7392259,10.9636033 C13.0869247,8.56988335 13.0869247,4.68495065 10.7392259,2.29123075 C8.39683517,-0.0970769149 4.60316483,-0.0970769149 2.26077415,2.29123075 C-0.0869247162,4.68495065 -0.0869247162,8.56988335 2.26077415,10.9636033 L6.5,15.285929 Z" id="Combined-Shape"></path><circle id="Oval-3" cx="6.5" cy="6.5" r="2"></circle></g></g></g></svg>
									<input type="text" class="w-100 text-truncate" placeholder="{{ trans('messages.store.enter_your_address') }}" value="{{session('location')}}"  id="location_search_mob" />
								</div>
								<ul class="nav nav-tabs">
						          <li class="active nn_delivery w-50">
						          	<a data-toggle="tab" href="#delivery1" data-dmode="2" class="delivery_mode_tab @{{  delivery_mode=='2' ? 'active' : '' }}" >{{ trans('admin_messages.delievery_door') }}</a>
						          </li>
						          <li class="nn_delivery w-50">
						          	<a data-toggle="tab" href="#delivery1" data-dmode="1" class="delivery_mode_tab @{{  delivery_mode=='1' ? 'active' : '' }}" >{{ trans('admin_messages.pickup_rest') }}</a>
						          </li>
						          <!-- <li class="nn_pickup w-50"><a data-toggle="tab" href="#delivery1">Pickup</a></li> -->
						        </ul>
						        <div class="tab-content nn_tabcontent">
						        <div id="delivery1" class="tab-pane fade in active show">
									<div class="asap">
										<h3 class="schedule-option d-flex align-items-center {{@session('schedule_data')[status]!='Schedule'?'active':''}} " data-val="ASAP">
											<a class="w-100" href="#"><i class="icon icon-clock"></i>{{trans('messages.store.asap')}}</a>
											<i class="icon icon-checked"></i>
	             							<!-- <input type="radio" name="asap" class="nn_radiobtn"> -->
										</h3>
									</div>
									<div class="schedule-order">
										<h3 class="schedule-option d-flex align-items-center {{@session('schedule_data')[status]=='Schedule'?'active':''}} " data-val="Schedule" >
											<a class="w-100" href="#"><i class="icon icon-clock"></i>{{trans('messages.store.schedule_order')}}</a>
											<i class="icon icon-checked"></i>
	              							<!-- <input type="radio" name="asap" class="nn_radiobtn"> -->
										</h3>
										<div class="schedule-form pd-15">
											<div class="form-group">
												<label>{{trans('messages.store.date')}}</label>
												<div class="select" ng-init="schedule_date='{{session('schedule_data')['date']?:date('Y-m-d')}}';schedule_time='{{session('schedule_data')['time']}}'">
													<select id="mob_schedule_date" ng-model="schedule_date">
														<option disabled="disabled" value="">{{trans('messages.restaurant_dashboard.select')}}</option>  
														@foreach(date_data() as $key=>$data)

														<option value="{{$key}}" {{ ($key == session('schedule_data')['date']) ? 'selected' : '' }}>{{date('Y', strtotime($data)).', '.trans('messages.driver.'.date('M', strtotime($data))).' '.date('d', strtotime($data))}}</option>
														@endforeach
													</select>
												</div>
											</div>
											<div class="form-group">
												<label>{{trans('messages.store.time')}}</label>
												<div class="select">
													<select id="mob_schedule_time"  >
														<option ng-selected="schedule_time==key" ng-repeat="(key ,value) in schedule_time_value" value="@{{key}}" ng-if="(key | checkTimeInDay :schedule_date)">@{{value}}</option>
													</select>
												</div>
											</div>
											<button class="w-100 btn btn-theme" ng-click="save_time()"  type="submit">{{ trans('messages.store.set_time') }}</button>
										</div>
									</div>
								</div>
								 <div id="pickup1" class="tab-pane fade">
									<div class="asap">
										<h3 class="schedule-option d-flex align-items-center {{@session('schedule_data')[status]!='Schedule'?'active':''}} " data-val="ASAP">
											<a class="w-100" href="#"><i class="icon icon-clock"></i>{{trans('messages.store.asap')}}</a>
											<i class="icon icon-checked"></i>
	             							<!-- <input type="radio" name="asap" class="nn_radiobtn"> -->
										</h3>
									</div>
									<div class="schedule-order">
										<h3 class="schedule-option d-flex align-items-center {{@session('schedule_data')[status]=='Schedule'?'active':''}} " data-val="Schedule" >
											<a class="w-100" href="#"><i class="icon icon-clock"></i>{{trans('messages.store.schedule_order')}}</a>
											<i class="icon icon-checked"></i>
	              							<!-- <input type="radio" name="asap" class="nn_radiobtn"> -->
										</h3>
										<div class="schedule-form pd-15">
											<div class="form-group">
												<label>{{trans('messages.store.date')}}</label>
												<div class="select" ng-init="schedule_date='{{session('schedule_data')['date']?:date('Y-m-d')}}';schedule_time='{{session('schedule_data')['time']}}'">
													<select id="mob_schedule_date" ng-model="schedule_date">
														<option disabled="disabled" value="">{{trans('messages.restaurant_dashboard.select')}}</option>  
														@foreach(date_data() as $key=>$data)

														<option value="{{$key}}" {{ ($key == session('schedule_data')['date']) ? 'selected' : '' }}>{{date('Y', strtotime($data)).', '.trans('messages.driver.'.date('M', strtotime($data))).' '.date('d', strtotime($data))}}</option>
														@endforeach
													</select>
												</div>
											</div>
											<div class="form-group">
												<label>{{trans('messages.store.time')}}</label>
												<div class="select">
													<select id="mob_schedule_time"  >
														<option ng-selected="schedule_time==key" ng-repeat="(key ,value) in schedule_time_value" value="@{{key}}" ng-if="(key | checkTimeInDay :schedule_date)">@{{value}}</option>
													</select>
												</div>
											</div>
											<button class="w-100 btn btn-theme" ng-click="save_time()"  type="submit">{{ trans('messages.store.set_time') }}</button>
										</div>
									</div>
								</div>
								</div>
							</div>
						</div>
						<span class="d-inline-block text-nowrap mx-2">{{trans('messages.store.to')}}</span>
						<div class="search-input schedule-btn-sm w-75 ml-0">
							<svg width="16px" height="16px" viewBox="0 0 16 16" version="1.1"><g id="Symbols" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><g id="Icons-/-Semantic-/-Location" stroke="#262626"><g id="Group" transform="translate(1.500000, 0.000000)"><path d="M6.5,15.285929 L10.7392259,10.9636033 C13.0869247,8.56988335 13.0869247,4.68495065 10.7392259,2.29123075 C8.39683517,-0.0970769149 4.60316483,-0.0970769149 2.26077415,2.29123075 C-0.0869247162,4.68495065 -0.0869247162,8.56988335 2.26077415,10.9636033 L6.5,15.285929 Z" id="Combined-Shape"></path><circle id="Oval-3" cx="6.5" cy="6.5" r="2"></circle></g></g></g></svg>
							<input type="text" class="w-100 text-truncate" id="locations_search" placeholder="{{ trans('messages.store.enter_your_address') }}" value="{{session('locality')}}" />
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>

	<div class="search-result pt-4 pt-md-5 pb-3" ng-cloak>
		<div class="container">
			<h1 ng-hide="search_key">{{ isset($request_cat) ? $request_cat : trans('messages.store.food')  }} {{trans('messages.store.delivery_in')}}  @{{location}}</h1>
			<h1 ng-show="search_key">{{trans('messages.store.results_for')}} @{{search_key}}</h1>
			<div class="whole-product my-4 my-md-5 row clearfix">
				<div class="product float-left col-lg-4 col-md-6 col-12" ng-repeat="restaurant in restaurant_data.category">
					<a href="details/@{{restaurant.restaurant_id}}" class="details_page" data-id="@{{restaurant.restaurant_id}}">
						<div class="product-img" style="background-image: url('@{{restaurant.banner.original}}');">
							<span class="closed-overlay" ng-if="restaurant.status==0 && restaurant.restaurant_closed!=0">
								{{ trans('messages.store.currently_unavailable') }}
							</span>
							<span class="closed-overlay" ng-if="restaurant.restaurant_closed==0">
								{{trans('messages.store.closed')}}
							</span>
							<span ng-if="restaurant.restaurant_offer-0!='0' && restaurant.status!=0 " class="pro-offer">
								<p>
									@{{restaurant.restaurant_offer[0].title}}
								</p>
								<small>
									@{{restaurant.restaurant_offer[0].description}}
								</small>
								<br>
								@{{restaurant.restaurant_offer[0].percentage}}% <span>{{trans('messages.store.off')}}</span>
							</span>
						</div>

						<div class="product-info">
							<h2 class="text-truncate">
								<a href="details/@{{restaurant.restaurant_id}}" class="details_page" data-id="@{{restaurant.restaurant_id}}">
									@{{restaurant.name}}
									<span ng-if="restaurant.user_address.city">
										- @{{restaurant.user_address.city}}
									</span>
								</a>
							</h2>
							<div class="pro-category text-truncate pr-3">
								<p class="text-truncate">
									<i ng-repeat="i in repeater(restaurant.price_rating)" >{!!default_currency_symbol()!!}
									</i>
								</p>
								<p class="text-truncate" ng-repeat="menu in restaurant.category.split(',')">
									<span>•</span>
									@{{menu}}
								</p>
							</div>
							<div class="product-rating">
								<span ng-show="restaurant.restaurant_rating > 0">
									@{{restaurant.restaurant_rating }}
									<i class="icon icon-star mr-1"></i>(@{{restaurant.average_rating }})
								</span>
								<span ng-if="restaurant.status==0 && restaurant.restaurant_closed!=0">
									{{ trans('messages.store.currently_unavailable') }}
								</span>
								<span ng-if="restaurant.restaurant_closed!=0 && restaurant.status!=0 "> @{{restaurant.min_time}}–@{{restaurant.max_time}} {{trans('messages.store.min')}}</span>
								<span ng-if="restaurant.restaurant_closed==0"> @{{restaurant.restaurant_next_time}}</span>
							</div>
						</div>
					</a>
				</div>

				<div ng-show="restaurant_data.count=='0'" class="no-result text-center w-100">
					<h3>
						{{trans('messages.store.no_result')}}
					</h3>
				</div>
			</div>
		</div>
	</div>
</main>
@stop
