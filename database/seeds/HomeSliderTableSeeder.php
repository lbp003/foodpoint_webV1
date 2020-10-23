<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class HomeSliderTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      DB::table('home_slider')->delete();
      
      DB::table('home_slider')->insert(array(
        array('id' => '1','title' => 'Marc Canter','description' => 'Canter\'s Deli, Los Angeles','type' => '1','status' => '1'),
        array('id' => '2','title' => 'Amarit Dulyapaibul','description' => 'Lettuce Entertain You, Chicago','type' => '1','status' => '1'),
        array('id' => '3','title' => 'Nguyen Tran','description' => 'Starry Kitchen, Los Angeles','type' => '1','status' => '1'),
        array('id' => '4','title' => 'Find food you love from local restaurants and chain favorites.','description' => 'Find food you love from local restaurants and chain favorites.','type' => '0','status' => '1'),
        array('id' => '5','title' => 'Tap to place your order and pay with your gofereats account.','description' => 'Tap to place your order and pay with your Gofereats account.','type' => '0','status' => '1'),
        array('id' => '6','title' => 'Track your food in real time from the restaurant to you.','description' => 'Track your food in real time from the restaurant to you.','type' => '0','status' => '1'),
      ));   
    }
}
