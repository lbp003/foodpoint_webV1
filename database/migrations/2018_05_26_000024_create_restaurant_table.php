<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRestaurantTable extends Migration
{
    /**
     * Schema table name to migrate
     * @var string
     */
    public $set_schema_table = 'restaurant';

    /**
     * Run the migrations.
     * @table restaurant
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable($this->set_schema_table)) return;
        Schema::create($this->set_schema_table, function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('user');
            $table->string('name', 100)->nullable();
            $table->text('description')->nullable();
            $table->time('min_time')->nullable();
            $table->time('max_time')->nullable();
            $table->tinyInteger('type')->nullable();
            $table->string('currency_code', 3)->nullable();
            $table->Integer('price_rating');

            $table->string('delivery_mode', 3)->nullable();
            
            $table->Integer('recommend');

            $table->enum('is_free', ['0','1'])->default('1');
            $table->Integer('delivery_fee');

            $table->tinyInteger('status')->nullable()->default(1);
        });
        
        $statement = "ALTER TABLE `restaurant` AUTO_INCREMENT = 10001;";

        DB::unprepared($statement);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
     public function down()
     {
       Schema::dropIfExists($this->set_schema_table);
     }
}
