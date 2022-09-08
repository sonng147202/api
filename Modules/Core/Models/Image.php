<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    protected $guarded = [];

    public static function createImage($image_name)
    {
        return self::create([
            'file_path'  => $image_name,
            'image_url'  => config('core.resource_url') . $image_name,
            'medium_url' => config('core.resource_url') . $image_name,
            'small_url'  => config('core.resource_url') . $image_name
        ]);
    }
}
