<?php

/**
 * RestaurantPreparationTime Model
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

class RestaurantPreparationTime extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'restaurant_preparation_time';

    public $timestamps =false;

    public function scopeIsActive($query)
    {
        return $query->where('status', 1);
    }
  
    public function getMaxTimeAttribute()
    {
        return  convert_minutes($this->attributes['max_time']);
    }
}