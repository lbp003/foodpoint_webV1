<?php

/**
 * FoodReceiver Translations Model
 *
 * @package     Makent
 * @subpackage  Model
 * @category    FoodReceiver Translations
 * @author      Trioangle Product Team
 * @version     1.5.6
 * @link        http://trioangle.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FoodReceiverTranslations extends Model
{
    public $timestamps = false;
    protected $fillable = ['name'];
    
    public function language() {
    	return $this->belongsTo('App\Models\Language','locale','value');
    }
}
