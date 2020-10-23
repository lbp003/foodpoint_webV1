<?php

/**
 * StartService Provider
 *
 * @package     GoferEats
 * @subpackage  Provider
 * @category    Service
 * @author      Trioangle Product Team
 * @version     1.3
 * @link        http://trioangle.com
 */

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Currency;
use App\Models\SiteSettings;
use View;
use Config;
use Schema;
use Auth;
use App;
use Session;
use Request;
use App\Models\Admin;
use App\Models\Pages;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Collection;

class StartServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
    	if(env('DB_DATABASE') != '') {
            if(Schema::hasTable('currency')) {
                $this->currency();
            }
            if(Schema::hasTable('site_setting')) {
                $this->site_settings();
            }
            if(Schema::hasTable('language')) {
                $this->language();
            }
            if(Schema::hasTable('static_page')) {
                $this->pages();
            }
		}
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
	 
    public function currency()
    {
        $default_currency = SiteSettings::where('name','default_currency')->first()->value;
        $currency = Currency::where('code',$default_currency)->first();
        define('DEFAULT_CURRENCY', $default_currency);
        View::share('default_currency', $default_currency);
        View::share('default_currency_symbol', $currency->symbol);
    }

	// Share Language Details to whole software
	public function language()
	{
        $language = resolve('language');
		// Language lists for footer
        View::share('lang', $language);
        View::share('language', $language->pluck('name', 'value'));  
		
		// Default Language for footer
		$default_language = $language->where('default_language', '=', '1')->values();

        View::share('default_language', $default_language);
        
        if(Request::segment(1) == ADMIN_URL) {
		    $default_language = $language->where('value', 'en')->values();
		}

        if($default_language->count() > 0) {
			Session::put('language', $default_language[0]->value);
			App::setLocale($default_language[0]->value);
		}
	}

    public function site_settings()
    {
        $site_settings = SiteSettings::all();
        $admin_prefix = $site_settings->where('name','admin_prefix')->first();
        $site_version = $site_settings->where('name','version')->first();
        // View::share('version', optional($site_version)->value ?? '1.0');
        $site_analystics = $site_settings->where('name','analystics')->first();
        View::share('analystics', $site_analystics->value);
        View::share('version', str_random(4));
        define('ADMIN_URL', optional($admin_prefix)->value ?? 'admin');

        $site_url = $site_settings->where('name','site_url')->first();

        if($site_url->value == '' && @$_SERVER['HTTP_HOST'] && !App::runningInConsole()) {
            $url = "http://".$_SERVER['HTTP_HOST'];
            $url .= str_replace(basename($_SERVER['SCRIPT_NAME']),"",$_SERVER['SCRIPT_NAME']);

            SiteSettings::where('name','site_url')->update(['value' =>  $url]);
        }
    }

    public function pages()
    {
        if (Schema::hasTable('static_page')) {
            $root = check_current_root();
            $page = $root == 'web' ? 'eater' : $root;
            
            if($page != 'admin' && $page != 'api') {
                $static_pages_changes = Pages::User($page)->where('footer', 1)->where('status', '1')->get();
                View::share('static_pages_changes', $static_pages_changes->split(2));
            }
        }
    }
}