<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    protected $fillable = [
        'name',
        'file_path',
        'image_url',
        'medium_url',
        'small_url',
        'image_category_id',
    ];

    public function imageCategory()
    {
    	return $this->belongsTo('App\Models\ImageCategory', 'image_category_id');
    }
}
