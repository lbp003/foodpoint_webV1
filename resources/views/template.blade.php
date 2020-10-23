@include('common.head')

@if (Route::current()->uri() != 'restaurant/login' && Route::current()->uri() != 'restaurant/password' && Route::current()->uri() != 'restaurant/forget_password' && Route::current()->uri() != 'restaurant/mail_confirm' && Route::current()->uri() != 'restaurant/set_password')
	@include('common.header')
@endif


@yield('main')

@include('common.footer')

@include('common.foot')