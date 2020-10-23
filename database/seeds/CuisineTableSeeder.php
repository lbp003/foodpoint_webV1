<?php

use Illuminate\Database\Seeder;

class CuisineTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	DB::table('cuisine')->delete();
    	
        DB::table('cuisine')->insert(array(
          array('id' => '1','name' => 'Afghan','description' => 'Afghan','status' => '1','is_top' => '1','most_popular' => 1,'created_at' => '2018-05-28 10:10:00','updated_at' => '2018-09-07 05:06:16','is_dietary' => '1'),
          array('id' => '2','name' => 'African','description' => 'African','status' => '1','is_top' => '0','most_popular' => NULL,'created_at' => '2018-05-28 10:10:00','updated_at' => '2018-09-07 05:37:43','is_dietary' => '1'),
          array('id' => '3','name' => 'American','description' => 'American','status' => '1','is_top' => '1','most_popular' => 1,'created_at' => '2018-05-28 10:10:00','updated_at' => '2018-09-07 05:38:07','is_dietary' => '1'),
          array('id' => '4','name' => 'Arabic','description' => 'Arabic','status' => '1','is_top' => '1','most_popular' => 1,'created_at' => '2018-05-28 10:10:00','updated_at' => '2018-09-07 05:38:50','is_dietary' => '1'),
          array('id' => '5','name' => 'Bagels','description' => 'Bagels','status' => '1','is_top' => '1','most_popular' => NULL,'created_at' => '2018-05-28 10:10:00','updated_at' => '2018-09-07 05:43:53','is_dietary' => '1'),
          array('id' => '6','name' => 'Balti','description' => 'Balti','status' => '1','is_top' => '1','most_popular' => 1,'created_at' => '2018-05-28 10:10:00','updated_at' => '2018-09-07 05:44:02','is_dietary' => '1'),
          array('id' => '7','name' => 'Bangladeshi','description' => 'Bangladeshi','status' => '1','is_top' => '0','most_popular' => NULL,'created_at' => '2018-05-28 10:10:00','updated_at' => '2018-09-07 05:44:11','is_dietary' => '1'),
          array('id' => '8','name' => 'BBQ','description' => 'BBQ','status' => '1','is_top' => '1','most_popular' => 1,'created_at' => '2018-05-28 10:10:00','updated_at' => '2018-09-07 05:44:18','is_dietary' => '1'),
          array('id' => '9','name' => 'Breakfast','description' => 'Breakfast','status' => '1','is_top' => '0','most_popular' => NULL,'created_at' => '2018-05-28 10:10:00','updated_at' => '2018-09-07 05:44:27','is_dietary' => '1'),
          array('id' => '10','name' => 'British','description' => 'British','status' => '1','is_top' => '0','most_popular' => NULL,'created_at' => '2018-05-28 10:10:00','updated_at' => '2018-09-07 05:44:39','is_dietary' => '1'),
          array('id' => '11','name' => 'Burgers','description' => 'Burgers','status' => '1','is_top' => '1','most_popular' => 1,'created_at' => '2018-05-28 10:10:00','updated_at' => '2018-09-07 05:45:42','is_dietary' => '1'),
          array('id' => '12','name' => 'Cakes','description' => 'Cakes','status' => '1','is_top' => '1','most_popular' => 1,'created_at' => '2018-05-28 10:10:00','updated_at' => '2018-09-07 05:45:49','is_dietary' => '1'),
          array('id' => '13','name' => 'Caribbean','description' => 'Caribbean','status' => '1','is_top' => '0','most_popular' => NULL,'created_at' => '2018-05-28 10:10:00','updated_at' => '2018-09-07 05:46:09','is_dietary' => '1'),
          array('id' => '14','name' => 'Chicken','description' => 'Chicken','status' => '1','is_top' => '1','most_popular' => 1,'created_at' => '2018-05-28 10:10:00','updated_at' => '2018-09-07 05:46:21','is_dietary' => '1'),
          array('id' => '15','name' => 'Chinese','description' => 'Chinese','status' => '1','is_top' => '1','most_popular' => 1,'created_at' => '2018-05-28 10:10:00','updated_at' => '2018-09-07 05:46:32','is_dietary' => '1'),
          array('id' => '16','name' => 'Curry','description' => 'Curry','status' => '1','is_top' => '0','most_popular' => NULL,'created_at' => '2018-05-28 10:10:00','updated_at' => '2018-09-07 05:46:42','is_dietary' => '1'),
          array('id' => '17','name' => 'Desserts','description' => 'Desserts','status' => '1','is_top' => '0','most_popular' => NULL,'created_at' => '2018-05-28 10:10:00','updated_at' => '2018-09-07 05:46:52','is_dietary' => '1'),
          array('id' => '18','name' => 'Drinks','description' => 'Drinks','status' => '1','is_top' => '0','most_popular' => NULL,'created_at' => '2018-05-28 10:10:00','updated_at' => '2018-09-07 05:47:09','is_dietary' => '1'),
          array('id' => '19','name' => 'Fish & Chips','description' => 'Fish & Chips','status' => '1','is_top' => '0','most_popular' => NULL,'created_at' => '2018-05-28 10:10:00','updated_at' => '2018-09-07 05:47:17','is_dietary' => '1'),
          array('id' => '20','name' => 'Fusion','description' => 'Fusion','status' => '1','is_top' => '0','most_popular' => 1,'created_at' => '2018-05-28 10:10:00','updated_at' => '2018-09-07 05:47:29','is_dietary' => '1'),
          array('id' => '21','name' => 'Gourmet','description' => 'Gourmet','status' => '1','is_top' => '0','most_popular' => NULL,'created_at' => '2018-05-28 10:10:00','updated_at' => '2018-09-07 05:48:25','is_dietary' => '1'),
          array('id' => '22','name' => 'Gourmet Burgers','description' => 'Gourmet Burgers','status' => '1','is_top' => '0','most_popular' => NULL,'created_at' => '2018-05-28 10:10:00','updated_at' => '2018-09-07 05:48:36','is_dietary' => '1'),
          array('id' => '23','name' => 'Grill','description' => 'Grill','status' => '1','is_top' => '0','most_popular' => 1,'created_at' => '2018-05-28 10:10:00','updated_at' => '2018-09-07 05:48:46','is_dietary' => '1'),
          array('id' => '24','name' => 'Ice Cream','description' => 'Ice Cream','status' => '1','is_top' => '0','most_popular' => NULL,'created_at' => '2018-05-28 10:10:00','updated_at' => '2018-09-07 05:49:01','is_dietary' => '1'),
          array('id' => '25','name' => 'Indian','description' => 'Indian','status' => '1','is_top' => '1','most_popular' => 1,'created_at' => '2018-05-28 10:10:00','updated_at' => '2018-09-07 05:49:19','is_dietary' => '1'),
          array('id' => '26','name' => 'Indonesian','description' => 'Indonesian','status' => '1','is_top' => '0','most_popular' => NULL,'created_at' => '2018-05-28 10:10:00','updated_at' => '2018-09-07 05:49:27','is_dietary' => '1'),
          array('id' => '27','name' => 'Iranian','description' => 'Iranian','status' => '1','is_top' => '0','most_popular' => NULL,'created_at' => '2018-05-28 10:10:00','updated_at' => '2018-09-07 05:49:42','is_dietary' => '1'),
          array('id' => '28','name' => 'Italian','description' => 'Italian','status' => '1','is_top' => '0','most_popular' => 1,'created_at' => '2018-05-28 10:10:00','updated_at' => '2018-09-07 05:49:53','is_dietary' => '1'),
          array('id' => '29','name' => 'Jamaican','description' => 'Jamaican','status' => '1','is_top' => '1','most_popular' => NULL,'created_at' => '2018-05-28 10:10:00','updated_at' => '2018-09-07 05:50:04','is_dietary' => '1'),
          array('id' => '30','name' => 'Japanese','description' => 'Japanese','status' => '1','is_top' => '0','most_popular' => NULL,'created_at' => '2018-05-28 10:10:00','updated_at' => '2018-09-07 05:50:22','is_dietary' => '1'),
          array('id' => '31','name' => 'Kebab','description' => 'Kebab','status' => '1','is_top' => '1','most_popular' => NULL,'created_at' => '2018-05-28 10:10:00','updated_at' => NULL,'is_dietary' => '0'),
          array('id' => '32','name' => 'Korean','description' => 'Korean','status' => '1','is_top' => '0','most_popular' => 1,'created_at' => '2018-05-28 10:10:00','updated_at' => NULL,'is_dietary' => '0'),
          array('id' => '33','name' => 'Kosher','description' => 'Kosher','status' => '1','is_top' => '0','most_popular' => NULL,'created_at' => '2018-05-28 10:10:00','updated_at' => NULL,'is_dietary' => '0'),
          array('id' => '34','name' => 'Lebanese','description' => 'Lebanese','status' => '1','is_top' => '0','most_popular' => NULL,'created_at' => '2018-05-28 10:10:00','updated_at' => NULL,'is_dietary' => '0'),
          array('id' => '35','name' => 'Lunch','description' => 'Lunch','status' => '1','is_top' => '0','most_popular' => 1,'created_at' => '2018-05-28 10:10:00','updated_at' => NULL,'is_dietary' => '0'),
          array('id' => '36','name' => 'Mediterranean','description' => 'Mediterranean','status' => '1','is_top' => '0','most_popular' => NULL,'created_at' => '2018-05-28 10:10:00','updated_at' => NULL,'is_dietary' => '0'),
          array('id' => '37','name' => 'Middle Eastern','description' => 'Middle Eastern','status' => '1','is_top' => '0','most_popular' => NULL,'created_at' => '2018-05-28 10:10:00','updated_at' => NULL,'is_dietary' => '0'),
          array('id' => '38','name' => 'Milkshakes','description' => 'Milkshakes','status' => '1','is_top' => '0','most_popular' => NULL,'created_at' => '2018-05-28 10:10:00','updated_at' => NULL,'is_dietary' => '0'),
          array('id' => '39','name' => 'Nigerian','description' => 'Nigerian','status' => '1','is_top' => '0','most_popular' => NULL,'created_at' => '2018-05-28 10:10:00','updated_at' => NULL,'is_dietary' => '0'),
          array('id' => '40','name' => 'Noodles','description' => 'Noodles','status' => '1','is_top' => '1','most_popular' => NULL,'created_at' => '2018-05-28 10:10:00','updated_at' => NULL,'is_dietary' => '0'),
          array('id' => '41','name' => 'Oriental','description' => 'Oriental','status' => '1','is_top' => '0','most_popular' => NULL,'created_at' => '2018-05-28 10:10:00','updated_at' => NULL,'is_dietary' => '0'),
          array('id' => '42','name' => 'Pakistani','description' => 'Pakistani','status' => '1','is_top' => '0','most_popular' => NULL,'created_at' => '2018-05-28 10:10:00','updated_at' => NULL,'is_dietary' => '0'),
          array('id' => '43','name' => 'Peri Peri','description' => 'Peri Peri','status' => '1','is_top' => '1','most_popular' => NULL,'created_at' => '2018-05-28 10:10:00','updated_at' => NULL,'is_dietary' => '0'),
          array('id' => '44','name' => 'Persian','description' => 'Persian','status' => '1','is_top' => '1','most_popular' => NULL,'created_at' => '2018-05-28 10:10:00','updated_at' => NULL,'is_dietary' => '0'),
          array('id' => '45','name' => 'Pizza','description' => 'Pizza','status' => '1','is_top' => '1','most_popular' => 1,'created_at' => '2018-05-28 10:10:00','updated_at' => NULL,'is_dietary' => '0'),
          array('id' => '46','name' => 'Portuguese','description' => 'Portuguese','status' => '1','is_top' => '0','most_popular' => NULL,'created_at' => '2018-05-28 10:10:00','updated_at' => NULL,'is_dietary' => '0'),
          array('id' => '47','name' => 'South Indian','description' => 'South Indian','status' => '1','is_top' => '0','most_popular' => NULL,'created_at' => '2018-05-28 10:10:00','updated_at' => NULL,'is_dietary' => '0'),
          array('id' => '48','name' => 'Steak','description' => 'Steak','status' => '1','is_top' => '0','most_popular' => NULL,'created_at' => '2018-05-28 10:10:00','updated_at' => NULL,'is_dietary' => '0'),
          array('id' => '49','name' => 'Sushi','description' => 'Sushi','status' => '1','is_top' => '0','most_popular' => NULL,'created_at' => '2018-05-28 10:10:00','updated_at' => NULL,'is_dietary' => '0'),
          array('id' => '50','name' => 'Thai','description' => 'Thai','status' => '1','is_top' => '1','most_popular' => NULL,'created_at' => '2018-05-28 10:10:00','updated_at' => NULL,'is_dietary' => '0'),
          array('id' => '51','name' => 'Turkish','description' => 'Turkish','status' => '1','is_top' => '0','most_popular' => NULL,'created_at' => '2018-05-28 10:10:00','updated_at' => NULL,'is_dietary' => '0'),
          array('id' => '52','name' => 'Vegan','description' => 'Vegan','status' => '1','is_top' => '0','most_popular' => NULL,'created_at' => '2018-05-28 10:10:00','updated_at' => NULL,'is_dietary' => '0'),
          array('id' => '53','name' => 'Vegetarian','description' => 'Vegetarian','status' => '1','is_top' => '1','most_popular' => 1,'created_at' => '2018-05-28 10:10:00','updated_at' => NULL,'is_dietary' => '0'),
          array('id' => '54','name' => 'Vietnamese','description' => 'Vietnamese','status' => '1','is_top' => '0','most_popular' => NULL,'created_at' => '2018-05-28 10:10:00','updated_at' => NULL,'is_dietary' => '0')
        ));
    }
}
