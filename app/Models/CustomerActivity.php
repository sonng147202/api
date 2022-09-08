<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerActivity extends Model
{
    protected $guarded = [];

    const TYPE = [
        0 => 'Contract',
        1 => 'Quotation',
        2 => 'Payment',
        3 => 'ContractProvice'
    ];

    /**
     * @param $customerId
     * @param $content
     * @param $type
     * @param $typeId
     * @return $this|Model
     */
    public static function createActivity($customerId, $content, $type, $typeId)
    {
        return self::create([
            'customer_id' => $customerId,
            'type'        => $type,
            'type_id'     => $typeId,
            'detail'      => $content
        ]);
    }
}
