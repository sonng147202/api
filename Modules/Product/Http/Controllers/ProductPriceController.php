<?php

namespace Modules\Product\Http\Controllers;

use DB;
use Modules\Insurance\Models\InsurancePriceAttribute;
use Modules\Insurance\Models\InsurancePriceType;
use Validator;
use League\Flysystem\Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Modules\Product\Models\Company;
use Modules\Product\Models\Product;
use Modules\Product\Models\ProductPrice;
use Modules\Product\Models\ProductPriceCondition;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Redirect;

class ProductPriceController extends Controller
{
    public $operators = [
        'equal'   => 'Bằng',
        'greater' => 'Lớn hơn',
        'less'    => 'Nhỏ hơn',
        'between' => 'Trong khoảng'
    ];

    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index($productId)
    {
        $productPrices = ProductPrice::where('product_id', $productId)
            ->with('product')->with('productPriceCondition')->paginate(15);

        // Get product info
        $product = Product::where('id', $productId)->with('product_categories')->with('product_categories.category')->first();

        // Get list insurance
        if (isset($product->insurance_type->insurance_type_id) && !empty($product->insurance_type->insurance_type_id)) {
            $insuranceTypeIds = [$product->insurance_type->insurance_type_id];
        } else {
            foreach ($product->product_categories as $productCategory) {
                $insuranceTypeIds[] = $productCategory->category->insurance_type_id;
            }
        }

        // List price types
        // Get list price type by condition only
        $priceTypes = InsurancePriceType::getListPriceTypesByTypeIds($insuranceTypeIds);

        // Get list price type by product
        $productPriceTypes = InsurancePriceType::getListPriceTypesByTypeIds($insuranceTypeIds, InsurancePriceType::USE_TYPE_BY_PRODUCT);

        // Get list price attributes
        $priceAttributes = InsurancePriceAttribute::getListWithKeyCode($insuranceTypeIds);
        // Get price attribute value
        foreach ($priceAttributes as $key => $attribute) {
            $priceAttributes[$key]['values'] = [];
            if ($attribute['data_type'] == 'select' && !empty($attribute['default_value'])) {
                $options = explode(';', trim($attribute['default_value']));
                foreach ($options as $item) {
                    $item = explode(':', $item);
                    $priceAttributes[$key]['values'][$item[0]] = $item[1];
                }
            }
        }

        return view('product::prices/index', [
            'productPrices'     => $productPrices,
            'productId'         => $productId,
            'product'           => $product,
            'priceTypes'        => $priceTypes,
            'priceAttributes'   => $priceAttributes,
            'productPriceTypes' => $productPriceTypes
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param $productId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create($productId)
    {
        // Get list price type, price condition by insurance type
        $product = Product::where('id', $productId)->with('product_categories')->with('product_categories.category')->first();

        // Get list insurance
        if (isset($product->insurance_type->insurance_type_id) && !empty($product->insurance_type->insurance_type_id)) {
            $insuranceTypeIds = [$product->insurance_type->insurance_type_id];
        } else {
            foreach ($product->product_categories as $productCategory) {
                $insuranceTypeIds[] = $productCategory->category->insurance_type_id;
            }
        }

        // List price types
        $priceTypes = InsurancePriceType::getListPriceTypesByTypeIds($insuranceTypeIds);

        // Get list price attributes
        $priceAttributes = InsurancePriceAttribute::getListWithKeyCode($insuranceTypeIds);

        //Unit price type
        $unitPriceType = !empty($product->unit_price_type_health_insurance) ? \GuzzleHttp\json_decode($product->unit_price_type_health_insurance, true) : [];


        return view('product::prices/create', [
            "productId"       => $productId,
            'product'         => $product,
            'priceTypes'      => $priceTypes,
            'priceConditions' => $priceAttributes,
            'operators'       => $this->operators,
            'insuranceTypeIds' => $insuranceTypeIds,
            'unitPriceType'   => $unitPriceType,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request, $productId)
    {
        $params = $request->all();

        /*$validatorArray = [
            'price' => 'required|numeric'
        ];

        $validator = Validator::make($request->all(), $validatorArray);
        if ($validator->fails()) {
            $message = $validator->errors();
            return Redirect::route('product.prices.create', $productId)->withErrors([$message->first()]);
        }*/

        DB::beginTransaction();
        try {
            $price = ProductPrice::create([
                'price'        => isset($params["price"]) ? $params["price"] : 0,
                'price_type'   => isset($params['price_type']) ? $params['price_type'] : 0,
                'price_detail' => !empty($params['price_detail']) ? json_encode($params['price_detail']) : '',
                'product_id'   => $productId,
                'product_code' => $params['product_code']
            ]);

            //Save price Conditions
            $price->saveConditions($params["attr_key"], $params["attr_value"], $params["attr_operator"], $params["attr_min_value"], $params["attr_max_value"]);

            //Save unit price type of product
            $price->saveUnitPriceType($params, $productId);

            DB::commit();
            return Redirect::route('product.prices.index', $productId);
        } catch (\Exception $e) {
            DB::rollback();
            Log::alert($e);
            return Redirect::route('product.prices.index', $productId)->withErrors(["Có lỗi khi lưu bản ghi!"]);
        }
    }

    /**
     * Show the form for editing a new resource.
     *
     * @param $productId
     * @param $priceId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($productId, $priceId)
    {
        $productPrice = ProductPrice::where('id', $priceId)->with('productPriceCondition')->first();

        // Get list price type, price condition by insurance type
        $product = Product::where('id', $productId)->with('product_categories')->with('product_categories.category')->first();

        // Get list insurance
        $insuranceTypeIds = [$product->insurance_type->insurance_type_id];

        // List price types
        $priceTypes = InsurancePriceType::getListPriceTypesByTypeIds($insuranceTypeIds);

        // Get list price attributes
        $priceAttributes = InsurancePriceAttribute::getListWithKeyCode($insuranceTypeIds);

        //Unit price type
        $unitPriceType = !empty($product->unit_price_type_health_insurance) ? \GuzzleHttp\json_decode($product->unit_price_type_health_insurance, true) : [];

        return view('product::prices/edit', [
            "productPrice"    => $productPrice,
            "productId"       => $productId,
            'priceTypes'      => $priceTypes,
            'priceConditions' => $priceAttributes,
            'operators'       => $this->operators,
            'insuranceTypeIds' => $insuranceTypeIds,
            'unitPriceType'   => $unitPriceType
        ]);
    }

    /**
     * Update a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request, $productId, $priceId)
    {
        $params = $request->all();
        /*$validatorArray = [
            'price' => 'required|numeric'
        ];

        $validator = Validator::make($request->all(), $validatorArray);
        if ($validator->fails()) {
            $message = $validator->errors();
            return Redirect::route('product.prices.create', $productId)->withErrors([$message->first()]);
        }*/

        $price = ProductPrice::where("id", $priceId)->first();
        DB::beginTransaction();
        try {
            $price->price = isset($params["price"]) ? $params["price"] : 0;
            $price->price_detail = !empty($params['price_detail']) ? json_encode($params['price_detail']) : '';
            $price->product_code = !empty($params['product_code']) ? $params['product_code'] : '';
            $price->price_type   = !empty($params['price_type']) ? $params['price_type'] : 0;
            $price->save();

            $price->saveConditions($params["attr_key"], $params["attr_value"], $params["attr_operator"], $params["attr_min_value"], $params["attr_max_value"]);

            //Save unit price type of product
            $price->saveUnitPriceType($params, $productId);

            DB::commit();
            return Redirect::route('product.prices.index', $productId);
        } catch (\Exception $e) {
            DB::rollback();
            Log::alert($e);
            return Redirect::route('product.prices.index', $productId)->withErrors(["Có lỗi khi lưu bản ghi!"]);
        }
    }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy($productId, $priceId)
    {
        $obj = ProductPrice::where("id", $priceId)->first();
        if ($obj) {
            DB::beginTransaction();
            try {
                ProductPriceCondition::where('price_id', $priceId)->delete();
                $obj->delete();
                DB::commit();
                return Redirect::route('product.prices.index', $productId);
            } catch (\Exception $e) {
                DB::rollback();
                Log::alert($e);
                return Redirect::route('product.prices.index', $productId)->withErrors(["Có lỗi khi xóa bản ghi!"]);
            }
        } else {
            return Redirect::route('product.prices.index', $productId)->withErrors(["Bản ghi không tồn tại!"]);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPriceAttributeInputs(Request $request)
    {
        // check price attribute
        $attrCode = $request->price_attribute;
        $insuranceType = $request->type;

        $priceAttribute = InsurancePriceAttribute::where('type_id', $insuranceType)->where('code', $attrCode)
            ->first();

        // Check for product default value setting
        $productDefaultValues = [];
        if (isset($request->product_id)) {
            $productDefaultValues = Product::getDefaultPriceAttributeValues($request->product_id);
        }

        // Check data type
        switch ($priceAttribute->data_type) {
            case 'select':
                $html = '<select name="attr_value[]" class="form-control">';
                // Parse default value for options
                if (isset($productDefaultValues[$priceAttribute->code])) {
                    $options = explode(';', trim($productDefaultValues[$priceAttribute->code]));
                } else {
                    $options = explode(';', trim($priceAttribute->default_value));
                }

                foreach ($options as $item) {
                    $item = explode(':', $item);
                    $checked = false;
                    if (isset($request->value) && $request->value == $item[0]) {
                        $checked = true;
                    }

                    $html .= '<option '. ($checked ? 'selected' : '') .' value="'. $item[0] .'">' . $item[1] . '</option>';
                }

                $html .= '</select>';
                $html .= '<input type="hidden" name="attr_min_value[]"/><input type="hidden" name="attr_max_value[]"/>';

                break;
            case 'checkbox':
                $html = '<input type="checkbox" name="attr_value[]" value="1"/>';
                $html .= '<input type="hidden" name="attr_min_value[]"/><input type="hidden" name="attr_max_value[]"/>';
                break;
            default:
                $html = '<div class="value-inputs">
                                <input name="attr_value[]" type="text" class="form-control" value="' . (isset($request->value) ? $request->value : '') . '">
                            </div>
                            <div class="between-inputs">
                                <div class="row">
                                    <div class="col-md-5"><input name="attr_min_value[]" type="text" class="form-control" value="' . (isset($request->min_value) ? $request->min_value : '') . '"></div>
                                    <div class="col-md-2">Tới</div>
                                    <div class="col-md-5"><input name="attr_max_value[]" type="text" class="form-control" value="' . (isset($request->max_value) ? $request->max_value : '') . '"></div>
                                </div>
                            </div>';
                break;
        }

        return response()->json(['success' => true, 'html' => $html]);
    }
}
