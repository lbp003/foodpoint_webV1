<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRestaurantDocumentTable extends Migration {
	/**
	 * Schema table name to migrate
	 * @var string
	 */
	public $set_schema_table = 'restaurant_document';

	/**
	 * Run the migrations.
	 * @table restaurant_document
	 *
	 * @return void
	 */
	public function up() {
		if (Schema::hasTable($this->set_schema_table))
			return;

		Schema::create($this->set_schema_table, function (Blueprint $table) {
			$table->increments('id');
			$table->integer('restaurant_id')->unsigned();
			// $table->foreign('restaurant_id')->references('id')->on('restaurant');
			$table->string('name', 50)->nullable();
			$table->integer('document_id')->nullable();

		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists($this->set_schema_table);
	}
}
