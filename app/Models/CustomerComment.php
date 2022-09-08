<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerComment extends Model
{
    protected $table = 'customer_comment';
    protected $fillable = [
        'content', 'author_id', 'customer_id'
    ];

    public function users()
    {
        return $this->belongsTo('Modules\Core\Models\User','author_id');
    }
    
    /**
     * Get last comment
     *
     * @param $customer_id
     * @return Model|null|static
     */
    public static function getLastComment($customer_id){
        return self::where('customer_id', $customer_id)->orderBy('id', 'DESC')->first();
    }
}
