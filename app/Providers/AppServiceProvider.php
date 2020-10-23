<?php

namespace App\Providers;

use App\Models\Pages;
use App\Models\SiteSettings;
use App\Models\Language;
use App\Models\Currency;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		/*logger('requested URL : '.request()->fullUrl());
		if(request()->isMethod('POST')) {
			logger('Post Method Params : '.json_encode(request()->post()));
		}*/

		Schema::defaultStringLength(191);

		Blade::if('checkPermission', function ($permission) {
		     return Auth::guard('admin')->user()->can($permission);
		});
		
		if (env('DB_DATABASE') != '') {
		if (Schema::hasTable('site_setting')) {
			config()->set('fcm.http', [
				'server_key' => site_setting('fcm_server_key'),
				'sender_id' => site_setting('fcm_sender_id'),
				'server_send_url' => 'https://fcm.googleapis.com/fcm/send',
				'server_group_url' => 'https://android.googleapis.com/gcm/notification',
				'timeout' => 10,
			]);

			config()->set([
				'mail.driver' => site_setting('email_driver'),
				'mail.host' => site_setting('email_host'),
				'mail.port' => site_setting('email_port'),
				'mail.from' => ['address' => site_setting('email_from_address'),
					'name' => site_setting('email_from_name')],
				'mail.encryption' => site_setting('email_encryption'),
				'mail.username' => site_setting('email_user_name'),
				'mail.password' => site_setting('email_password'),

			]);

			if (site_setting('email_domain') == 'mailgun') {
				config()->set([
					'services.mailgun.domain' => site_setting('email_domain'),
					'services.mailgun.secret' => site_setting('email_secret'),
				]);
			}
		}
	 }
   }

	/**
	 * Register any application services.
	 *
	 * @return void
	 */
	public function register() {

		foreach (glob(app_path() . '/Helpers/*.php') as $file) {
			require_once $file;
		}

		if (env('DB_DATABASE') != '') {
			if (Schema::hasTable('site_setting')) {
				$this->app->singleton('site_setting', function ($app) {
					$setting = SiteSettings::all()->pluck('value', 'name');
					$setting['jquery_date_format'] = convertPHPToMomentFormat($setting['site_date_format']);
					$setting['restaurant_new_order_expiry_time'] = "00:01:00";
					return $setting;
				});
			}
			if (Schema::hasTable('static_page')) {
				$this->app->singleton('static_page', function ($app) {
					$page = request()->route()->getPrefix() == '' ? 'eater' : request()->route()->getPrefix();
					$static_pages = Pages::User($page)->where('footer', 1)->where('status', '1')->pluck('name', 'url');
					return $static_pages->split(2);
				});
			}

			if (Schema::hasTable('language')) {
				$this->app->singleton('language', function () {
					return Language::translatable()->get();
				});
			}
			if (Schema::hasTable('currency')) {
				$this->app->singleton('Currency', function () {
					return Currency::get();
				});
			}
		}
		$this->app->singleton('time_data', function () {

			$day = array('1' => trans('messages.monday'), '2' => trans('messages.tuesday'), '3' => trans('messages.wedsday'), '4' => trans('messages.thursday'), '5' => trans('messages.friday'), '6' => trans('messages.saturday'), '7' => trans('messages.sunday'));
			for ($i = 300; $i <= 3600; $i = $i + 300) {
				$hours = floor($i / 3600);
				$mins = floor($i / 60 % 60);
				$secs = floor($i % 60);
				$v = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
				if ($mins == 0) {
					$time[$v] = '1 Hour';
				} else {
					$time[$v] = $mins . ' Minutes';
				}
			}
			
			$start = strtotime(date('Y-m-d') . "0 minutes");
			$end = strtotime(date('Y-m-d') . " 24:00:00");
			while ($start < $end) {
				$time_drop[date('H:i:s', $start)] = date('h:i', $start).trans('messages.driver.'.date('a', $start));
				$schedule_time_drop[date('H:i:s', $start)] = date('h:i', $start).trans('messages.driver.'.date('a', $start)) . ' - ' . date('h:i', strtotime("+60 minutes", $start)).trans('messages.driver.'.date('a', strtotime("+60 minutes", $start)));
				$start = strtotime("+60 minutes", $start);
			}

			$start = strtotime(date('Y-m-d') . "0 minutes");
			$end = strtotime(date('Y-m-d') . " 24:00:00");
			while ($start < $end) {
				$schedule_time_drop[date('H:i:s', $start)] = date('h:i', $start).trans('messages.driver.'.date('a', $start)) . ' - ' . date('h:i', strtotime("+30 minutes", $start)).trans('messages.driver.'.date('a', strtotime("+30 minutes", $start)));
				$start = strtotime("+30 minutes", $start);
			}

			$time_drop[date('H:i:s', strtotime('23:59:00'))] = date('h:i', strtotime('23:59:00')).trans('messages.driver.'.date('a', strtotime('23:59:00')));
			$data['time'] = $time_drop;
			$data['day'] = $day;
			$data['minutes'] = $time;
			$data['schedule_time'] = $schedule_time_drop;
			return $data;
		});

		// Collection method to pluck mulitple items
		Collection::macro('pluckMultiple', function ($assoc) {
			return $this->map(function ($item) use ($assoc) {
				$list = [];
				foreach ($assoc as $key) {
					$list[$key] = data_get($item, $key);
				}
				return $list;
			}, new static );
		});

	}

}
