<?php

namespace Modules\Product\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\ApiController;
use Modules\Insurance\Models\InsuranceExtraFee;
use Modules\Insurance\Models\InsurancePriceAttribute;
use Modules\Insurance\Models\InsurancePriceType;
use Modules\Insurance\Models\InsuranceType;
use Modules\Product\Http\Requests\ApiDetailProductRequest;
use Modules\Product\Http\Requests\ApiProductListRequest;
use Modules\Product\Libraries\ProductPriceHelper;
use Modules\Product\Models\Category;
use Modules\Product\Models\CategoryAttribute;
use Modules\Product\Models\CategoryClass;
use Modules\Product\Models\Product;
use Modules\Product\Models\ProductInsuranceType;
use Illuminate\Support\Facades\View;

class ProductController extends ApiController
{
    /**
     * @param ApiProductListRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getList(ApiProductListRequest $request)
    {
        // Find package by filter data
        $insuranceTypeId = $request->insurance_type_id;
        $filterData = $request->filter_data;
        $filterData = json_decode($filterData);
        $filterData = (array) $filterData;
        if (!isset($filterData['category']) || empty($filterData['category'])) {
            $category = Category::getByInsurance($insuranceTypeId);
            $filterData['category'] = $category->id;
        }
        // Check child categories
        $childCategories = Category::getListChild($filterData['category']);

        // Get category multi level
        $categoryIds = [(int)$filterData['category']];
        if ($childCategories) {
            foreach ($childCategories as $cate) {
                $categoryIds[] = $cate->id;
            }
        }

        // Get list classes
        $classes = CategoryClass::getListClassByCategory($filterData['category']);

        // Get compare attribute by category
        $compareAttributes = CategoryAttribute::getListCompareAttributes($filterData['category']);

        if ($compareAttributes) {
            $tmpData = [];

            // Set attribute id as array key
            foreach ($compareAttributes as $attribute) {
                $tmpData[$attribute->id] = $attribute;
            }

            $compareAttributes = $tmpData;
        }

        $filter = [
            'category_ids' => $categoryIds
        ];

        if (isset($request->feature)) {
            $filter['feature'] = $request->feature;
        }

        if (isset($request->sponsor)) {
            $filter['sponsor'] = $request->sponsor;
        }

        if (isset($request->is_agency)) {
            $filter['is_agency'] = $request->is_agency;
        }

        if (isset($request->category_class_id)) {
            $filter['category_class_id'] = $request->category_class_id;
        }

        // Get 5 product with current filter info
        $products = Product::getProducts($filter, true);

        // Reformat list product attribute, group product by category_class
        $tmpData = [];
        $productIds = [];

        foreach ($products as $product) {
            if (isset($product['attributes']) && !empty($product['attributes'])) {
                $tmp = [];
                foreach ($product['attributes'] as $attribute) {
                    if (isset($compareAttributes[$attribute['attribute_id']])) {
                        $tmp[$compareAttributes[$attribute['attribute_id']]->name] = $attribute['attribute_data'];
                    }
                }

                $product['attributes'] = $tmp;
            }

            if (!isset($tmpData[$product['category_class_id']])) {
                $tmpData[$product['category_class_id']] = [];
            }

            $tmpData[$product['category_class_id']][] = $product;
            $productIds[] = $product['id'];
        }

        $products = $tmpData;

        // Get prices
        $result = ProductPriceHelper::getProductsPrices($productIds, $insuranceTypeId, $filterData);

        // Order products by price asc
        if (isset($result['prices']) && !empty($result['prices'])) {
            $prices = $result['prices'];
            foreach ($products as $classId => $listProducts) {
                foreach ($listProducts as $key => $product)
                if (isset($prices[$product['id']])) {
                    $products[$classId][$key]['price'] = $prices[$product['id']];
                }
            }
        }

        return $this->successResponse([
            'products'          => $products,
            'classes'           => $classes,
            'compare_attributes' => $compareAttributes,
            'filter_conditions' => isset($result['conditions']) ? $result['conditions'] : new \stdClass()
        ], '');
    }

    /**
     * Get list all product category
     */
    public function getListCategories()
    {
        $categories = Category::with('children')->whereNull('parent_id')->get();

        return $this->successResponse([
            'categories' => $categories
        ]);
    }

    /**
     * Get product info
     * @param ApiDetailProductRequest $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDetail(ApiDetailProductRequest $request, $id)
    {
        $product = Product::getProduct($id);

        return $this->successResponse(['product' => $product]);
    }

    /**
     * Get product price with filter data
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProductPrice(Request $request)
    {
        // Get product insurance type id
        $productInsurance = ProductInsuranceType::getByProduct($request->product_id);
        $filterData = $request->filter_data;
        $selectedPriceTypes = $request->selected_price_type;
        $beneficiaries = $request->beneficiaries;

        if (!empty($beneficiaries)) {
            $beneficiaries = json_decode($beneficiaries, true);
        } else {
            $beneficiaries = [];
        }

        if (isset($productInsurance->insurance_type_id) && !empty($productInsurance->insurance_type_id)) {
            // Get insurance VAT value
            $insurance = InsuranceType::getDetail($productInsurance->insurance_type_id);

            $prices = ProductPriceHelper::getProductsPrices(
                [$request->product_id],
                $productInsurance->insurance_type_id,
                $filterData,
                !empty($selectedPriceTypes) ? explode(',', $selectedPriceTypes) : []
                );

            $productPrice = isset($prices['prices'][$request->product_id]) ? $prices['prices'][$request->product_id] : 0;

            if (!empty($productPrice)) {
                // Format return data
                $data = [
                    'product_code' => !empty($productPrice['product_code']) ? $productPrice['product_code'] : '',
                    'product_price' => !empty($productPrice['price']) ? $productPrice['price'] : 0,
                    'insurance_fee_no_tax' => !empty($productPrice['price']) ? ($productPrice['price'] * count($beneficiaries)) : 0,
                    'tax' => 0,
                    'price_with_tax' => 0,
                    'final_price' => 0,
                    'gift_code' => ''
                ];

                // Get VAT price, discount price
                if (isset($insurance->vat) && !empty($insurance->vat)) {
                    $data['tax'] = $insurance->vat;
                }

                $data['price_with_tax'] = $data['product_price'] + ($data['tax'] * $data['product_price'] / 100);
                $data['final_price'] = $data['price_with_tax'] * count($beneficiaries);

                return $this->successResponse(['prices' => $data]);
            } else {
                return $this->errorResponse(new \stdClass(), trans('Không tìm thấy mức giá phù hợp'), 99);
            }
        } else {
            return $this->errorResponse([], trans('product::api.product_insuranct_type_not_found'), 99);
        }
    }

    /**
     * Get product price and code by product id and filter data
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProductPriceData(Request $request)
    {
        $params = $request->input();
        if (isset($params['product_id']) && isset($params['insurance_type_id']) && isset($params['age']) && $params['insurance_type_id'] == 22 && $params['company_id'] == 80) {
            if ($params['age'] < 16 || $params['age'] > 60 ) {
                return response()->json(['success' => false, 'message' => 'Độ tuổi của người hưởng bảo hiểm nằm trong khoảng 16-60']);
            }
            $price_id = DB::table('mp_product_price_conditions')->join('mp_product_prices', 'mp_product_prices.id', '=', 'mp_product_price_conditions.price_id')
                ->where('product_id', $params['product_id'])
                ->where('attr_min_value', '<=', $params['age'])
                ->where('attr_max_value', '>=', $params['age'])
                ->first()->price_id;
            $product = Product::where('mp_products.id', $params['product_id'])
                ->select('mp_products.id', 'mp_products.code', 'mp_product_prices.price_detail')
                ->join('mp_product_prices', 'mp_product_prices.product_id', '=', 'mp_products.id')
                ->where('mp_product_prices.id', $price_id)
                ->first();
            if (empty($product)) {
                return response()->json(['success' => false, 'message' => 'Không tìm thấy cho sản phẩm']);
            } else {
                $return_data = [
                    'success' => true,
                    'message' => ''
                ];
                $return_data['product_code'] = $product->code;
                $product_price = 0;
                foreach (json_decode($product->price_detail) as $key => $P_price) {
                    $product_price += (int)$P_price;
                }
                if ($product_price == 0) {
                    return response()->json(['success' => false, 'message' => 'Không tìm thấy giá cho sản phẩm']);
                }
                $return_data['product_price'] = $product_price;
                $return_data['product_price_str'] = number_format($product_price, 0);

                $insuranceType = InsuranceType::getDetail($params['insurance_type_id']);
                if ($insuranceType->vat > 0) {
                    $return_data['product_tax'] = $insuranceType->vat;
                    $return_data['product_tax_amount'] = $product_price * (int)$insuranceType->vat / 100;
                    $price_with_tax = (float)$tax_amount + (float)$product_price;
                    $return_data['product_price_with_tax'] = $price_with_tax;
                    $return_data['product_price_with_tax_str'] = number_format($price_with_tax);
                } else {
                    $return_data['product_tax'] = 0;
                    $return_data['product_tax_amount'] = 0;
                    $return_data['product_price_with_tax'] = 0;
                    $return_data['product_price_with_tax_str'] = 0;
                }
                return response()->json($return_data);
            }
        }
        if(isset($params['product_id']) && isset($params['insurance_type_id']) && $params['insurance_type_id'] == 22) {
            $product = Product::where('mp_products.id', $params['product_id'])
                ->select('mp_products.id', 'mp_products.code', 'mp_product_prices.price_detail')
                ->join('mp_product_prices', 'mp_product_prices.product_id', '=', 'mp_products.id')
                ->first();
            if(empty($product)) {
                return response()->json(['success' => false, 'message' => 'Không tìm thấy cho sản phẩm']);
            } else {
                $return_data = [
                    'success' => true,
                    'message' => ''
                ];
                $return_data['product_code'] = $product->code;
                $product_price = 0;
                foreach(json_decode($product->price_detail) as $key => $P_price) {
                    $product_price += (int) $P_price;
                }
                if($product_price == 0) {
                    return response()->json(['success' => false, 'message' => 'Không tìm thấy giá cho sản phẩm']);
                }
                $return_data['product_price'] = $product_price;
                $return_data['product_price_str'] = number_format($product_price, 0);

                $insuranceType = InsuranceType::getDetail($params['insurance_type_id']);
                if ($insuranceType->vat > 0) {
                    $return_data['product_tax'] = $insuranceType->vat;
                    $return_data['product_tax_amount'] = $product_price * (int)$insuranceType->vat / 100;
                    $price_with_tax = (float)$tax_amount + (float)$product_price;
                    $return_data['product_price_with_tax'] = $price_with_tax;
                    $return_data['product_price_with_tax_str'] = number_format($price_with_tax);
                } else {
                    $return_data['product_tax'] = 0;
                    $return_data['product_tax_amount'] = 0;
                    $return_data['product_price_with_tax'] = 0;
                    $return_data['product_price_with_tax_str'] = 0;
                }
                return response()->json($return_data);
            }
        }

        if(isset($params['product_id']) && isset($params['insurance_type_id']) && $params['insurance_type_id'] == 27) {
            $product = Product::where('mp_products.id', $params['product_id'])
                ->select('mp_products.id', 'mp_products.code', 'mp_product_prices.price_detail')
                ->join('mp_product_prices', 'mp_product_prices.product_id', '=', 'mp_products.id')
                ->first();
            if(empty($product)) {
                return response()->json(['success' => false, 'message' => 'Không tìm thấy cho sản phẩm']);
            } else {
                $return_data = [
                    'success' => true,
                    'message' => ''
                ];
                $return_data['product_code'] = $product->code;
                $product_price = 0;
                foreach(json_decode($product->price_detail) as $key => $P_price) {
                    $product_price += (int) $P_price;
                }
                if($product_price == 0) {
                    return response()->json(['success' => false, 'message' => 'Không tìm thấy giá cho sản phẩm']);
                }
                $return_data['product_price'] = $product_price;
                $return_data['product_price_str'] = number_format($product_price, 0);

                $insuranceType = InsuranceType::getDetail($params['insurance_type_id']);
                if ($insuranceType->vat > 0) {
                    $return_data['product_tax'] = $insuranceType->vat;
                    $return_data['product_tax_amount'] = $product_price * (int)$insuranceType->vat / 100;
                    $price_with_tax = (float)$tax_amount + (float)$product_price;
                    $return_data['product_price_with_tax'] = $price_with_tax;
                    $return_data['product_price_with_tax_str'] = number_format($price_with_tax);
                } else {
                    $return_data['product_tax'] = 0;
                    $return_data['product_tax_amount'] = 0;
                    $return_data['product_price_with_tax'] = 0;
                    $return_data['product_price_with_tax_str'] = 0;
                }
                return response()->json($return_data);
            }
        }
        if (isset($params['product_id']) && isset($params['insurance_type_id']) && isset($params['filter_data'])) {
            $priceType = isset($request->price_type) ? $request->price_type : [];

            if (!empty($priceType) && !is_array($priceType)) {
                $priceType = explode(',', $priceType);
            }

            $productPrice = ProductPriceHelper::getProductPrice($params['product_id'], $params['insurance_type_id'], $params['filter_data'], $priceType);
            if (isset($productPrice['prices']) && !empty($productPrice['prices'])) {
                return response()->json([
                    'success' => true,
                    'message' => '',
                    'product_code' => isset($productPrice['prices']['product_code']) ? $productPrice['prices']['product_code'] : '',
                    'product_price' => isset($productPrice['prices']['price']) ? $productPrice['prices']['price'] : 0,
                    'product_price_str' => isset($productPrice['prices']['price']) ? number_format($productPrice['prices']['price'], 0) : 0,
                    'product_tax' => isset($productPrice['prices']['tax']) ? $productPrice['prices']['tax'] : 0,
                    'product_tax_amount' => isset($productPrice['prices']['tax_amount']) ? $productPrice['prices']['tax_amount'] : 0,
                    'product_price_with_tax' => isset($productPrice['prices']['price_with_tax']) ? $productPrice['prices']['price_with_tax'] : 0,
                    'product_price_with_tax_str' => isset($productPrice['prices']['price_with_tax']) ? number_format($productPrice['prices']['price_with_tax']) : 0
                ]);
            } else {
                return response()->json(['success' => false, 'message' => 'Không tìm thấy giá cho sản phẩm']);
            }
        } else {
            return response()->json(['success' => false, 'message' => 'Thiếu dữ liệu gửi lên.']);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getExtraFeePrice(Request $request)
    {
        $params = $request->input();
        if (isset($params['extra_fee_id']) && isset($params['insurance_type_id']) && isset($params['filter_data']) && isset($params['product_id'])) {
            $price = ProductPriceHelper::getExtraFeePrice($params['extra_fee_id'], $params['filter_data'], $params['extra_fee_attributes'], $params['product_id']);
            return response()->json(['success' => true, 'message' => '', 'price' => $price['price'], 'price_str' => $price['price_str'], 'conditions' => $price['conditions']]);
        } else {
            return response()->json(['success' => false, 'message' => 'Thiếu dữ liệu gửi lên.']);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getExtraProduct(Request $request)
    {
        $params = $request->input();
        if (isset($params['insurance_type_id']) && $params['insurance_type_id'] > 0) {
            // Get extra products for insurance type only
            $products = Product::getListProduct([
                'extra_for_insurance_type' => $params['insurance_type_id'],
                'extra_for_product'        => 0
            ], false);

            $insuranceIds = [];
            $extraFilterData = [];
            $extraInsuranceTypes = [];
            $extraPriceTypes = [];
            $productPriceAttributes = [];
            $selectedPriceTypes = isset($request->selected_price_types) ? explode(',', $request->selected_price_types) : [];
            $filterData = isset($request->filter_data) ? $request->filter_data : [];

            if (!empty($filterData) && !is_array($filterData)) {
                $filterData = json_decode($filterData, true);
            }

            if (isset($request->product_id) && !empty($request->product_id)) {
                // Get extra product for product
                $extraProducts = Product::getListProduct([
                    'extra_for_product' => $request->product_id
                ], false);

                // Merge two list product
                if ($extraProducts->count()) {
                    $products = $products->toBase()->merge($extraProducts);
                }
            } else {
                // Get extra product for product filter form
                $extraProducts = Product::getListProduct([
                    'extra_for_insurance_type' => $params['insurance_type_id']
                ], false);
            }

            if ($extraProducts->count()) {
                // Get insurance type ids for filter form
                foreach ($extraProducts as $product) {
                    if ($product->insurance_type->insurance_type_id != $params['insurance_type_id']) {
                        if (!in_array($product->insurance_type->insurance_type_id, $insuranceIds)) {
                            $insuranceIds[] = $product->insurance_type->insurance_type_id;
                        }
                    }
                }
            }

            if (!empty($products)) {
                // Check product price attribute
                foreach ($products as $product) {
                    if ($product->insurance_type->insurance_type_id != $params['insurance_type_id']) {
                        // Get product price attribute
                        $priceAttributes = InsurancePriceAttribute::getListWithKeyCodeByTypeId($product->insurance_type->insurance_type_id);
                        if ($priceAttributes) {
                            $productPriceAttributes[$product->id] = $priceAttributes;
                        }
                    }
                }

                // Get list price type
                if (!empty($insuranceIds)) {
                    $priceTypes = InsurancePriceType::getListPriceTypesByTypeIds($insuranceIds);
                    // Group by type
                    if ($priceTypes) {
                        foreach ($priceTypes as $priceType) {
                            if (!isset($extraPriceTypes[$priceType->insurance_type_id])) {
                                $extraPriceTypes[$priceType->insurance_type_id] = [];
                            }

                            $extraPriceTypes[$priceType->insurance_type_id][] = $priceType;
                        }
                    }

                    // Get filter data
                    $extraFilterData = InsurancePriceAttribute::getListWithKeyCode($insuranceIds);
                    // Group by insurance type
                    $tmpData = [];
                    foreach ($extraFilterData as $item) {
                        $tmpData[$item['type_id']][] = $item;
                    }
                    $extraFilterData = $tmpData;

                    // Get extra insurance types. Only load if missing product id
                    if ((!isset($request->product_id) || empty($request->product_id)) && $request->is_create_contract == false) {
                        $extraInsuranceTypes = InsuranceType::getByListId($insuranceIds);
                    }
                }

                // Get current selected product
                $selectedProducts = !empty($request->extra_products) ? explode(',', $request->extra_products) : [];
                $view = View::make('product::elements.extra_product',
                    compact('products', 'selectedProducts', 'productPriceAttributes', 'extraInsuranceTypes',
                        'extraFilterData', 'extraPriceTypes', 'selectedPriceTypes', 'filterData'));
                $products = $products->pluck('name', 'id');

                return response()->json([
                    'success' => true,
                    'message' => '',
                    'html'    => $view->render(),
                    'products' => $products,
                    'extra_insurance_types' => $extraInsuranceTypes,
                    'extra_price_types' => $extraPriceTypes,
                    'extra_filter_data' => $extraFilterData
                ]);
            } else {
                return response()->json(['success' => true, 'message' => 'Không tìm thấy sản phẩm phụ']);
            }
        } else {
            return response()->json(['success' => true, 'message' => 'Thiếu loại hình bảo hiểm.']);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getExtraProductForProduct(Request $request)
    {
        $params = $request->input();
        if (isset($params['product_id']) && $params['product_id'] > 0) {
            // Get extra products for product
            $products = Product::getListProduct([
                'extra_for_product' => $params['product_id']
            ], false);

            if (!empty($products)) {
                // Check product price attribute
                $productPriceAttributes = [];
                $extraPriceTypes = [];
                $insuranceIds = [];
                foreach ($products as $product) {
                    if (isset($product->insurance_type->insurance_type_id) && !in_array($product->insurance_type->insurance_type_id, $insuranceIds)) {
                        $insuranceIds[] = $product->insurance_type->insurance_type_id;
                    }
                    // Get product price attribute
                    $priceAttributes = InsurancePriceAttribute::getListWithKeyCodeByTypeId($product->insurance_type->insurance_type_id);
                    if ($priceAttributes) {
                        $productPriceAttributes[$product->id] = $priceAttributes;
                        // Get default value from product setting
                        if (isset($product->default_price_attribute_values) && !empty($product->default_price_attribute_values)) {
                            $productPriceAttributes[$product->id]['default_price_attribute_values'] = json_decode($product->default_price_attribute_values, true);
                        }
                    }
                }

                $priceTypes = InsurancePriceType::getListPriceTypesByTypeIds($insuranceIds);
                // Group by type
                if ($priceTypes) {
                    foreach ($priceTypes as $priceType) {
                        if (!isset($extraPriceTypes[$priceType->insurance_type_id])) {
                            $extraPriceTypes[$priceType->insurance_type_id] = [];
                        }

                        $extraPriceTypes[$priceType->insurance_type_id][] = $priceType;
                    }
                }

                $listFilterData = [];

                if (isset($request->filter_data)) {
                    $listFilterData = json_decode($request->filter_data, true);
                }

                // Get current selected product
                $selectedProducts = !empty($request->extra_products) ? explode(',', $request->extra_products) : [];
                $view = View::make('product::elements.extra_product', compact('products', 'selectedProducts',
                    'productPriceAttributes', 'listFilterData', 'extraPriceTypes'));
                $products = $products->pluck('name', 'id');

                return response()->json([
                    'success' => true,
                    'message' => '',
                    'html'    => $view->render(),
                    'products' => $products,
                ]);
            } else {
                return response()->json(['success' => true, 'message' => 'Không tìm thấy sản phẩm phụ']);
            }
        } else {
            return response()->json(['success' => true, 'message' => 'Thiếu loại hình bảo hiểm.']);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getExtraFee(Request $request)
    {
        $params = $request->input();
        if (isset($params['insurance_type_id']) && $params['insurance_type_id'] > 0) {
            $extraFees = InsuranceExtraFee::getListWithKeyCodeByTypeId($params['insurance_type_id']);
            if (!empty($extraFees)) {
                $prefixInput = $request->prefix_input;
                $productId = $request->product_id;
                $selectedFees = !empty($request->extra_fees) ? json_decode($request->extra_fees, true) : [];
                $selectedFeeAttributes = !empty($request->extra_fee_attributes) ? json_decode($request->extra_fee_attributes, true) : [];
                // Render html
                $view = View::make('product::elements.extra_fee', compact('extraFees', 'prefixInput', 'productId', 'selectedFees', 'selectedFeeAttributes'));

                return response()->json([
                    'success' => true,
                    'message' => '',
                    'extra_fees' => $extraFees,
                    'html' => $view->render()
                ]);
            } else {
                return response()->json(['success' => true, 'message' => 'Không tìm thấy sản phẩm phụ']);
            }
        } else {
            return response()->json(['success' => true, 'message' => 'Thiếu loại hình bảo hiểm.']);
        }
    }

    /**
     * Search product
     */
    public function searchProduct(Request $request)
    {
        $params = $request->all();
        $data = Product::searchProduct($params);
        return response()->json($data);
    }

    public function bhtnGetPrice(Request $request)
    {
        $params = $request->all();
        $BHTN_time = $params['filter_data']['BHTN_time'];
        $insurance_type_id = $params['type_id'];
        $product_id = $params['product_id'];
        $data = [];
        $i = 0;
        foreach($params['beneficiary'] as $key => $beneficiary) {
            if(empty($beneficiary['date_of_birth'])) {
                $data[$i] = [];
            } else {
                $bits = explode('/', $beneficiary['date_of_birth']);
                // $age_range = date('Y') - $bits[2];

                if(date('m') > $bits[1]){
                    $age_range = date('Y') - $bits[2];
                }elseif(date('m') == $bits[1]){
                    if(date('d') < $bits[0]){
                        $age_range = date('Y') - $bits[2] - 1;
                    }else{
                        $age_range = date('Y') - $bits[2];
                    }
                }else{
                    $age_range = date('Y') - $bits[2] - 1;
                }

                $filter_data['age_range'] = $age_range;
                $filter_data['BHTN_time'] = $BHTN_time;
                $filter_data['sex'] = $beneficiary['sex'];

                $productPrice = ProductPriceHelper::getProductPrice($product_id, $insurance_type_id, $filter_data, [], [],[]);
                if (isset($productPrice['prices']) && !empty($productPrice['prices'])) {
                    // if($beneficiary['relationship'] == 'Vợ chồng' || $beneficiary['relationship'] == 'Con') {
                    //     // vk ck con của chủ hợp đồng sẽ dc giảm 20%
                    //     $productPrice['prices']['price'] = (80 * $productPrice['prices']['price']) / 100;
                    //     $product_price_str = number_format($productPrice['prices']['price'], 0) . ' Giảm 20%';
                    // } else {
                    //     $product_price_str = number_format($productPrice['prices']['price'], 0);
                    // }
                    $data[$i] = [
                        'index_size' => $key,
                        'product_code' => isset($productPrice['prices']['product_code']) ? $productPrice['prices']['product_code'] : '',
                        'product_price' => isset($productPrice['prices']['price']) ? $productPrice['prices']['price'] : 0,
                        'product_price_str' => isset($productPrice['prices']['price']) ? number_format($productPrice['prices']['price'], 0) : 0,
                        'product_tax' => isset($productPrice['prices']['tax']) ? $productPrice['prices']['tax'] : 0,
                        'product_tax_amount' => isset($productPrice['prices']['tax_amount']) ? $productPrice['prices']['tax_amount'] : 0,
                        'product_price_with_tax' => isset($productPrice['prices']['price_with_tax']) ? $productPrice['prices']['price_with_tax'] : 0,
                        'product_price_with_tax_str' => isset($productPrice['prices']['price_with_tax']) ? number_format($productPrice['prices']['price_with_tax']) : 0
                    ];
                } else {
                    $data[$i]['message'] = 'Không tìm thấy giá cho sản phẩm';
                    $data[$i]['index_size'] = $key;
                }
            }
            $i++;
        }
        return response()->json(['success' => true, 'data' => $data]);
    }

    public function getPopupByProduct(Request $request)
    {
        if($request->product_id && $request->popup_choose){
            $prduct = Product::where('id','=',$request->product_id )->first();
            if($prduct){
                $popup_app = 'popup_app'.$request->popup_choose;
                if($prduct[$popup_app] != ''){
                    return response()->json(['success' => true, 'message' => $prduct[$popup_app]]);
                }else{
                    return response()->json(['success' => false, 'message' => 'Product ko có nội dung cần hiển thị']);
                }
            }else{
                return response()->json(['success' => false, 'message' => 'Thiếu loại hình bảo hiểm.']);
            }
        }else{
            return response()->json(['success' => false, 'message' => 'Truyền sai dữ liệu đầu vào gồm có :product_id , popup_choose ']);
        }
    }
}
