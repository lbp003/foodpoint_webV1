<?php

namespace App\Providers;

use App\Models\Cuisine;
use App\Models\Pages;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;
use Schema;
use View;

class RouteServiceProvider extends ServiceProvider {
	/**
	 * This namespace is applied to your controller routes.
	 *
	 * In addition, it is set as the URL generator's root namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'App\Http\Controllers';

	/**
	 * Define your route model bindings, pattern filters, etc.
	 *
	 * @return void
	 */
	public function boot() {
		//

		parent::boot();
		if (env('DB_DATABASE') != '') {
			

			$this->pages();
			// Calling Currency function
		}
	}

	/**
	 * Define the routes for the application.
	 *
	 * @return void
	 */
	public function map() {
		$this->mapApiRoutes();

		$this->mapAdminRoutes();

		// $this->mapDriverRoutes();

		$this->mapRestaurantRoutes();

		$this->mapWebRoutes();

		//
	}

	/**
	 * Define the "web" routes for the application.
	 *
	 * These routes all receive session state, CSRF protection, etc.
	 *
	 * @return void
	 */
	protected function mapWebRoutes() {
		Route::middleware('web','auth_check')
			->namespace($this->namespace)
			->group(base_path('routes/web.php'));
	}

	/**
	 * Define the "driver" routes for the application.
	 *
	 * These routes all receive session state, CSRF protection, etc.
	 *
	 * @return void
	 */
	protected function mapDriverRoutes() {
		Route::middleware('web')
			->prefix('driver')
			->name('driver.')
			->namespace($this->namespace . '\Driver')
			->group(base_path('routes/driver.php'));
	}

/**
 * Define the "Restaurant" routes for the application.
 *
 * These routes all receive session state, CSRF protection, etc.
 *
 * @return void
 */
	protected function mapRestaurantRoutes() {
		Route::middleware('web')
			->prefix('restaurant')
			->name('restaurant.')
			->namespace($this->namespace . '\Restaurant')
			->group(base_path('routes/restaurant.php'));
	}
	/**
	 * Define tge "admin" routes for the application
	 *
	 * These routes all receive session state, CSRF protection, etc.
	 *
	 * @return void
	 */
	protected function mapAdminRoutes() {

		$admin_prefix = "admin";
	    if (Schema::hasTable('site_setting')) {
            $admin_prefix = \DB::table('site_setting')->where('name', 'admin_prefix')->first()->value;
        }
		Route::middleware('web')
			->prefix($admin_prefix)
			->name('admin.')
			->namespace($this->namespace . '\Admin')
			->group(base_path('routes/admin.php'));
	}

	/**
	 * Define the "api" routes for the application.
	 *
	 * These routes are typically stateless.
	 *
	 * @return void
	 */
	protected function mapApiRoutes() {
		Route::prefix('api')
			->middleware('api')
			->namespace($this->namespace . '\Api')
			->group(base_path('routes/api.php'));
	}

	

	//static pages
	public function pages() {
		if (Schema::hasTable('static_page')) {
			$static_pages = Pages::select('id', 'url', 'name')->where('status', '=', '1')->get();

			View::share('static_pages', $static_pages);
		}

	}

}
