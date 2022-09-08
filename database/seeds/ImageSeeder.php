<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ImageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($i = 0; $i < 10; $i++) {
            DB::table('images')->insert([
                [
                    'name' => "name_image-$i",
                    'file_path' => '/images/image.png',
                    'image_url' => "image_url-$i",
                    'medium_url' => "medium_url-$i",
                    'small_url' => "small_url-$i",
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                    'image_category_id' => null
                ],
            ]);
        }
    }
}
