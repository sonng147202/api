<?php

namespace Modules\Product\Models;

use DB;
use Illuminate\Support\Facades\Cache;
use Modules\Product\Models\ProductPrice;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    // use SoftDeletes;

    protected $table = 'mp_products';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];
    protected $fildable = [
        'code', 'name', 'insurance_company_id','is_main_product','status','discount','calculate_pfyp'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    const STATUS_DELETED = -1;

    const IS_AGENCY = 1;
    const IS_NOT_AGENCY = 0;
    const FOR_CUSTOMER = 1;
    const FOR_NOT_CUSTOMER = 0;

    protected $dates = ['deleted_at'];
    public function level_commissions()
    {
        return $this->hasMany('Modules\Product\Models\ProductLevelCommission', 'product_id');
    }

    public function getStatusName() {
        if ($this->status == Product::STATUS_ACTIVE)
            return "Đang kích hoạt";
        elseif ($this->status == Product::STATUS_INACTIVE)
            return "Không sử dụng";
        else
            return "Đã xóa";
    }

    public static function searchByCondition($params) {
        $p = Product::with('company')->with('category_class')->where('status', '>', self::STATUS_DELETED);
        if (!empty($params["keyword"])) {
            $p = $p->where('name', 'like', '%'.$params["keyword"].'%');
        }
        if (!empty($params["company_id"])) {
            $p = $p->where('company_id', $params["company_id"]);
        }
        if (!empty($params["category_id"])) {
            $p = $p->join(DB::raw("(SELECT product_id FROM mp_product_categories WHERE category_id = ".$params["category_id"].") pc"), "mp_products.id", "=", "pc.product_id");
        }
        return $p->orderBy('created_at', 'desc')->paginate(10);
    }

    public function saveCategory($category_ids) {
        ProductCategory::where("product_id", $this->id)->delete();
        if (empty($category_ids))
            return;

        $records = [];
        foreach ($category_ids as $category_id) {
            if (empty($category_id))
                continue;
            $obj = [
                "product_id" => $this->id,
                "category_id" => $category_id,
                "created_at" => date("Y-m-d H:i:s"),
                "updated_at" => date("Y-m-d H:i:s")
            ];
            array_push($records, $obj);
        }
        ProductCategory::insert($records);
    }

    public function savePrice($prices) {
        ProductPrice::where("product_id", $this->id)->delete();
        if (empty($prices))
            return;

        $records = [];
        foreach ($prices as $price) {
            if (empty($price))
                continue;
            $obj = [
                "product_id" => $this->id,
                "price" => $price,
                "created_at" => date("Y-m-d H:i:s"),
                "updated_at" => date("Y-m-d H:i:s")
            ];
            array_push($records, $obj);
        }
        ProductPrice::insert($records);
    }

    public function saveLevelCommission($commission_levels) {
        ProductLevelCommission::where("product_id", $this->id)->delete();
        $records = [];
        foreach ($commission_levels as $key => $amount) {
            $obj = [
                "product_id" => $this->id,
                "commission_id" => $key,
                "commission_amount" => $amount,
                "created_at" => date("Y-m-d H:i:s"),
                "updated_at" => date("Y-m-d H:i:s")
            ];
            array_push($records, $obj);
        }
        ProductLevelCommission::insert($records);
    }

    // public function saveLevelProduct($commission_levels) {
    //     LevelProduct::where("product_id", $this->id)->delete();
    //     $records = [];
    //     foreach ($commission_levels as $key => $amount) {
    //         $obj = [
    //             "product_id" => $this->id,
    //             "commission_id" => $key,
    //             "commission_amount" => $amount,
    //             "created_at" => date("Y-m-d H:i:s"),
    //             "updated_at" => date("Y-m-d H:i:s")
    //         ];
    //         array_push($records, $obj);
    //     }
    //     LevelProduct::insert($records);
    // }

    public function saveCommission($commission_type, $commission_amount) {
        if ($commission_amount != '') {
            ProductCommission::create([
                "product_id" => $this->id,
                "commission_type" => $commission_type,
                "commission_amount" => $commission_amount
            ]);
        }
    }

    public function saveCommissionSubsidiary($commission_type, $commission_amount, $subsidiary_id)
    {
        if ((!empty($commission_type) && !empty($commission_amount) && !empty($subsidiary_id)) &&
            count($commission_type) == count($commission_amount) &&
            count($commission_type) == count($subsidiary_id) &&
            count($commission_amount) == count($subsidiary_id)) {
            $n = count($subsidiary_id);
            ProductCommission::where('product_id', $this->id)->where('subsidiary_id','!=', 0)->delete();
            for($i=0; $i < $n; $i++) {
                ProductCommission::create([
                    "product_id" => $this->id,
                    "commission_type" => $commission_type[$i],
                    "commission_amount" => $commission_amount[$i],
                    "subsidiary_id" => $subsidiary_id[$i]
                ]);
            }
        }
    }

    public function saveInsuranceType($insuranceTypeId)
    {
        if ($insuranceTypeId != '') {
            // Check exist data
            $productInsuranceType = ProductInsuranceType::where('product_id', $this->id)->first();

            if ($productInsuranceType) {
                ProductInsuranceType::where('id', $productInsuranceType->id)->update(['insurance_type_id' => $insuranceTypeId]);
            } else {
                ProductInsuranceType::create([
                    'product_id' => $this->id,
                    'insurance_type_id' => $insuranceTypeId
                ]);
            }
        }
    }

    /**
     * @param $commission_amount
     * @param int $commission_type
     */
    public function saveCustomerCommission($commission_amount, $commission_type = 0)
    {
        if ($commission_amount > 0) {
            ProductCustomerCommission::create([
                'product_id'        => $this->id,
                'commission_type'   => $commission_type,
                'commission_amount' => $commission_amount
            ]);
        }
    }

    /**
     * The relationship
     */
    public function company()
    {
        return $this->belongsTo('Modules\Product\Models\Company', 'company_id');
    }

    public function product_categories()
    {
        return $this->HasMany('Modules\Product\Models\ProductCategory');
    }

    public function category_class()
    {
        return $this->belongsTo('Modules\Product\Models\CategoryClass');
    }

    public function prices()
    {
        return $this->hasMany('Modules\Product\Models\ProductPrice', 'product_id');
    }

    public function attributes()
    {
        return $this->hasMany('Modules\Product\Models\ProductAttribute', 'product_id');
    }

    public function commissions()
    {
        return $this->hasMany('Modules\Product\Models\ProductCommission', 'product_id');
    }

    public function commission()
    {
        return $this->commissions()->where('subsidiary_id',0)->latest()->first();
    }

    public function insurance_type()
    {
        return $this->hasOne('Modules\Product\Models\ProductInsuranceType','product_id','id');
    }

    public function type()
    {
        return $this->belongsToMany('Modules\Product\Models\InsuranceType', 'mp_product_insurance_types', 'product_id', 'insurance_type_id');
    }

    public function customer_commissions()
    {
        return $this->hasMany('Modules\Product\Models\ProductCustomerCommission', 'product_id');
    }

    public function customer_commission()
    {
        return $this->customer_commissions()->latest()->first();
    }
    public function levels()
    {
        return $this->belongsToMany('Modules\Insurance\Models\Level')->withPivot('commission_rate', 'counterpart_commission_rate');
    }


    /**
     * @param $filter
     * @param bool $asArray
     * @return mixed
     */
    public static function getProducts($filter, $asArray = false)
    {
        if (isset($filter['category_ids'])) {
            $products = self::whereHas('product_categories', function ($query) use ($filter) {
                $query->whereIn('category_id', $filter['category_ids']);
            })
                ->where('status', '>', self::STATUS_INACTIVE)
                ->where('extra_for_insurance_type', 0);

            if (isset($filter['status'])) {
                $products->where('status', $filter['status']);
            }

            //FILTER PRODUCT FOR CUSTOMER
            if (isset($filter['is_agency'])) {
                if ($filter['is_agency'] == self::IS_NOT_AGENCY) {
                    $products->where('for_customer', self::FOR_CUSTOMER);
                }
            }

            // Check for feature and sponsor products
            if (isset($filter['feature'])) {
                $products->where('is_feature', (int)$filter['feature']);
            }

            // Check for sponsor products
            if (isset($filter['sponsor'])) {
                $products->where('is_sponsor', (int)$filter['feature']);
            }

            //check if filter by category_class_id
            if (isset($filter['category_class_id'])) {
                $products->whereIn('category_class_id', $filter['category_class_id']);
            }

            $products = $products->with('attributes')->get();

            if ($asArray) {
                $products = $products->toArray();
            }

            return $products;
        } else {
            return false;
        }
    }

    /**
     * @param $productId
     * @return mixed
     */
    public static function getProduct($productId)
    {
        $product = self::with('attributes', 'attributes.category_attribute', 'company', 'insurance_type')
            ->where('id', $productId)->first();

        // format data

        return $product;
    }

    /**
     * @param $filterData
     * @return \Illuminate\Support\Collection
     */
    public static function getListProduct($filterData, $pluck = true)
    {
        if (isset($filterData['status'])) {
            $query = self::where('status', (int)$filterData['status']);
        } else {
            $query = self::where('status', self::STATUS_ACTIVE);
        }

        $query->with('insurance_type');

        if (isset($filterData['company_id'])) {
            $query->where('company_id', (int)$filterData['company_id']);
        }

        if (isset($filterData['extra_for_insurance_type'])) {
            $query->where('extra_for_insurance_type', (int)$filterData['extra_for_insurance_type']);
        }

        if (isset($filterData['extra_for_product'])) {
            if ($filterData['extra_for_product'] !== true) {
                $query->where('extra_for_product', (int)$filterData['extra_for_product']);
            } else {
                $query->where('extra_for_product', '>', 0);
            }
        } else {
            $query->where('extra_for_product', 0);
        }

        // Check filter by insurance type
        if (isset($filterData['insurance_type_id'])) {
            $query->whereHas('insurance_type', function ($query) use ($filterData) {
                $query->where('insurance_type_id', $filterData['insurance_type_id']);
            });
        };

        if ($pluck) {
            return $query->get()->pluck('name', 'id');
        } else {
            return $query->get();
        }
    }

    /**
     * @param $productId
     * @return mixed
     */
    public static function getDefaultPriceAttributeValues($productId)
    {
        $cacheKey = 'product_default_price_attribute_values_' . $productId;

        $defaultValues = Cache::tags('product')->remember($cacheKey, config('product.default_cache_time', 60), function () use ($productId) {
            $product = self::getProduct($productId);

            if (isset($product->default_price_attribute_values) && !empty($product->default_price_attribute_values)) {
                $defaultValues = json_decode($product->default_price_attribute_values, true);
            } else {
                $defaultValues = [];
            }

            return $defaultValues;
        });

        return $defaultValues;
    }

    /**
     * @param $productIds
     * @return array
     */
    public static function getExtraProductsByProduct($productIds)
    {
        $extraProducts = self::getListExtraProductsForProduct();
        $returnData = [];
        if ($extraProducts) {
            foreach ($extraProducts as $extraProduct) {
                if (in_array($extraProduct->extra_for_product, $productIds)) {
                    if (!isset($returnData[$extraProduct->extra_for_product])) {
                        $returnData[$extraProduct->extra_for_product] = [];
                    }
                    $returnData[$extraProduct->extra_for_product][] = $extraProduct;
                }
            }
        }

        return $returnData;
    }

    /**
     * Return all product is extra-product
     */
    public static function getListExtraProductsForProduct()
    {
        $cacheKey = 'list_extra_products_for_product';

        $products = Cache::tags('product')->remember($cacheKey, config('product.default_cache_time', 60), function () {
            return self::where('extra_for_product', '>', 0)
                ->select(['id', 'name', 'code', 'company_id', 'status', 'avatar', 'extra_for_insurance_type', 'extra_for_product', 'default_price_attribute_values', 'created_at'])
                ->get();
        });

        return $products;
    }

    /**
     * Search product
     */
    public static function searchProduct($params)
    {
        isset($params['page']) ? $page = $params['page'] : $page = 1;
        isset($params['per_page']) ? $perPage = $params['per_page'] : $perPage = 10;
        $offSet = ($page * $perPage) - $perPage;
        $query = self::where('name', 'like', '%'.$params['key'].'%')->with('company:id,name');
        $count = $query->count();
        $data = $query->offset($offSet)
            ->limit($perPage)
            ->orderBy('id', 'desc')
            ->get();
        $rs = [
            'count' => $count,
            'data' => $data
        ];
        return $rs;
    }

    /**
     * Get detail
     *
     * @param $product_id
     * @return Model|null|static
     */
    public static function getDetail($product_id){
        return self::where('id', $product_id)->first();
    }


    public static function getProductPriceConditions(){
        $query = self::where('status', self::STATUS_ACTIVE);
//        $query->with('prices')->select('price_detail');
        $query->with('insurance_type');

        $query->whereHas('insurance_type', function ($query) {
            $query->where('insurance_type_id', 21);
        });
        return $query->select('name', 'id')->get();
    }

    public static function searchIndex($params){

        
        $list = Product::select(
            'mp_products.*','ic.name as company_name','ic.id as company_id'
        )
        ->leftjoin('insurance_companies as ic', 'ic.id', 'mp_products.insurance_company_id')
        ->where('mp_products.status','!=',0);

        if (!empty($params["name"])) {
            $list = $list->where('mp_products.name', 'like', '%'.$params["name"].'%');
        }
        if (!empty($params["code"])) {
            $list = $list->where('mp_products.code',$params["code"]);
        }
        if (!empty($params["is_main_product"])) {
            $list = $list->where('mp_products.is_main_product',$params["is_main_product"]);
        }
        
        if (!empty($params["company_id"])) {
            $list = $list->where('ic.id',$params["company_id"]);
        }

        $list= $list->orderBy('id','desc');

        return $list;
    }

}

