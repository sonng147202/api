<?php

namespace Modules\Api\Http\Controllers\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Http\Controllers\ApiController;
use App\Models\Product;
use App\Models\InsuranceCompany;

class InsuranceProductController extends ApiController
{
    // Chi tiết sản phẩm
    public function getInsuranceDetail(Request $request)
    {
        try {
            $params = $request->all();
            $product = Product::where('id',$params['product_id'])->first();
            $company = InsuranceCompany::where('id',$product->insurance_company_id)->first();
            $data = [
                "product" => $product,
                'company'=>$company
            ];
        } catch (\Exception $e) {
            $data = ['error_msg' => $e->getMessage()];
        }
        return $this->apiResponse($data);
    }
}
