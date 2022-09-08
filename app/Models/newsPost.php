<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class newsPost extends Model
{
    protected $table = 'news_posts';
    public static function getListType() {
        $data = self::all();
        return $data;
    }

}