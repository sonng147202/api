<?php


namespace Modules\Product\Models;


use Illuminate\Database\Eloquent\Model;

class SubContractProduct extends Model
{
    protected $table = 'sub_contract_products';

    protected $fillable = [
        'contract_id',
        'product_id'
    ];

    public static function createContractProduct($sub_product_id, $contract_id) {
        foreach ($sub_product_id as $id) {
            if ($id != null) {
                $sub_contract_product = SubContractProduct::create([
                    'contract_id' => $contract_id,
                    'product_id' => $id
                ]);
            }
        }
    }
}
