<?php

namespace Modules\Product\Http\Controllers;

use App\Models\ProductPriceCondition;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Modules\Insurance\Models\InsuranceAgencyCommissionLevel;
use Modules\Insurance\Models\InsuranceCompany;
use Modules\Insurance\Models\Level;
use Modules\Insurance\Models\InsuranceExtraFee;
use Modules\Insurance\Models\InsuranceFormula;
use Modules\Insurance\Models\InsurancePriceAttribute;
use Modules\Insurance\Models\InsurancePriceType;
use Modules\Insurance\Models\InsuranceType;
use Modules\Product\Libraries\ProductPriceHelper;
use Modules\Product\Models\ProductCommission;
use Modules\Product\Models\ProductPrice;
use Validator;
use League\Flysystem\Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Modules\Product\Models\Company;
use Modules\Product\Models\LevelProduct;
use Modules\Product\Models\Product;
use Modules\Product\Models\ProductAttribute;
use Modules\Product\Models\Category;
use Modules\Product\Models\CategoryClass;
use Modules\Product\Models\CategoryAttribute;
use Modules\Product\Models\Commission;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Redirect;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index(Request $request)
    {
        $params = $request->all();
        $products = Product::searchByCondition($params);

        return view('product::products/index', [
            "params" => $params,
            'companies' => Company::all(),
            "categories" => Category::getNestedListCategory(),
            "products" => $products
        ]);
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('product::products/create', [
            'companies' => Company::where('parent_id', 0)->get(),
            "categories" => Category::getNestedListCategory(),
            'insuranceTypes' => InsuranceType::getListType(),
            'levels' => Level::orderBy('level', 'asc')
                ->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $currentUser = Auth::user();
        $params = $request->all();
        //avatar
        $file = $request->file('avatar');
        $avatar = '';
        if (!empty($file)) {
            $ext = $file->getClientOriginalExtension();
            $fileName = time() . $file->getFilename() . '.' . $ext;
            // Storage::disk('product')->put($fileName, \Illuminate\Support\Facades\File::get($file));
            $file->storeAs('/public/', $fileName);
            $avatar = '/storage/' . $fileName;


        }
        //end avatar
        //sponsor image
        $sponsorImage = $request->file('sponsor_image');
        $sponsorImageFile = '';
        if (!empty($sponsorImage)) {
            // $ext = $sponsorImage->getClientOriginalExtension();
            // $fileName = time().$sponsorImage->getFilename().'.'.$ext;
            // Storage::disk('product')->put($fileName, \Illuminate\Support\Facades\File::get($sponsorImage));
            // $sponsorImageFile = '/storage/product/'.$fileName;

            $ext = $sponsorImage->getClientOriginalExtension();
            $fileName = time() . $sponsorImage->getFilename() . '.' . $ext;
            $sponsorImage->storeAs('/public/', $fileName);
            $avatar = '/storage/' . $fileName;
        }
        //end sponsor image
        $validatorArray = [
            'company_id' => 'required',
            'category_class_id' => 'required',
            'name' => 'required',
            'insurance_type_id' => 'required',
            'code' => 'required|unique:mp_products,code'
        ];

        $validator = Validator::make($request->all(), $validatorArray);
        if ($validator->fails()) {
            $message = $validator->errors();
            return Redirect::route('product.sp.create')->withErrors([$message->first()]);
        }

        DB::beginTransaction();
        try {
            $result = Product::create([
                "company_id" => $params["company_id"],
                "category_class_id" => $params["category_class_id"],
                "name" => $params["name"],
                "code" => $params['code'],
                "avatar" => $avatar,
                "sponsor_image" => $sponsorImageFile,
                "description" => isset($params["description"]) ? $params["description"] : null,
                "content" => isset($params["content"]) ? $params["content"] : null,
                "created_by" => $currentUser->id,
                "updated_by" => $currentUser->id,
                "status" => $params["status"],
                'is_feature' => isset($params['is_feature']) ? (int)$params['is_feature'] : 0,
                'is_agency' => isset($params['is_agency']) ? (int)$params['is_agency'] : 0,
                'is_sponsor' => isset($params['is_sponsor']) ? (int)$params['is_sponsor'] : 0,
                'for_customer' => isset($params['for_customer']) ? (int)$params['for_customer'] : 0,
                'extra_for_insurance_type' => isset($params['extra_for_insurance_type']) ? (int)$params['extra_for_insurance_type'] : 0,
                'extra_for_product' => isset($params['extra_for_product']) ? (int)$params['extra_for_product'] : 0,
                'extra_fees' => isset($params['extra_fee']) ? json_encode($params['extra_fee']) : '',
                'default_extra_fee_attribute_values' => isset($params['default_extra_fee_attribute_values']) ? json_encode($params['default_extra_fee_attribute_values']) : '',
                'insurance_formula_id' => isset($params['insurance_formula_id']) ? (int)$params['insurance_formula_id'] : 0,
                'default_price_attribute_values' => isset($params['default_price_attribute_value']) ? json_encode($params['default_price_attribute_value']) : '',
                'product_type_online' => !empty($params['product_type_online']) ? 1 : 0,
                'Insurance_money' => (isset($params['Insurance_money']) && !empty($params['Insurance_money'])) ? $params['Insurance_money'] : 0
            ]);

            $commission_rate = $request->commission_rate;
            $counterpart_commission_rate = $request->counterpart_commission_rate;

            $levels = Level::all();
            foreach ($levels as $level) {
                LevelProduct::create([
                    'level_id' => $level->id,
                    'product_id' => $result->id,
                    'commission_rate' => $commission_rate[$level->id],
                    'counterpart_commission_rate' => $counterpart_commission_rate[$level->id]
                ]);
            }


            $result->saveCategory($params["category_ids"]);
            $result->saveCommission($params["commission_type"], $params["commission_amount"]);
            if (!empty($params["commission_subsidiary_amount"]) && !empty($params["commission_subsidiary_type"]) && !empty($params["subsidiary_id"])) {
                $result->saveCommissionSubsidiary($params["commission_subsidiary_type"], $params["commission_subsidiary_amount"], $params["subsidiary_id"]);
            }
            $result->saveCustomerCommission($params['customer_commission']);
            $result->saveLevelCommission(!empty($params["commission_level"]) ? $params["commission_level"] : []);
            $result->saveInsuranceType($params['insurance_type_id']);
            DB::commit();

            Cache::tags('product')->flush();


            return Redirect::route('product.sp.index');
        } catch (\Exception $e) {
            DB::rollback();
            Log::alert($e);
            return Redirect::route('product.sp.index')->withErrors(["Lỗi không lưu được bản ghi!"]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit($id)
    {
        $obj = Product::where("id", $id)->with('insurance_type')->first();
        $productLevelCommissions = $obj->level_commissions()->pluck('commission_amount', 'commission_id');
        $pCategoryIds = $obj->product_categories()->pluck('category_id')->all();
        $productCommission = $obj->commission();
        $productCustomerCommission = $obj->customer_commission();

        // Get list product for extra_for_product options
        $listExtraForProducts = [];
        if (!empty($obj->extra_for_insurance_type)) {
            $listExtraForProducts = Product::getListProduct([
                'insurance_type_id' => $obj->extra_for_insurance_type,
                'company_id' => $obj->company_id
            ]);
        }

        // Get list insurance
        if (isset($obj->insurance_type->insurance_type_id) && !empty($obj->insurance_type->insurance_type_id)) {
            $insuranceTypeIds = [$obj->insurance_type->insurance_type_id];
        } else {
            foreach ($obj->product_categories as $productCategory) {
                $insuranceTypeIds[] = $productCategory->category->insurance_type_id;
            }
        }

        $priceTypes = [];
        $priceAttributes = [];
        if (isset($insuranceTypeIds) && !empty($insuranceTypeIds)) {
            // List price types
            $priceTypes = InsurancePriceType::getListPriceTypesByTypeIds($insuranceTypeIds);

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
        }
        // comission level product
        // $level_products = LevelProduct::where('product_id',$id)->get();


        //Danh sach hoa hong cong ty con
        $parent_id = $obj->company_id;

        $subsidiaries = InsuranceCompany::where('parent_id', $parent_id)->with(['commissionSubsidiary' => function ($query) use ($id) {
            $query->where('product_id', $id);
        }])->get();

        return view('product::products/edit', [
            "product" => $obj,
            "productCategoryIds" => $pCategoryIds,
            'companies' => Company::where('parent_id', 0)->get(),
            // 'level_products' => $level_products,
            'classes' => CategoryClass::getListClassByCategoryIds($pCategoryIds),
            "categories" => Category::getNestedListCategory(),
            "productCommission" => $productCommission,
            "productLevelCommissions" => $productLevelCommissions,
            "commissionLevels" => Commission::all(),
            'insuranceTypes' => InsuranceType::getListType(),
            'extraFees' => InsuranceExtraFee::getListWithKeyCodeByTypeId($obj->insurance_type->insurance_type_id),
            'formulas' => InsuranceFormula::getListByType($obj->insurance_type->insurance_type_id),
            'listExtraForProducts' => $listExtraForProducts,
            'priceTypes' => $priceTypes,
            'priceAttributes' => $priceAttributes,
            'productCustomerCommission' => $productCustomerCommission,
            'subsidiaries' => $subsidiaries,
            'levels' => Level::orderBy('level', 'asc')
                ->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @return Response
     */
    ///// Thêm hoa hồng cho sản phẩm
    public function CreateProductLevel($id)
    {
        $product = Product::find($id);
        return view('product::products/create_product_level', [
            'levels' => Level::orderBy('level', 'asc')->get(),
            'product' => $product
        ]);
    }

    public function storeProductLevel(Request $request, $id)
    {
        try {
            Product::find($id)->levels()->detach();

            $commission_rate = $request->commission_rate;
            $counterpart_commission_rate = $request->counterpart_commission_rate;

            $levels = Level::all();
            foreach ($levels as $level) {
                LevelProduct::create([
                    'level_id' => $level->id,
                    'product_id' => $id,
                    'commission_rate' => $commission_rate[$level->id],
                    'counterpart_commission_rate' => $counterpart_commission_rate[$level->id]
                ]);
            }
            return Redirect::route('product.sp.index')->with('msg_success', 'Thêm hoa hồng theo cấp thành công');
        } catch (\Exception $e) {
            DB::rollback();
            Log::alert($e);
            return Redirect::route('product.sp.index')->withErrors(["Sản phẩm đã có hoa hồng theo cấp!"]);
        }
    }


    public function update(Request $request, $id)
    {
        $currentUser = Auth::user();
        $params = $request->all();
        //upload image
        $file = $request->file('avatar');
        $avatar = '';
        if (!empty($file)) {
            $ext = $file->getClientOriginalExtension();
            $fileName = time() . $file->getFilename() . '.' . $ext;
            Storage::disk('product')->put($fileName, \Illuminate\Support\Facades\File::get($file));
            $avatar = '/storage/product/' . $fileName;
        }
        //end upload image
        //sponsor image
        $sponsorImage = $request->file('sponsor_image');
        $sponsorImageFile = '';
        if (!empty($sponsorImage)) {
            $ext = $sponsorImage->getClientOriginalExtension();
            $fileName = time() . $sponsorImage->getFilename() . '.' . $ext;
            Storage::disk('product')->put($fileName, \Illuminate\Support\Facades\File::get($sponsorImage));
            $sponsorImageFile = '/storage/product/' . $fileName;
        }
        //end sponsor image
        $validatorArray = [
            'company_id' => 'required',
            'category_class_id' => 'required',
            'name' => 'required',
            'status' => 'required',
            'code' => 'required|unique:mp_products,code,' . $id
        ];

        $validator = Validator::make($request->all(), $validatorArray);
        if ($validator->fails()) {
            $message = $validator->errors();
            return Redirect::route('product.sp.edit', $id)->withErrors([$message->first()]);
        }

        $obj = Product::where("id", $id)->first();
        if ($obj) {
            $obj->company_id = $params["company_id"];
            $obj->category_class_id = $params["category_class_id"];
            $obj->name = $params["name"];
            $obj->code = $params["code"];
            if ($avatar != '') {
                $obj->avatar = $avatar;
            }
            if ($sponsorImageFile != '') {
                $obj->sponsor_image = $sponsorImageFile;
            }
            $obj->description = isset($params["description"]) ? $params["description"] : null;
            $obj->content = isset($params["content"]) ? $params["content"] : null;
            $obj->updated_by = $currentUser->id;
            $obj->status = $params["status"];
            $obj->is_feature = isset($params['is_feature']) ? (int)$params['is_feature'] : 0;
            $obj->is_agency = isset($params['is_agency']) ? (int)$params['is_agency'] : 0;
            $obj->is_sponsor = isset($params['is_sponsor']) ? (int)$params['is_sponsor'] : 0;
            $obj->for_customer = isset($params['for_customer']) ? (int)$params['for_customer'] : 0;
            $obj->extra_for_insurance_type = isset($params['extra_for_insurance_type']) ? (int)$params['extra_for_insurance_type'] : 0;
            $obj->extra_for_product = isset($params['extra_for_product']) ? (int)$params['extra_for_product'] : 0;
            $obj->extra_fees = isset($params['extra_fee']) ? json_encode($params['extra_fee']) : '';
            $obj->insurance_formula_id = isset($params['insurance_formula_id']) ? (int)$params['insurance_formula_id'] : 0;
            $obj->default_price_attribute_values = isset($params['default_price_attribute_value']) ? json_encode($params['default_price_attribute_value']) : '';
            $obj->default_extra_fee_attribute_values = isset($params['default_extra_fee_attribute_values']) ? json_encode($params['default_extra_fee_attribute_values']) : '';
            $obj->product_type_online = !empty($params['product_type_online']) ? 1 : 0;
            $obj->Insurance_money = (isset($params['Insurance_money']) && !empty($params['Insurance_money'])) ? $params['Insurance_money'] : 0;
            $obj->PEYP = isset($params['PEYP']) ? $params['PEYP'] : 1;
            $obj->popup_app1 = isset($params['popup_app1']) ? $params['popup_app1'] : '';
            $obj->popup_app2 = isset($params['popup_app2']) ? $params['popup_app2'] : '';
            $obj->popup_app3 = isset($params['popup_app3']) ? $params['popup_app3'] : '';
            $obj->is_hot = isset($params['is_hot']) ? $params['is_hot'] : 0;
            $obj->file_type = isset($params['file_type']) ? $params['file_type'] : null;
            $obj->is_life = isset($params['is_life']) ? (int)$params['is_life'] : 1;

            DB::beginTransaction();
            try {
                $obj->save();
                $obj->saveCategory($params["category_ids"]);
                $commission = $obj->commission();

                if (!$commission || $commission->commission_type != $params["commission_type"] || $commission->commission_amount != $params["commission_amount"]) {
                    $obj->saveCommission($params["commission_type"], $params["commission_amount"]);
                }

                if (!empty($params["commission_subsidiary_amount"]) && !empty($params["commission_subsidiary_type"]) && !empty($params["subsidiary_id"])) {
                    $obj->saveCommissionSubsidiary($params["commission_subsidiary_type"], $params["commission_subsidiary_amount"], $params["subsidiary_id"]);
                }

                $customerCommission = $obj->customer_commission();
                if (!$customerCommission || $customerCommission->commission_amount != $params['customer_commission']) {
                    $obj->saveCustomerCommission($params['customer_commission']);
                }


                $obj->saveLevelCommission(!empty($params["commission_level"]) ? $params["commission_level"] : []);
                $obj->saveInsuranceType($params['insurance_type_id']);

                $commission_rate = $request->commission_rate;
                $counterpart_commission_rate = $request->counterpart_commission_rate;
                $levels = Level::all();
                foreach ($levels as $level) {
                    $obj->levels()->updateExistingPivot($level->id, [
                        'commission_rate' => $commission_rate[$level->level],
                        'counterpart_commission_rate' => $counterpart_commission_rate[$level->level]
                    ]);
                }

                DB::commit();

                Cache::tags('product')->flush();

                return Redirect::route('product.sp.index');
            } catch (\Exception $e) {
                DB::rollback();
                Log::alert($e);
                return Redirect::route('product.sp.index')->withErrors(["Lỗi không lưu được bản ghi!"]);
            }
        } else {
            return Redirect::route('product.sp.index')->withErrors(["Bản ghi không tồn tại!"]);
        }
    }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy($id)
    {
        $obj = Product::where("id", $id)->first();
        if ($obj) {
            $obj->delete();
//            $obj->status = Product::STATUS_DELETED;
//            $obj->save();

            Cache::tags('product')->flush();

            return Redirect::route('product.sp.index');
        } else {
            return Redirect::route('product.sp.index')->withErrors(["Bản ghi không tồn tại!"]);
        }
    }

    /**
     * Show the specified resource from storage.
     * @return Response
     */
    public function show($id)
    {
        return Redirect::route('product.sp.edit', $id);
    }


    /**
     * Show the attribute page.
     * @return Response
     */
    public function attribute($id)
    {
        $obj = Product::where("id", $id)->first();

        if ($obj) {
            return view('product::attributes/edit', [
                "product" => $obj,
                "productAttributes" => $obj->attributes()->get(),
                "attributes" => $obj->product_categories()->first()->category()->first()->attributes()->get()
            ]);
        } else {
            return Redirect::route('product.sp.index')->withErrors(["Bản ghi không tồn tại!"]);
        }
    }

    /**
     * Update attribute of product.
     * @return Response
     */
    public function updateAttribute(Request $request, $id)
    {
        $params = $request->all();
        $obj = Product::where("id", $id)->first();
        if ($obj) {
            ProductAttribute::saveAtributes($params, $id);
            return Redirect::route('product.sp.index');
        } else {
            return Redirect::route('product.sp.index')->withErrors(["Bản ghi không tồn tại!"]);
        }
    }

    /**
     * @param $productId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function updatePriceType($productId)
    {
        $product = Product::getProduct($productId);

        // Get price type for product by insurance type id
        $priceTypes = InsurancePriceType::getListPriceTypesByTypeId($product->insurance_type->insurance_type_id, InsurancePriceType::USE_TYPE_BY_PRODUCT);

        return view('product::products.update_price_type', compact('product', 'priceTypes'));
    }

    /**
     * @param Request $request
     * @param $productId
     * @return $this|\Illuminate\Http\RedirectResponse
     */
    public function updatePriceTypePost(Request $request, $productId)
    {
        try {
            // config_price_types
            Product::where('id', $productId)->update([
                'config_price_types' => isset($request->config_value) ? json_encode($request->config_value) : ''
            ]);

            return redirect()->route('product.prices.index', $productId);
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . '. ' . $ex->getFile() . ':' . $ex->getLine());

            return redirect()->route('product.update_price_type', $productId)->withErrors([$ex->getMessage()]);
        }
    }

    public function getClasses(Request $request)
    {
        $params = $request->all();
        $data = CategoryClass::getListClassByCategoryIds(explode(",", $params["category_ids"]));
        return \response()->json([
            'data' => $data
        ]);
    }

    /**
     * Get list product by ajax request
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajaxGetListProduct(Request $request)
    {
        $products = Product::getListProduct($request->input());

        return response()->json(['success' => true, 'products' => $products, 'message' => '']);
    }

    public function bhtnGetPrice(Request $request)
    {
        $params = $request->all();

        $BHTN_time = $params['filter_data']['BHTN_time'];
        $insurance_type_id = $params['type_id'];
        $product_id = $params['product_id'];
        $data = [];
        $i = 0;
        foreach ($params['beneficiary'] as $key => $beneficiary) {
            if (empty($beneficiary['date_of_birth'])) {
                $data[$i] = [];
            } else {
                $bits = explode('/', $beneficiary['date_of_birth']);
                // $age_range = date('Y') - $bits[2] - 1;
                if (date('m') > $bits[1]) {
                    $age_range = date('Y') - $bits[2];
                } elseif (date('m') == $bits[1]) {
                    if (date('d') < $bits[0]) {
                        $age_range = date('Y') - $bits[2] - 1;
                    } else {
                        $age_range = date('Y') - $bits[2];
                    }
                } else {
                    $age_range = date('Y') - $bits[2] - 1;
                }
                $filter_data['age_range'] = $age_range;
                $filter_data['BHTN_time'] = $BHTN_time;
                $filter_data['sex'] = $beneficiary['sex'];

                $productPrice = ProductPriceHelper::getProductPrice($product_id, $insurance_type_id, $filter_data, [], [], []);
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
                    $data[$i]['message'] = 'Không tìm thấy giá cho sản phẩm 1 ';
                    $data[$i]['index_size'] = $key;
                }
            }
            $i++;
        }
        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * Get product price and code by product id and filter data
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProductPrice(Request $request)
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

        if (isset($params['product_id']) && isset($params['insurance_type_id']) && $params['insurance_type_id'] == 22) {
            $product = Product::where('mp_products.id', $params['product_id'])
                ->select('mp_products.id', 'mp_products.code', 'mp_product_prices.price_detail')
                ->join('mp_product_prices', 'mp_product_prices.product_id', '=', 'mp_products.id')
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

        if (isset($params['product_id']) && isset($params['insurance_type_id']) && $params['insurance_type_id'] == 27) {
            $product = Product::where('mp_products.id', $params['product_id'])
                ->select('mp_products.id', 'mp_products.code', 'mp_product_prices.price_detail')
                ->join('mp_product_prices', 'mp_product_prices.product_id', '=', 'mp_products.id')
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
        if (isset($params['product_id']) && isset($params['insurance_type_id']) && isset($params['filter_data'])) {
            $priceType = $request->price_type ?? [];
            if (!empty($priceType) && !is_array($priceType)) {
                $priceType = explode(',', $priceType);
            }
            $params["custom_price_type_health_insurance"] = $params["custom_price_type_health_insurance"] ?? [];
            $productPrice = ProductPriceHelper::getProductPrice($params['product_id'], $params['insurance_type_id'], $params['filter_data'], $priceType, [], $params["custom_price_type_health_insurance"]);
            if (isset($productPrice['prices']) && !empty($productPrice['prices'])) {
                // if($params['insurance_type_id'] == 21 && isset($productPrice['prices']['price'])) {
                //     if($params['filter_data']['relationship'] == 'Vợ chồng' || $params['filter_data']['relationship'] == 'Con') {
                //         // vk ck con của chủ hợp đồng sẽ dc giảm 20%
                //         $productPrice['prices']['price'] = (80 * $productPrice['prices']['price']) / 100;
                //         $product_price_str = number_format($productPrice['prices']['price'], 0) . ' Giảm 20%';
                //     } else {
                //         $product_price_str = number_format($productPrice['prices']['price'], 0);
                //     }
                // } else {
                //     $product_price_str = number_format($productPrice['prices']['price'], 0);
                // }

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
                'extra_for_product' => 0
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
                    'html' => $view->render(),
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
                    'html' => $view->render(),
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
     * @param Request $request
     * @return array
     */
    public function checkUnitPriceTypeHealthInsurance(Request $request)
    {
        $params = $request->all();
        if (!empty($params) && $params["productId"] != "") {
            $product_detail = Product::select('unit_price_type_health_insurance')->findOrFail($params["productId"]);
            return [
                "success" => true,
                "data" => !empty($product_detail->unit_price_type_health_insurance) ? \GuzzleHttp\json_decode($product_detail->unit_price_type_health_insurance, true) : new \stdClass(),
            ];
        } else {
            return [
                "success" => false,
                "message" => "No data"
            ];
        }
    }
}
