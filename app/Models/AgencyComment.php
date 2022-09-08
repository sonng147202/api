<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgencyComment extends Model
{
    protected $table = 'agency_comment';
    protected $fillable = [
        'content', 'author_id', 'agency_id'
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
    public static function getLastComment($agency_id){
        return self::where('agency_id', $agency_id)->orderBy('id', 'DESC')->first();
    }
}
