<?php

namespace Modules\Api\Http\Controllers\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Http\Controllers\ApiController;
use App\Models\InsuranceCompany;

class InsuranceCompanyController extends ApiController
{
    // Danh sách công ty bảo hiểm
    public function getInsuranceLifeCompany(Request $request)
    {
        try {
            $params = $request->all();   
            $companies = InsuranceCompany::searchIndex($params)->whereIn('id',[1,10])->paginate(50)->toArray();
            $data = [
                "companies" => $companies
            ];
        } catch (\Exception $e) {
            $data = ['error_msg' => $e->getMessage()];
        }
        return $this->apiResponse($data);
    }

    // Danh sách công ty bảo hiểm phi nhân thọ
    public function getInsuranceNonLifeCompany(Request $request)
    {
        try {
            $params = $request->all();   
            $companies = InsuranceCompany::searchIndex($params)->whereNotIn('id',[1,10])->paginate(50)->toArray();
            $data = [
                "companies" => $companies
            ];
        } catch (\Exception $e) {
            $data = ['error_msg' => $e->getMessage()];
        }
        return $this->apiResponse($data);
    }
}
