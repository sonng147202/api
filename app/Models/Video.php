<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    protected $fillable = [
        'file_path',
        'title',
        'video_category_id',
    ];

    public function videoCategory()
    {
    	return $this->belongsTo('App\Models\VideoCategory', 'video_category_id');
    }
}
