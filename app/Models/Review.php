<?php

/**
 * Review Model
 *
 * @package    GoferEats
 * @subpackage Model
 * @category   Review
 * @author     Trioangle Product Team
 * @version    1.2
 * @link       http://trioangle.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model {

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */

	protected $table = 'review';

	public $typeArray = [
		'user_menu_item' => 0,
		'user_driver' => 1,
		'user_restaurant' => 2,
		'restaurant_delivery' => 3,
		'driver_delivery' => 4,
		'driver_restaurant' => 5,
	];

	protected $appends = ['restaurant_rating_count','user_to_driver_rating', 'restaurant_rating', 'average_rating', 'star_rating'];

	/**
	 * To filter based on type text
	 */
	public function scopeTypeText($query, $type = "user_menu_item") {
		$type_value = $this->typeArray[$type];
		return $query->type($type_value);
	}

	/**
	 * @param  [type]
	 * @param  integer
	 * @return [type]
	 */
	public function scopeType($query, $type = 0) {
		return $query->where('type_id', $type);
	}

	public function review_issue($query) {
		return $this->hasMany('App\Models\ReviewIssue', 'review_id', 'id');
	}

	// Join with OrderItemModifier table
	public function issue() {
		return $this->hasMany('App\Models\ReviewIssue', 'review_id', 'id');
	}

	// Join with OrderItemModifier table
	public function get_issue() {
		return $this->hasMany('App\Models\ReviewIssue', 'review_id', 'id');
	}

	/**
	 * User to driver rating
	 */
	public function getUserToDriverRatingAttribute() {

		$review = Review::where('reviewee_id', $this->id)->where('type', $this->typeArray['user_driver'])->get();
		if ($review) {
			$is_thumbs = $review->sum('is_thumbs');
			$count = $review->count();
			if ($is_thumbs != 0) {
				return (string) round($is_thumbs * 5 / $count);
			} else {
				return (string) 0;
			}

		} else {

			return (string) 0;
		}
	}

	/**
	 * User to Restaurant rating
	 */
	public function getRestaurantRatingAttribute() {
		$review = Review::where('reviewee_id', $this->reviewee_id)->where('type', $this->typeArray['user_restaurant'])->get();
		if ($review) {
			$rating = $review->sum('rating');
			$count = $review->count();
			if ($rating != 0) {
				return number_format(($rating / $count), 1);
			} else {
				return (string) 0;
			}

		} else {

			return (string) 0;
		}
	}

	/**
	 * User to Restaurant rating count
	 * restaurant_rating_count
	 */
	public function getRestaurantRatingCountAttribute() {
		$review = Review::where('reviewee_id', $this->reviewee_id)->where('type', $this->typeArray['user_restaurant'])->get();
		if ($review) 
			return  $review->count();
		else
			return  0;
	}

	/**
	 * User to Average Restaurant rating
	 */
	public function getAverageRatingAttribute() {
		$review = Review::where('reviewee_id', $this->reviewee_id)->where('type', $this->typeArray['user_restaurant'])->get();
		if ($review) {
			return (string) $review->count();

		} else {

			return (string) 0;
		}
	}

	/**
	 * User to Restaurant star individual order
	 */
	public function getStarRatingAttribute() {

		$review = Review::where('order_id', $this->order_id)->where('reviewee_id', $this->reviewee_id)->where('type', $this->typeArray['user_restaurant'])->first();

		if ($review) {
			return (string) $review->rating;
		} else {
			return '0.0';
		}

	}

	/**
	 * User to Restaurant star individual order
	 */
	public function getUserAtleastAttribute() {

		$review = Review::where('order_id', $this->order_id)->whereIn('type', [$this->typeArray['user_restaurant'], $this->typeArray['user_menu_item'], $this->typeArray['user_driver']])->get();

		if (count($review) > 0) {
			return 1;
		} else {
			return 0;
		}

	}

}
