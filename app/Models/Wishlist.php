<?php

/**
 * Wishlist Model
 *
 * @package     GoferEats
 * @subpackage  Model
 * @category    Wishlist
 * @author      Trioangle Product Team
 * @version     1.3
 * @link        http://trioangle.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wishlist extends Model {

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */

	protected $table = 'wishlist';

	protected $appends = ['wishlist_count'];

	// Join with Restaurant table

	public function restaurant() {
		return $this->belongsTo('App\Models\Restaurant', 'restaurant_id', 'id');

	}

	public function getWishlistCountAttribute() {

		return Wishlist::where('restaurant_id', $this->restaurant_id)->count();

	}

}
