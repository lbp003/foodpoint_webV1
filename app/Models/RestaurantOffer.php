<?php

/**
 * RestaurantOffer Model
 *
 * @package     GoferEats
 * @subpackage  Model
 * @category    Restaurant
 * @author      Trioangle Product Team
 * @version     1.3
 * @link        http://trioangle.com
 */


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use DateTime;
use DB;

class RestaurantOffer extends Model
{
    protected $table = 'restaurant_offer';

    public function scopeActiveOffer($query)
    {
    	$date = \Carbon\Carbon::today();
        return $query->where('start_date', '<=', $date)->where('end_date', '>=', $date)->where('status', '1')->where('percentage', '>', '0');
    }
}