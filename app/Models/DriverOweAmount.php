<?php

/**
 * DriverOweAmount Model
 *
 * @package    GoferEats
 * @subpackage Model
 * @category   DriverOweAmount
 * @author     Trioangle Product Team
 * @version    1.2
 * @link       http://trioangle.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverOweAmount extends Model
{
    
    protected $table = 'driver_owe_amount';

    protected $fillable = ['user_id', 'amount','currency_code','restaurant_id'];
    
    public $timestamps = false;

    public function getAmountAttribute()
    {
        return number_format(($this->attributes['amount']),2,'.',''); 
    }

    //paid_amount
    public function getPaidAmountAttribute()
    {
    	$amount = $this->payment()->get()->sum('amount');
        return  number_format(($amount),2,'.',''); 
    }
    
    // driver_name
    public function getDriverNameAttribute()
    {
    	return $this->user()->first()->name;
    }

    // Join with Menu table
    public function payment()
    {
        return $this->hasMany('App\Models\Payment', 'user_id', 'user_id');
    }
	
	public function user() {
		return $this->belongsTo('App\Models\User', 'user_id', 'id');
	}
    
    // restaurant_name
    public function getRestaurantNameAttribute()
    {
        return $this->restaurant()->first()->name;
    }
    
    public function restaurant() {
        return $this->belongsTo('App\Models\Restaurant', 'restaurant_id', 'id');
    }


   
}	
