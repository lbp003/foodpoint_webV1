<?php

/**
 * FoodReceiver Model
 *
 * @package    GoferEats
 * @subpackage Model
 * @category   FoodReceiver
 * @author     Trioangle Product Team
 * @version    1.2
 * @link       http://trioangle.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Request;
use Session;

class FoodReceiver extends Model {

	public $translatedAttributes = ['name'];
	public $timestamps = false;
	use Translatable;
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */

	public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        
        if(Request::segment(1) == 'admin') {
            $this->defaultLocale = 'en';
        }
        else {
            $this->defaultLocale = Session::get('language');
        }
    }

    public function getNameLangAttribute()
    {
      $lan = Session::get('language');
      if($lan=='en')
        return $this->attributes['name'];
      else{ 
         $get = FoodReceiverTranslations::where('food_receiver_id',$this->attributes['id'])->where('locale',$lan)->first();
         if($get)
          return $get->name;
        else
          return $this->attributes['name'];
      }
    }


	protected $table = 'food_receiver';


}
