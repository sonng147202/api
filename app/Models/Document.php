<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = [
        'file_path',
        'file_name',
        'document_category_id',
    ];

    public function imageCategory()
    {
    	return $this->belongsTo('App\Models\DocumentCategory', 'document_category_id');
    }
}
