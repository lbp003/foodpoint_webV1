@php
if(request()->device=='mobile'){
$view_device='mobile';
}
@endphp
@if(!isset($view_device))
<header ng-controller="header_controller" ng-cloak ng-init="order_data={{json_encode(session('order_data'))}};schedule_status= '{{session('schedule_data') ? trans('messages.restaurant.'.session('schedule_data')['status']):trans('messages.restaurant.ASAP')}}';schedule_time_value={{json_encode(time_data('schedule_time'))}};delivery_mode_status= '{{ getDeliveryModeText( session('schedule_data')['delivery_mode'] ?? '2' ) }}';delivery_mode= '{{ session('schedule_data')['delivery_mode'] ?? '2' }}';pickup_txt='{{ trans('admin_messages.pickup_rest') }}';delivery_txt='{{ trans('admin_messages.delievery_door') }}';">


  <div class="container">
    <div class="top-panel d-block d-md-flex align-items-center justify-content-between">
      <div class="logo text-center d-flex justify-content-start flex-row flex-wrap">
       @if (@$page->user_page==1)
       <a href="{{route('restaurant.signup')}}">
        <img src="{{site_setting('1','1')}}"/>
      </a>
      @elseif (@$page->user_page==2)
      {{-- 
      <a href="{{route('driver.signup')}}">
        <img src="http://trioangledemo.com/foodpoint/public//storage/images/site_setting/logo.png"/>
      </a>
      --}}
      @else
      <a href="{{route('home')}}">

        @if(@$restaurant_logo_url)
        <img src="{{$restaurant_logo_url}}"/>
        @else
        <img src="{{site_setting('1','1')}}"/>
        @endif

      </a>
      @endif
     <!--  <div class="select_lang select nn_selectlang d-block d-md-none">  
             <svg class="MuiSvgIcon-root-339 c511 c517" focusable="false" viewBox="0 0 24 24" color="white" aria-hidden="true" role="presentation"><g><defs><path d="M9.99,0 C4.47,0 0,4.48 0,10 C0,15.52 4.47,20 9.99,20 C15.52,20 20,15.52 20,10 C20,4.48 15.52,0 9.99,0 Z M16.92,6 L13.97,6 C13.65,4.75 13.19,3.55 12.59,2.44 C14.43,3.07 15.96,4.35 16.92,6 Z M10,2.04 C10.83,3.24 11.48,4.57 11.91,6 L8.09,6 C8.52,4.57 9.17,3.24 10,2.04 Z M2.26,12 C2.1,11.36 2,10.69 2,10 C2,9.31 2.1,8.64 2.26,8 L5.64,8 C5.56,8.66 5.5,9.32 5.5,10 C5.5,10.68 5.56,11.34 5.64,12 L2.26,12 Z M3.08,14 L6.03,14 C6.35,15.25 6.81,16.45 7.41,17.56 C5.57,16.93 4.04,15.66 3.08,14 Z M6.03,6 L3.08,6 C4.04,4.34 5.57,3.07 7.41,2.44 C6.81,3.55 6.35,4.75 6.03,6 Z M10,17.96 C9.17,16.76 8.52,15.43 8.09,14 L11.91,14 C11.48,15.43 10.83,16.76 10,17.96 Z M12.34,12 L7.66,12 C7.57,11.34 7.5,10.68 7.5,10 C7.5,9.32 7.57,8.65 7.66,8 L12.34,8 C12.43,8.65 12.5,9.32 12.5,10 C12.5,10.68 12.43,11.34 12.34,12 Z M12.59,17.56 C13.19,16.45 13.65,15.25 13.97,14 L16.92,14 C15.96,15.65 14.43,16.93 12.59,17.56 Z M14.36,12 C14.44,11.34 14.5,10.68 14.5,10 C14.5,9.32 14.44,8.66 14.36,8 L17.74,8 C17.9,8.64 18,9.31 18,10 C18,10.69 17.9,11.36 17.74,12 L14.36,12 Z" id="globe-menu-path"></path></defs><g><g transform="translate(2.000000, 2.000000)"><g><mask fill="white"><use xlink:href="#globe-menu-path"></use></mask><use xlink:href="#globe-menu-path"></use></g></g></g></g></svg>
              {!! Form::select('language',$language, (Session::get('language')) ? Session::get('language') : $default_language[0]->value, ['class' => 'language-selector footer-select', 'aria-labelledby' => 'language-selector-label', 'id' => 'language_footer']) !!}
      </div> -->
     <!--  <div class="nn_ndhelp align-items-center d-none d-md-none responsive-mess">
              <a href="{{route('help_page',current_page())}}">
              <svg class="MuiSvgIcon-root-366 c511 c514" focusable="false" viewBox="0 0 24 24" color="white" aria-hidden="true" role="presentation"><g transform="translate(2.000000, 2.000000)"><path d="M10.0048023,19.2980769 C11.3948926,19.2980769 12.7176429,19.0240385 13.9153332,18.5336538 C13.9441932,18.5192308 13.9682432,18.5096154 13.9971032,18.5 C14.0019132,18.5 14.0067232,18.5 14.0067232,18.4951923 C14.1750732,18.4326923 14.3578533,18.3990385 14.5454433,18.3990385 C14.7522733,18.3990385 14.9494834,18.4375 15.1274534,18.5144231 L19.1678543,20 L18.1048441,15.75 C18.1048441,15.4951923 18.1769941,15.2548077 18.2924341,15.0480769 L18.2924341,15.0480769 C18.3309142,14.9855769 18.3693942,14.9230769 18.4126842,14.8701923 C19.4179744,13.3653846 19.9999845,11.5769231 19.9999845,9.65865385 C20.0096045,4.32211538 15.5314935,0 10.0048023,0 C4.47811101,0 0,4.32211538 0,9.64903846 C0,14.9807692 4.47811101,19.2980769 10.0048023,19.2980769 Z M14.6224033,8.46153846 C15.4737735,8.46153846 16.1616037,9.14903846 16.1616037,10 C16.1616037,10.8509615 15.4737735,11.5384615 14.6224033,11.5384615 C13.7710331,11.5384615 13.083203,10.8509615 13.083203,10 C13.083203,9.14903846 13.7710331,8.46153846 14.6224033,8.46153846 Z M10.0048023,8.46153846 C10.8561725,8.46153846 11.5440026,9.14903846 11.5440026,10 C11.5440026,10.8509615 10.8561725,11.5384615 10.0048023,11.5384615 C9.15343207,11.5384615 8.46560192,10.8509615 8.46560192,10 C8.46560192,9.14903846 9.15343207,8.46153846 10.0048023,8.46153846 Z M5.38720122,8.46153846 C6.23857141,8.46153846 6.92640157,9.14903846 6.92640157,10 C6.92640157,10.8509615 6.23857141,11.5384615 5.38720122,11.5384615 C4.53583103,11.5384615 3.84800087,10.8509615 3.84800087,10 C3.84800087,9.14903846 4.53583103,8.46153846 5.38720122,8.46153846 Z" transform="translate(10.000000, 10.000000) scale(-1, 1) translate(-10.000000, -10.000000) "></path></g></svg>
              </a>
      </div> -->
  <!--   <form class="search-form d-none d-md-none d-lg-block m-0 nn_searchform" ng-cloak>
      <div class="search-type d-none d-md-none d-lg-flex justify-content-center align-items-center" ng-cloak>
        <button class="btn btn-primary schedule-btn" type="button" id="schedule_button" style="display: none;width: 200px;padding: 8px; text-transform: uppercase; font-size: 13px;"> @{{delivery_mode_status}} - @{{schedule_status}}  </button>
        <input class="btn btn-primary schedule-btn" type="hidden" ng-model="schedule_status_clone"  style="display: none;"></input>
      <input class="btn btn-primary schedule-btn" type="hidden" id="schedule_status_session"  style="display: none;" value="{{@session('schedule_data')[status]}}"></input>
      </div>
    </form> -->
    </div>
    <input type="hidden" id="orderdata" value="{{json_encode(session('order_data'))}}">
    @if (Route::current()->uri() == '/' && Route::current()->uri() !== 'checkout')
    {{--
    <div class="flex-grow-1 header-search d-none d-md-block">
      <form class="d-flex justify-content-center" name="search">
        <div class="search-input w-50 pr-15">
          <svg width="16px" height="16px" viewBox="0 0 16 16" version="1.1"><g id="Symbols" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><g id="Icons-/-Semantic-/-Location" stroke="#262626"><g id="Group" transform="translate(1.500000, 0.000000)"><path d="M6.5,15.285929 L10.7392259,10.9636033 C13.0869247,8.56988335 13.0869247,4.68495065 10.7392259,2.29123075 C8.39683517,-0.0970769149 4.60316483,-0.0970769149 2.26077415,2.29123075 C-0.0869247162,4.68495065 -0.0869247162,8.56988335 2.26077415,10.9636033 L6.5,15.285929 Z" id="Combined-Shape"></path><circle id="Oval-3" cx="6.5" cy="6.5" r="2"></circle></g></g></g></svg>
          <input type="text" class="w-100 text-truncate" placeholder="{{trans('messages.enter_delivery_address')}}" value="{{session('location')}}" id="header_location_val" />
        </div>
        <button class="btn btn-theme" type="submit" id="find_food_header">{{trans('messages.find_food')}}</button>
      </form>
    </div>
    --}}
     <!-- <form class="search-form d-none d-md-none d-lg-block m-0 nn_searchform nn_homesearch" ng-cloak>
      <div class="search-type d-none d-md-none d-lg-flex justify-content-center align-items-center" ng-cloak>
        <button class="btn btn-primary schedule-btn nn_homebtn" type="button" id="schedule_button" style="display: none;width: 200px;padding: 8px; text-transform: uppercase;"> @{{delivery_mode_status}} - @{{schedule_status}}  </button>
        <div class="schedule-dropdown nn_dropdown w-100 res">
        <ul class="nav nav-tabs w-100">
          <li class="active nn_delivery w-50"><a data-toggle="tab" href="#delivery" class="active">Delivery</a></li>
           <li class="nn_delivery w-50"><a data-toggle="tab" href="#delivery">Pickup</a></li>
         <!--  <li class="nn_pickup w-50"><a data-toggle="tab" href="#delivery">Pickup</a></li> 
        </ul>

        <div class="tab-content nn_tabcontent">
          <div id="delivery" class="tab-pane fade in active show">
            <div class="asap">
            <h3 class="schedule-option d-flex align-items-center {{@session('schedule_data')[status]!='Schedule'?'active':''}} " data-val="ASAP">
              <a class="w-100" href="#"><i class="icon icon-clock"></i>{{trans('messages.store.asap')}}</a>
              <!-- <i class="icon icon-checked"></i> 
              <input type="radio" name="radio" class="nn_radio">
            </h3>
          </div>
            <div class="schedule-order">
            <h3 class="schedule-option d-flex align-items-center {{@session('schedule_data')[status]=='Schedule'?'active':''}} " data-val="Schedule">
              <a class="w-100" href="#"><i class="icon icon-clock"></i>{{trans('messages.store.schedule_order')}}</a>
              <!-- <i class="icon icon-checked"></i> 
              <input type="radio" name="radio" class="nn_radio">
            </h3>
            <div class="schedule-form pd-15">
              <div class="form-group">
                <label>{{trans('messages.store.date')}}</label>
                <div class="select" ng-init="schedule_date_value='{{session('schedule_data')['date']?:date('Y-m-d')}}';schedule_time_set='{{session('schedule_data')['time']}}'">
                  <select id="schedule_date" ng-model="schedule_date_value">
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
                  <select id="schedule_time" >
                    <option ng-selected="schedule_time_set==key" ng-repeat="(key ,value) in schedule_time_value" value="@{{key}}" ng-if="(key | checkTimeInDay :schedule_date_value)">@{{value}}</option>

                  </select>
                </div>
              </div>
              <button class="w-100 btn btn-theme" id="set_time" type="submit">{{trans('messages.store.set_time')}}</button>
            </div>
          </div>
          </div>
          <div id="pickup" class="tab-pane fade">
              <div class="asap">
            <h3 class="schedule-option d-flex align-items-center {{@session('schedule_data')[status]!='Schedule'?'active':''}} " data-val="ASAP">
              <a class="w-100" href="#"><i class="icon icon-clock"></i>{{trans('messages.store.asap')}}</a>
              <!-- <i class="icon icon-checked"></i> 
              <input type="radio" name="radio" class="nn_radio">

            </h3>
          </div>
          </div>
        </div>
        </div>
      </div>
    </form> -->
    @endif
    @if (Route::current()->uri() == 'checkout')
    <div class="flex-grow-1"></div>
    @endif
    @if (Route::current()->uri() !== '/' && Route::current()->uri() !== 'checkout' &&  !Route::current()->named("restaurant.*"))
    <div class="flex-grow-1 header-search d-flex align-items-center justify-content-center">
      <div class="categories-menu d-block col-4 col-md-3 mx-md-3 mx-lg-0 text-nowrap">
        <i class="icon icon-dots-menu d-none d-md-inline-block">
          <span>{{trans('messages.store.categories')}}</span>
        </i>
        <div class="category-list">
          <div class="container">
            <div class="row">
              <div class="d-block d-md-none text-right w-100 pr-15 close_opt">
                <i class="icon icon-close-2 sm-category-close"></i>
              </div>

              <input type="hidden" class="city" id="header_city" value="{{session('locality')}}">

              <div class="col-12 col-md-6 float-left recommended">
                <label>{{trans('messages.store.recommended')}}</label>
                <ul class="clearfix">
                  @if(menu_category('recommended')!='')
                  @foreach(menu_category('recommended') as $recomm)

                  <li><a href="{{route('search')}}?q={{$recomm->name}}" class="recommended_val" data-id="{{$recomm->id}}">{{$recomm->name}}</a></li>

                  @endforeach
                  @endif

                </ul>
              </div>
              <div class="col-12 mt-4 mt-md-0 col-md-6 float-right most-popular">
                <label>{{trans('messages.store.most_popular')}}</label>
                <div class="search-list clearfix">
                 @if(menu_category('most_popular')!='')
                 @foreach(menu_category('most_popular') as $popular)

                 <div class="search-item" style="background-image: url({{$popular->category_image}});">
                  <a href="{{route('search')}}?q={{$popular->name}}" data-id="{{$popular->id}}" class="popular_val">
                    <div class="search-info row d-flex align-items-center h-100">
                      <p class="mx-auto">{{$popular->name}}</p>
                    </div>
                  </a>
                </div>

                @endforeach
                @endif

              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <form class="search-form d-none d-md-none d-lg-block m-0 nn_searchform" ng-cloak>
      <div class="search-type d-none d-md-none d-lg-flex justify-content-center align-items-center" ng-cloak>
        <button class="btn btn-primary schedule-btn" type="button" id="schedule_button" style="display: none;width: 200px;padding: 8px; text-transform: uppercase; font-size: 13px;"> @{{delivery_mode_status}} - @{{schedule_status}}  </button>
        <input class="btn btn-primary schedule-btn" type="hidden" ng-model="schedule_status_clone"  style="display: none;"></input>
        <input class="btn btn-primary schedule-btn" type="hidden" id="schedule_status_session"  style="display: none;" value="{{@session('schedule_data')[status]}}"></input>

        <!-- <div class="schedule-dropdown">
            <input type="hidden" id="schedule_data" value="{{json_encode(session('schedule_data'))}}">
          <div class="asap">
            <h3 class="schedule-option d-flex align-items-center {{@session('schedule_data')[status]!='Schedule'?'active':''}} " data-val="ASAP">
              <a class="w-100" href="#"><i class="icon icon-clock"></i>{{trans('messages.store.asap')}}</a>
              <i class="icon icon-checked"></i>
            </h3>
          </div>
          <div class="schedule-order">
            <h3 class="schedule-option d-flex align-items-center {{@session('schedule_data')[status]=='Schedule'?'active':''}} " data-val="Schedule">
              <a class="w-100" href="#"><i class="icon icon-clock"></i>{{trans('messages.store.schedule_order')}}</a>
              <i class="icon icon-checked"></i>
            </h3>
            <div class="schedule-form pd-15">
              <div class="form-group">
                <label>{{trans('messages.store.date')}}</label>
                <div class="select" ng-init="schedule_date_value='{{session('schedule_data')['date']?:date('Y-m-d')}}';schedule_time_set='{{session('schedule_data')['time']}}'">
                  <select id="schedule_date" ng-model="schedule_date_value">
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
                  <select id="schedule_time" >
                    <option ng-selected="schedule_time_set==key" ng-repeat="(key ,value) in schedule_time_value" value="@{{key}}" ng-if="(key | checkTimeInDay :schedule_date_value)">@{{value}}</option>

                  </select>
                </div>
              </div>
              <button class="w-100 btn btn-theme" id="set_time" type="submit">{{trans('messages.store.set_time')}}</button>
            </div>
          </div>
        </div> -->
      <div class="schedule-dropdown nn_dropdown w-100 web">
        <ul class="nav nav-tabs">
          <li class="active nn_delivery w-50">
            <a data-toggle="tab" href="#delivery" data-dmode="2" class="delivery_mode_tab @{{  delivery_mode=='2' ? 'active' : '' }}">{{ trans('admin_messages.delievery_door') }}</a>
          </li>
          <li class="nn_delivery w-50">
            <a data-toggle="tab" href="#delivery" data-dmode="1" class="delivery_mode_tab @{{  delivery_mode=='1' ? 'active' : '' }}">{{ trans('admin_messages.pickup_rest') }}</a>
          </li>
        </ul>

        <div class="tab-content nn_tabcontent">
          <div id="delivery" class="tab-pane fade in active show">
          <div class="asap">
            <h3 class="schedule-option d-flex align-items-center {{@session('schedule_data')[status]!='Schedule'?'active':''}} " data-val="ASAP">
              <a class="w-100" href="#"><i class="icon icon-clock"></i>{{trans('messages.store.asap')}}</a>
              <i class="icon icon-checked"></i>
              <!-- <input type="radio" name="radio" class="nn_radio"> -->
            </h3>
          </div>
          <div class="schedule-order">
            <h3 class="schedule-option d-flex align-items-center {{@session('schedule_data')[status]=='Schedule'?'active':''}} " data-val="Schedule">
              <a class="w-100" href="#"><i class="icon icon-clock"></i>{{trans('messages.store.schedule_order')}}</a>
              <i class="icon icon-checked"></i>
             <!--  <input type="radio" name="radio" class="nn_radio"> -->
            </h3>
            <div class="schedule-form pd-15">
              <div class="form-group">
                <label>{{trans('messages.store.date')}}</label>
                <div class="select" ng-init="schedule_date_value='{{session('schedule_data')['date']?:date('Y-m-d')}}';schedule_time_set='{{session('schedule_data')['time']}}'">
                  <select id="schedule_date" ng-model="schedule_date_value">
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
                  <select id="schedule_time" >
                    <option ng-selected="schedule_time_set==key" ng-repeat="(key ,value) in schedule_time_value" value="@{{key}}" ng-if="(key | checkTimeInDay :schedule_date_value)">@{{value}}</option>

                  </select>
                </div>
              </div>
              <button class="w-100 btn btn-theme" id="set_time" type="submit">{{trans('messages.store.set_time')}}</button>
            </div>
          </div>
          </div>
          <div id="pickup" class="tab-pane fade">
              <div class="asap">
            <h3 class="schedule-option d-flex align-items-center {{@session('schedule_data')[status]!='Schedule'?'active':''}} " data-val="ASAP">
              <a class="w-100" href="#"><i class="icon icon-clock"></i>{{trans('messages.store.asap')}}</a>
              <i class="icon icon-checked"></i>
            <!--   <input type="radio" name="radio" class="nn_radio"> -->

            </h3>
          </div>
          </div>
        </div>
      </div>
        <span class="d-inline-block text-nowrap mx-2">{{trans('messages.store.to')}}</span>
        <div class="search-input w-75 ml-0">
          <svg width="16px" height="16px" viewBox="0 0 16 16" version="1.1"><g id="Symbols" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><g id="Icons-/-Semantic-/-Location" stroke="#262626"><g id="Group" transform="translate(1.500000, 0.000000)"><path d="M6.5,15.285929 L10.7392259,10.9636033 C13.0869247,8.56988335 13.0869247,4.68495065 10.7392259,2.29123075 C8.39683517,-0.0970769149 4.60316483,-0.0970769149 2.26077415,2.29123075 C-0.0869247162,4.68495065 -0.0869247162,8.56988335 2.26077415,10.9636033 L6.5,15.285929 Z" id="Combined-Shape"></path><circle id="Oval-3" cx="6.5" cy="6.5" r="2"></circle></g></g></g></svg>
          <input type="text" class="w-100 text-truncate" id="location_search_new" placeholder="{{ trans('messages.store.enter_your_address') }}" value="{{session('location')}}" />
        </div>
      </div>
      <span class="d-none text-danger location_error_msg">{{trans('messages.store.enter_your_delivery_address_to_see')}} </span>
    </form>
  </div>
  @endif
  <div class="main-menu">

    <nav class="navbar navbar-expand-md px-0">
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="icon-bar"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav mr-auto" ng-cloak>
          <li>
            <div class="nn_ndhelp d-none d-md-flex align-items-center">
              <svg class="MuiSvgIcon-root-366 c511 c514" focusable="false" viewBox="0 0 24 24" color="white" aria-hidden="true" role="presentation"><g transform="translate(2.000000, 2.000000)"><path d="M10.0048023,19.2980769 C11.3948926,19.2980769 12.7176429,19.0240385 13.9153332,18.5336538 C13.9441932,18.5192308 13.9682432,18.5096154 13.9971032,18.5 C14.0019132,18.5 14.0067232,18.5 14.0067232,18.4951923 C14.1750732,18.4326923 14.3578533,18.3990385 14.5454433,18.3990385 C14.7522733,18.3990385 14.9494834,18.4375 15.1274534,18.5144231 L19.1678543,20 L18.1048441,15.75 C18.1048441,15.4951923 18.1769941,15.2548077 18.2924341,15.0480769 L18.2924341,15.0480769 C18.3309142,14.9855769 18.3693942,14.9230769 18.4126842,14.8701923 C19.4179744,13.3653846 19.9999845,11.5769231 19.9999845,9.65865385 C20.0096045,4.32211538 15.5314935,0 10.0048023,0 C4.47811101,0 0,4.32211538 0,9.64903846 C0,14.9807692 4.47811101,19.2980769 10.0048023,19.2980769 Z M14.6224033,8.46153846 C15.4737735,8.46153846 16.1616037,9.14903846 16.1616037,10 C16.1616037,10.8509615 15.4737735,11.5384615 14.6224033,11.5384615 C13.7710331,11.5384615 13.083203,10.8509615 13.083203,10 C13.083203,9.14903846 13.7710331,8.46153846 14.6224033,8.46153846 Z M10.0048023,8.46153846 C10.8561725,8.46153846 11.5440026,9.14903846 11.5440026,10 C11.5440026,10.8509615 10.8561725,11.5384615 10.0048023,11.5384615 C9.15343207,11.5384615 8.46560192,10.8509615 8.46560192,10 C8.46560192,9.14903846 9.15343207,8.46153846 10.0048023,8.46153846 Z M5.38720122,8.46153846 C6.23857141,8.46153846 6.92640157,9.14903846 6.92640157,10 C6.92640157,10.8509615 6.23857141,11.5384615 5.38720122,11.5384615 C4.53583103,11.5384615 3.84800087,10.8509615 3.84800087,10 C3.84800087,9.14903846 4.53583103,8.46153846 5.38720122,8.46153846 Z" transform="translate(10.000000, 10.000000) scale(-1, 1) translate(-10.000000, -10.000000) "></path></g></svg>
              <a href="{{route('help_page',current_page())}}">
                {{trans('messages.new_changes.need_help')}}
              </a>
            </div>
          </li>
          <li>
            <!-- <select class="selectpicker" id="language_footer">
              <option value="en">English</option>
              <option value="ar">Arabic</option>
              
            </select>  -->
          <div class="select_lang select nn_selectlang d-none d-md-flex">  
            <svg class="MuiSvgIcon-root-339 c511 c517" focusable="false" viewBox="0 0 24 24" color="white" aria-hidden="true" role="presentation"><g><defs><path d="M9.99,0 C4.47,0 0,4.48 0,10 C0,15.52 4.47,20 9.99,20 C15.52,20 20,15.52 20,10 C20,4.48 15.52,0 9.99,0 Z M16.92,6 L13.97,6 C13.65,4.75 13.19,3.55 12.59,2.44 C14.43,3.07 15.96,4.35 16.92,6 Z M10,2.04 C10.83,3.24 11.48,4.57 11.91,6 L8.09,6 C8.52,4.57 9.17,3.24 10,2.04 Z M2.26,12 C2.1,11.36 2,10.69 2,10 C2,9.31 2.1,8.64 2.26,8 L5.64,8 C5.56,8.66 5.5,9.32 5.5,10 C5.5,10.68 5.56,11.34 5.64,12 L2.26,12 Z M3.08,14 L6.03,14 C6.35,15.25 6.81,16.45 7.41,17.56 C5.57,16.93 4.04,15.66 3.08,14 Z M6.03,6 L3.08,6 C4.04,4.34 5.57,3.07 7.41,2.44 C6.81,3.55 6.35,4.75 6.03,6 Z M10,17.96 C9.17,16.76 8.52,15.43 8.09,14 L11.91,14 C11.48,15.43 10.83,16.76 10,17.96 Z M12.34,12 L7.66,12 C7.57,11.34 7.5,10.68 7.5,10 C7.5,9.32 7.57,8.65 7.66,8 L12.34,8 C12.43,8.65 12.5,9.32 12.5,10 C12.5,10.68 12.43,11.34 12.34,12 Z M12.59,17.56 C13.19,16.45 13.65,15.25 13.97,14 L16.92,14 C15.96,15.65 14.43,16.93 12.59,17.56 Z M14.36,12 C14.44,11.34 14.5,10.68 14.5,10 C14.5,9.32 14.44,8.66 14.36,8 L17.74,8 C17.9,8.64 18,9.31 18,10 C18,10.69 17.9,11.36 17.74,12 L14.36,12 Z" id="globe-menu-path"></path></defs><g><g transform="translate(2.000000, 2.000000)"><g><mask fill="white"><use xlink:href="#globe-menu-path"></use></mask><use xlink:href="#globe-menu-path"></use></g></g></g></g></svg>
              {!! Form::select('language',$language, (Session::get('language')) ? Session::get('language') : $default_language[0]->value, ['class' => 'language-selector footer-select', 'aria-labelledby' => 'language-selector-label', 'id' => 'language_footer']) !!}
            </div>
          </li>
          @if(is_user())
          <li class="nav-item dropdown">
            <a class="nav-link d-inline-block align-middle user-name p-0 dropdown-toggle" data-toggle="dropdown" href="#">
              <i class="icon icon-z1 d-inline-block user-icon">
                @if(@Auth::guard('web') == '')
                <img src="{{url('/')}}/images/user.png" class="profile_picture"/>
                @else
                <img src="{{@Auth::guard('web')->user()->eater_image}}" class="profile_picture"/>
                @endif
              </i>
            </a>
            <div class="dropdown-menu">
              <ul class="navbar-nav mr-auto">
                <li class="nav-item">
                  <a class="nav-link" href="{{route('user_profile')}}">
                    <i class="icon icon-user"></i>
                    {{trans('messages.profile.profile')}}
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="{{route('user_payment')}}">
                    <i class="icon icon-credit-card"></i>
                    {{trans('messages.profile.payment')}}
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="{{route('orders')}}">
                    <i class="icon icon-restaurant-eating-tools-set-of-three-pieces"></i>
                    {{trans('messages.profile.orders')}}
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="{{route('logout')}}">
                    <i class="icon icon-logout"></i>
                    {{trans('messages.profile.log_out')}}
                  </a>
                </li>
              </ul>
            </div>
          </li>
          @else
          <li class="nav-item">
            <a class="nav-link btn btn-primary" href="{{route('login')}}">{{trans('messages.profile.sign_in')}}</a>
          </li>
          <li class="nav-item">
            <a class="nav-link btn btn-secondary" href="{{route('signup')}}" name="signup">{{trans('messages.profile.register')}}</a>
          </li>
          <li class="nav-item">
            <div class="select_lang select nn_selectlang d-block d-md-none">  
             <svg class="MuiSvgIcon-root-339 c511 c517" focusable="false" viewBox="0 0 24 24" color="white" aria-hidden="true" role="presentation"><g><defs><path d="M9.99,0 C4.47,0 0,4.48 0,10 C0,15.52 4.47,20 9.99,20 C15.52,20 20,15.52 20,10 C20,4.48 15.52,0 9.99,0 Z M16.92,6 L13.97,6 C13.65,4.75 13.19,3.55 12.59,2.44 C14.43,3.07 15.96,4.35 16.92,6 Z M10,2.04 C10.83,3.24 11.48,4.57 11.91,6 L8.09,6 C8.52,4.57 9.17,3.24 10,2.04 Z M2.26,12 C2.1,11.36 2,10.69 2,10 C2,9.31 2.1,8.64 2.26,8 L5.64,8 C5.56,8.66 5.5,9.32 5.5,10 C5.5,10.68 5.56,11.34 5.64,12 L2.26,12 Z M3.08,14 L6.03,14 C6.35,15.25 6.81,16.45 7.41,17.56 C5.57,16.93 4.04,15.66 3.08,14 Z M6.03,6 L3.08,6 C4.04,4.34 5.57,3.07 7.41,2.44 C6.81,3.55 6.35,4.75 6.03,6 Z M10,17.96 C9.17,16.76 8.52,15.43 8.09,14 L11.91,14 C11.48,15.43 10.83,16.76 10,17.96 Z M12.34,12 L7.66,12 C7.57,11.34 7.5,10.68 7.5,10 C7.5,9.32 7.57,8.65 7.66,8 L12.34,8 C12.43,8.65 12.5,9.32 12.5,10 C12.5,10.68 12.43,11.34 12.34,12 Z M12.59,17.56 C13.19,16.45 13.65,15.25 13.97,14 L16.92,14 C15.96,15.65 14.43,16.93 12.59,17.56 Z M14.36,12 C14.44,11.34 14.5,10.68 14.5,10 C14.5,9.32 14.44,8.66 14.36,8 L17.74,8 C17.9,8.64 18,9.31 18,10 C18,10.69 17.9,11.36 17.74,12 L14.36,12 Z" id="globe-menu-path"></path></defs><g><g transform="translate(2.000000, 2.000000)"><g><mask fill="white"><use xlink:href="#globe-menu-path"></use></mask><use xlink:href="#globe-menu-path"></use></g></g></g></g></svg>
              {!! Form::select('language',$language, (Session::get('language')) ? Session::get('language') : $default_language[0]->value, ['class' => 'language-selector footer-select', 'aria-labelledby' => 'language-selector-label', 'id' => 'language_footer']) !!}
            </div>
          </li>
          <li class="nav-item">
             <div class="nn_ndhelp align-items-center d-block d-md-none responsive-mess">
              <a href="{{route('help_page',current_page())}}">
              <svg class="MuiSvgIcon-root-366 c511 c514" focusable="false" viewBox="0 0 24 24" color="white" aria-hidden="true" role="presentation"><g transform="translate(2.000000, 2.000000)"><path d="M10.0048023,19.2980769 C11.3948926,19.2980769 12.7176429,19.0240385 13.9153332,18.5336538 C13.9441932,18.5192308 13.9682432,18.5096154 13.9971032,18.5 C14.0019132,18.5 14.0067232,18.5 14.0067232,18.4951923 C14.1750732,18.4326923 14.3578533,18.3990385 14.5454433,18.3990385 C14.7522733,18.3990385 14.9494834,18.4375 15.1274534,18.5144231 L19.1678543,20 L18.1048441,15.75 C18.1048441,15.4951923 18.1769941,15.2548077 18.2924341,15.0480769 L18.2924341,15.0480769 C18.3309142,14.9855769 18.3693942,14.9230769 18.4126842,14.8701923 C19.4179744,13.3653846 19.9999845,11.5769231 19.9999845,9.65865385 C20.0096045,4.32211538 15.5314935,0 10.0048023,0 C4.47811101,0 0,4.32211538 0,9.64903846 C0,14.9807692 4.47811101,19.2980769 10.0048023,19.2980769 Z M14.6224033,8.46153846 C15.4737735,8.46153846 16.1616037,9.14903846 16.1616037,10 C16.1616037,10.8509615 15.4737735,11.5384615 14.6224033,11.5384615 C13.7710331,11.5384615 13.083203,10.8509615 13.083203,10 C13.083203,9.14903846 13.7710331,8.46153846 14.6224033,8.46153846 Z M10.0048023,8.46153846 C10.8561725,8.46153846 11.5440026,9.14903846 11.5440026,10 C11.5440026,10.8509615 10.8561725,11.5384615 10.0048023,11.5384615 C9.15343207,11.5384615 8.46560192,10.8509615 8.46560192,10 C8.46560192,9.14903846 9.15343207,8.46153846 10.0048023,8.46153846 Z M5.38720122,8.46153846 C6.23857141,8.46153846 6.92640157,9.14903846 6.92640157,10 C6.92640157,10.8509615 6.23857141,11.5384615 5.38720122,11.5384615 C4.53583103,11.5384615 3.84800087,10.8509615 3.84800087,10 C3.84800087,9.14903846 4.53583103,8.46153846 5.38720122,8.46153846 Z" transform="translate(10.000000, 10.000000) scale(-1, 1) translate(-10.000000, -10.000000) "></path></g></svg>
              </a>
      </div>
          </li>
          @endif
          @if(session('locality') || total_count_card() > 0)
          <li class="nav-item">
            <a class="nav-link p-0" href="{{route('checkout')}}" id="card_page">
              <i class="icon icon-shopping-bag-1 {{total_count_card() > 0 ? 'active':''}}"></i>
              <span class="cart-count ml-1">
                <span id="count_card" class="text-hide" ng-cloak>
                  {{total_count_card()}}
                </span>
              </span>
            </a>
          </li>
          @endif
        </ul>
      </div>
    </nav>
  </div>
</div>
</div>
<div class="flash-container">
  @if(Session::has('message'))
  <div class="alert {{ Session::get('alert-class') }} text-center" role="alert">
    <a href="#" class="alert-close" data-dismiss="alert">&times;</a> {{ Session::get('message') }}
  </div>
  @endif
</div>
</header>
@endif

