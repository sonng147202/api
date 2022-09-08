<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VideoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('videos')->insert([
            [
                'title' => 'video_7',
                'file_path' => 'https://youtu.be/E3w-PwlgBOo',
                'video_category_id' => '2',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'video_8',
                'file_path' => 'https://youtu.be/8V1D--1Ovgw',
                'video_category_id' => '2',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'video_9',
                'file_path' => 'https://youtu.be/RpYA_IOJjIA',
                'video_category_id' => '2',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'video_10',
                'file_path' => 'https://youtu.be/J_Lyd0x-R7E',
                'video_category_id' => '2',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'video_11',
                'file_path' => 'https://youtu.be/dMtXzl8sjpw',
                'video_category_id' => '2',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
