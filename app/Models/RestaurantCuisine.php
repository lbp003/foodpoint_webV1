<?php

/**
 * RestaurantCuisine Model
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
class RestaurantCuisine extends Model
{
   

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */


    protected $table = 'restaurant_cuisine';

    protected $appends = ['cuisine_name'];

    public $timestamps =false; 



    // Join with cuisine table

    public function cuisine()
    {
        return $this->belongsTo('App\Models\Cuisine','cuisine_id','id');
    }

  	public function getCuisineNameAttribute() {

  		return $this->cuisine->name;
        
  	}
  

}
