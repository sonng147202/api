<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(ImageCategorySeeder::class);
        $this->call(ImageSeeder::class);
        // $this->call(VideoCategorySeeder::class);
        // $this->call(VideoSeeder::class);
    }
}
