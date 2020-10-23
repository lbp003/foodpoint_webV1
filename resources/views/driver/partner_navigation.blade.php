	<div class="profile-img text-center col-12 col-md-3 col-lg-3 d-none d-md-block">
					@if(Auth::guard('driver')->user()->driver->driver_profile_picture =='')
					<img src="{{@$profile_image}}" class="profile_picture" />
					@else
					<img src="{{@Auth::guard('driver')->user()->driver->driver_profile_picture}}" class="profile_picture"/>
					@endif
					@if($driver_details)
						<h4>{{ str_replace('~',' ',$driver_details->name)}}</h4>
					@endif
					<div class="pro-nav">
						<ul class="navbar-nav mr-auto">
							<li class="nav-item">
								<a class="nav-link" href="{{route('driver.profile')}}">{{trans('messages.profile.profile')}}</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" href="{{route('driver.payment')}}">{{trans('messages.profile.earnings')}}</a>
							</li>
							<!-- <li class="nav-item">
								<a class="nav-link" href="{{route('driver.invoice')}}">Invoice</a>
							</li> -->
							<li class="nav-item ">
								<a class="nav-link" href="{{route('driver.trips')}}">{{trans('messages.profile.my_trips')}}</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" href="{{route('driver.logout')}}">{{trans('messages.profile.log_out')}}</a>
							</li>
						</ul>
					</div>
				</div>
</nav>