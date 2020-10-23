<?php

namespace App\Http\Middleware;

use App;
use Request;
use Session;
use App\Models\Language;
use App\Models\Pages;
use View;
use Schema;

class Locale
{
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, \Closure $next)
	{
		$locale = Language::translatable()->where('default_language', '1')->first()->value;
		
        $session_language = Language::translatable()->where('value',Session::get('language'))->first();

        if ($session_language) {
            $locale = $session_language->value;
        }
		
		$root = check_current_root();
        $page = $root == 'web' ? 'eater' : $root;
                          
        $setLocale = ($root=='admin') ? 'en' : $locale;
        App::setLocale($setLocale);
        Session::put('language', $locale);
        
    	if($page != 'admin' && $page != 'api') {                       
            $static_pages_changes = Pages::User($page)->where('footer', 1)->where('status', '1')->get();
            View::share('static_pages_changes', $static_pages_changes->split(2));
        }

		return $next($request);
	}
}