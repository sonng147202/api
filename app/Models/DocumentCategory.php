<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentCategory extends Model
{
    protected $table = 'document_categories';
    protected $fillable = [
    	'name',
    	'parent_id',
    ];

    public function documents()
    {
    	return $this->hasMany('App\Models\Document', 'document_category_id');
    }
}
