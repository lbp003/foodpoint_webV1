<nav id="sidebar">
	<button id="sidebarCollapse" type="button" data-toggle="active" data-target="#sidebar" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
		<span class="icon-bar"></span>
	</button>
	<ul class="list-unstyled components">
		<li class="{{navigation_active('restaurant.dashboard') ? 'active':''}}">
			<a href="{{route('restaurant.dashboard')}}">
				<i class="icon icon-dashboard"></i>
				<span>{{ trans('admin_messages.dashboard') }}</span>
			</a>
		</li>

		<li class="d-md-none {{navigation_active('restaurant.profile') ? 'active':''}}">
			<a href="{{url('restaurant/profile')}}">
				<i class="icon icon-user"></i>
				<span>{{ trans('messages.profile.profile') }}</span>
			</a>
		</li>

		<li class="{{navigation_active('restaurant.offers') ? 'active':''}}">
			<a href="{{route('restaurant.offers')}}">
				<i class="icon icon-offer"></i>
				<span>{{ trans('messages.restaurant_dashboard.offers') }}</span>
			</a>
		</li>

		<li class="{{navigation_active('restaurant.payout_preference') ? 'active':''}}">
			<a href="{{route('restaurant.payout_preference')}}">
				<i class="icon icon-credit-card"></i>
				<span>{{ trans('messages.restaurant_dashboard.payout_details') }}</span>
			</a>
		</li>

		<li class="{{navigation_active('restaurant.menu') ? 'active':''}}">
			<a href="{{route('restaurant.menu')}}">
				<i class="icon icon-restaurant-eating-tools-set-of-three-pieces"></i>
				<span>{{ trans('admin_messages.category') }}</span>
			</a>
		</li>
		<li class="{{navigation_active('restaurant.preparation') ? 'active':''}}">
			<a href="{{route('restaurant.preparation')}}">
				<i class="icon icon-timer"></i>
				<span>{{ trans('messages.restaurant_dashboard.timings') }}</span>
			</a>
		</li>
		<li class="{{navigation_active('restaurant.order_history') ? 'active':''}}">
			<a href="{{route('restaurant.order_history')}}">
				<i class="icon icon-clock"></i>
				<span>{{ trans('messages.restaurant_dashboard.order_history') }}</span>
			</a>
		</li>

		@if(isset($static_pages))
		<!-- 	<li>
				<a href="{{url($static_pages[0]->url)}}">
					<i class="icon icon-question-mark"></i>
					<span>Help</span>
				</a>
			</li> -->
		@endif

		@if(@get_current_restaurant_id()!=='')
		<li class="d-md-none">
			<a href="{{route('restaurant.logout')}}">
				<i class="icon icon-logout"></i>
				<span>{{ trans('messages.profile.log_out') }}</span>
			</a>
		</li>
		@endif
	</ul>
</nav>