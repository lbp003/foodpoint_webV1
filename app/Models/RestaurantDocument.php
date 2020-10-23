<?php

/**
 * Restaurant Document Model
 *
 * @package     GoferEats
 * @subpackage  Model
 * @category    Restaurant Document
 * @author      Trioangle Product Team
 * @version     1.3
 * @link        http://trioangle.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RestaurantDocument extends Model {

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */

	protected $table = 'restaurant_document';

	protected $appends = ['document_file'];

	public $timestamps = false;

	public function file() {
		return $this->belongsTo('App\Models\File', 'document_id', 'id');
	}

	//document_file
	public function getDocumentFileAttribute() {
		if ($this->file()->first()) {
			return $this->file()->first()->restaurant_document;
		} else {
			return sample_image();
		}

	}
}
