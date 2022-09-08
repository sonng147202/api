<?php

namespace Modules\Api\Http\Controllers\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Models\Category;
use App\Models\ProductVBIInsurancePackage;
use App\Models\ProductVBIAdditionCondition;
use App\Models\Product;
use App\Http\Controllers\ApiController;
use DB;

class ProductController extends ApiController
{
    // Danh sách loại bảo hiểm (Danh mục sản phẩm)
    public function getListTypeInsurance(Request $request)
    {
        try {
            $data = Category::where('status', Category::STATUS_ACTIVE);

            if (isset($request['name']))
            {
                $data = $data->where('name', 'like', '%'.$request['name'].'%');
            }

            $data = $data->paginate(12)->toArray();
        } catch (\Exception $e) {
            $data = [
                'error_msg' => $e->getMessage(),
            ];
        }

        return $this->apiResponse($data, null, 1);
    }

    // Danh sách sản phẩm theo loại bảo hiểm (Danh mục sản phẩm)
    public function getListInsuranceByTypeInsurance(Request $request)
    {
        if(empty($request['category_id'])){
            $data = [
                'error_msg' => "Id loại sản phẩm khôg được để trống",
            ];
        }

        try {
            $data = Product::where('category_id', $request['category_id'])->where('status', Product::STATUS_ACTIVE);

            if (isset($request['name']))
            {
                $data = $data->where('name', 'like', '%'.$request['name'].'%');
            }

            $data = $data->paginate(12)->toArray();
        } catch (\Exception $e) {
            $data = [
                'error_msg' => $e->getMessage(),
            ];
        }

        return $this->apiResponse($data, null, 1);
    }

    // Lấy danh sách gói bảo hiểm VBI care theo sản phẩm (1 sản phẩm có nhiều gói BH)
    public function getListVBIInsurancePackageByProduct(Request $request)
    {
        try {
            if(empty($request['product_id'])){
                $data = [
                    'error_msg' => "Id sản phẩm không được để trống",
                ];
            }

            $data = ProductVBIInsurancePackage::where('product_id', $request['product_id'])->get()->toArray();
        } catch (\Exception $e) {
            $data = [
                'error_msg' => $e->getMessage(),
            ];
        }

        return $this->apiResponse($data, null, 1);
    }

    // Lấy danh sách điều kiện bổ sung của bảo hiểm theo gói BH (1 BH có ĐKBS)
    public function getListVBIAdditionConditionByInsurancePackage(Request $request)
    {
        if(empty($request['insurance_package_id'])){
            $data = [
                'error_msg' => "Id gói bảo hiểm không được để trống",
            ];
        }

        try {
            $data = ProductVBIAdditionCondition::where('insurance_package_id', $request['insurance_package_id'])->get()->toArray();
        } catch (\Exception $e) {
            $data = [
                'error_msg' => $e->getMessage(),
            ];
        }

        return $this->apiResponse($data, null, 1);
    }

    // Danh sách sản phẩm bảo hiểm theo công ty bảo hiểm
    public function getListInsuranceByCompany(Request $request)
    {
        try {
            $params = $request->all();

            $products = Product::select(
                'mp_products.id as product_id',
                'mp_products.code as product_code',
                'mp_products.name as product_name',
                'mp_products.insurance_company_id',
                DB::raw("(CASE WHEN mp_products.is_main_product = 1 
                            THEN 'Sản phẩm chính' 
                            ELSE 'Sản phẩm phụ' 
                            END) AS is_main_product"),
                'mp_products.status',
                'mp_products.discount',
                'mp_products.calculate_pfyp',
                'mp_products.updated_at',
                'mp_products.created_at',
                'mp_products.category_id'
            )
            ->leftjoin('insurance_companies', 'insurance_companies.id', 'mp_products.insurance_company_id')
            ->where('mp_products.insurance_company_id', $params['insurance_company_id'])
            ->paginate(12)
            ->toArray();

            $data = $products;

        } catch (\Exception $e) {
            $data = ['error_msg' => $e->getMessage()];
        }
        return $this->apiResponse($data);
    }

    // Lấy danh sách các sản phẩm bảo hiểm nhân thọ
    public function getListInsuranceLife(Request $request)
    {
        try {
            $data = $list = Product::select(
                'mp_products.*','ic.name as company_name','ic.id as company_id'
            )
            ->leftjoin('insurance_companies as ic', 'ic.id', 'mp_products.insurance_company_id')
            ->whereIn('insurance_company_id', [1, 10])
            ->where('mp_products.status','!=',0)
            ->get()->toArray();

        } catch (\Exception $e) {
            $data = [
                'error_msg' => $e->getMessage(),
            ];
        }

        return $this->apiResponse($data, null, 1);
    }

    // Lấy danh sách các sản phẩm bảo hiểm phi nhân thọ
    public function getListInsuranceNonLife(Request $request)
    {
        try {
            $data = $list = Product::select(
                'mp_products.*','ic.name as company_name','ic.id as company_id'
            )
            ->leftjoin('insurance_companies as ic', 'ic.id', 'mp_products.insurance_company_id')
            ->whereNotIn('insurance_company_id', [1, 10])
            ->where('mp_products.status','!=',0)
            ->get()->toArray();

        } catch (\Exception $e) {
            $data = [
                'error_msg' => $e->getMessage(),
            ];
        }

        return $this->apiResponse($data, null, 1);
    }
}
