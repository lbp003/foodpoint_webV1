<?php

/**
 * UserAddress Model
 *
 * @package     GoferEats
 * @subpackage  Model
 * @category    UserAddress
 * @author      Trioangle Product Team
 * @version     1.3
 * @link        http://trioangle.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model
{
	/**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

	protected $table = 'user_address';

	protected $appends = ['address1', 'default_timezone','delivery_mode_text'];

	public $timestamps = false;

	Public $AddresstypeArray = [
		'home' => 0,
		'work' => 1,
		'delivery' => 2,
	];

	public function scopeDefault($query, $default = 1) {
		return $query->where('default', $default);
	}
	/**
	 * To check the Addresstype
	 */
	public function scopeAddressType($query, $status = 'home') {
		$type_value = $this->AddresstypeArray[$status];

		return $query->where('type', $type_value);
	}

	public function getStaticMapAttribute() {

		if(isset($this->attributes['latitude'])) {
			return 'https://maps.googleapis.com/maps/api/staticmap?center=' . $this->attributes['latitude'] . ',' . $this->attributes['longitude'] . '&markers=icon:' . url('images/map_green.png') . '|color:red|label:C|' . $this->attributes['latitude'] . ',' . $this->attributes['longitude'] . '&zoom=12&size=100x100&key=' . site_setting('google_api_key');
		} else {
			return '';
		}
	}

	public function getAddress1Attribute()
	{
		$address = '';
		if (isset($this->attributes['street'])) {
			if ($this->attributes['street']) {
				$address .= $this->attributes['street'];
			}

			if ($this->attributes['city']) {
				$address .= ' ' . $this->attributes['city'];
			}

			if ($this->attributes['state']) {
				$address .= ' ' . $this->attributes['state'];
			}

			if ($this->attributes['country']) {
				$address .= ' ' . $this->attributes['country'];
			}

		}
		return str_replace('  ', '', $address);
	}

	public function getDefaultTimezoneAttribute()
	{
		$time = Timezone::where('name', $this->country_code)->first();
		if ($time) {
			return $time->value;
		}
		return 'Asia/kolkata';
	}

	public function getDeliveryModeTextAttribute(){
		return (@$this->attributes['delivery_mode'] == 1) 
			? trans('admin_messages.pickup_rest') 
			: trans('admin_messages.delievery_door');
	}
}