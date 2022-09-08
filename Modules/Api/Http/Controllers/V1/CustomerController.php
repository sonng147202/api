<?php

namespace Modules\Api\Http\Controllers\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Http\Controllers\ApiController;
use App\Models\Customer;
use App\Models\Province;
use DB;

class CustomerController extends ApiController
{
    // Chi tiết KH theo số CMND/CCCD
    public function getInfoCustomerByIdCardNumber(Request $request)
    {
        try {
            $params = $request->all();

            $customer = Customer::select(
                'customers.id as customer_id',
                'customers.id_card_number',
                'customers.name as customer_name',
                'customers.phone_number',
                'customers.date_of_birth',
                DB::raw("(CASE WHEN customers.sex = 1 
                            THEN 'Nam' 
                            ELSE 'Nữ' 
                          END) AS sex"),
                'customers.email',
                'customers.address',
                'provinces.name as province_name',
                'customers.date_card_number',
                'customers.place_card_number'
            )
            ->leftjoin('provinces', 'provinces.id', 'customers.province_id')
            ->where('customers.id_card_number', $params['id_card_number'])
            ->first();

            if($customer != null)
            {
                $customer = $customer->toArray();
            }

            $data = $customer;

        } catch (\Exception $e) {
            $data = ['error_msg' => $e->getMessage()];
        }
        return $this->apiResponse($data);
    }

    // Khách hàng: Danh sách KH + tìm kiếm
    public function getListCustomer(Request $request)
    {
        try {
            $params = $request->all();

            $customer = Customer::select(
                'customers.id as customer_id',
                'customers.id_card_number',
                'customers.name as customer_name',
                'customers.phone_number',
                'customers.date_of_birth',
                DB::raw("(CASE WHEN customers.sex = 1 
                            THEN 'Nam' 
                            ELSE 'Nữ' 
                          END) AS sex"),
                'customers.email',
                'customers.address',
                DB::raw("COUNT(insurance_contracts.id) as number_of_contracts")
            )
            ->leftjoin('insurance_contracts', 'insurance_contracts.customer_id', 'customers.id')
             ->groupBy('customers.id','customers.id_card_number','customers.name','customers.phone_number',
                       'customers.date_of_birth','sex','customers.email','customers.address');

            if(!empty($params['search'])){
                $customer = $customer->where('customers.name','like','%'.$params['search'].'%')
                                     ->orWhere('customers.phone_number','like','%'.$params['search'].'%');
            }

            $customer = $customer->orderBy('customers.id','ASC')->paginate(50)->toArray();
            $data = $customer;

        } catch (\Exception $e) {
            $data = ['error_msg' => $e->getMessage()];
        }
        return $this->apiResponse($data);
    }

    // Danh sách hợp đồng của khách hàng
    public function getListContractByCustomer(Request $request)
    {
        try {
            $params = $request->all();

            $listContract = Customer::select(
                'customers.id as customer_id',
                'customers.name as customer_name',
                'customers.phone_number',
                'customers.address',
                'insurance_contracts.id as contract_id',
                'insurance_contracts.contract_number',
                'mp_products.name',
                'insurance_contracts.status',
                'insurance_contracts.release_date',
                'insurance_contracts.fee_payment_next_date',
                'consulting_information.amount_paid as fee_main_product',
                'consulting_information.total_amount_sup as fee_sup_product'
            )
            ->leftjoin('insurance_contracts', 'insurance_contracts.customer_id', 'customers.id')
            ->join('consulting_information', 'consulting_information.id', 'insurance_contracts.consulting_information_id')
            ->join('mp_products', 'mp_products.id', 'consulting_information.main_product_id')
            ->where('customers.id', $params['customer_id']);

            $listContract = $listContract->orderBy('customers.id','ASC')->paginate(50)->toArray();
            $data = $listContract;

        } catch (\Exception $e) {
            $data = ['error_msg' => $e->getMessage()];
        }
        return $this->apiResponse($data);
    }

    // Tạo khách hàng
    public function storeCustomer(Request $request)
    {
        $params = $request->all();
        if (empty($params["name"])) {
            return \response()->json(['result' => 0, 'message' => 'Họ và tên không được bỏ trống']);
        }

        if (empty($params["date_of_birth"])) {
            return \response()->json(['result' => 0, 'message' => 'Ngày sinh khách hàng không được bỏ trống']);
        }

        if(empty($params['sex']) && $params['sex'] != '0'){
            return \response()->json(['result' => 0, 'message' => 'Giới tính khách hàng không được bỏ trống']);
        }

        if (empty($params["province_id"])) {
            return \response()->json(['result' => 0, 'message' => 'Tỉnh thành không được bỏ trống']);
        }

        if (empty($params["phone_number"])) {
            return \response()->json(['result' => 0, 'message' => 'Số điện thoại khách hàng không được bỏ trống']);
        }

        if (empty($params["email"])) {
            return \response()->json(['result' => 0, 'message' => 'Email khách hàng không được bỏ trống']);
        }

        if(!filter_var($params['email'], FILTER_VALIDATE_EMAIL)){
            return \response()->json(['result' => 0, 'message' => 'Email khách hàng không đúng định dạng']);
        }

        if (empty($params["address"])) {
            return \response()->json(['result' => 0, 'message' => 'Địa chỉ khách hàng không được bỏ trống']);
        }

        if (empty($params["id_card_number"])) {
            return \response()->json(['result' => 0, 'message' => 'Số CMND/CCCD khách hàng không được bỏ trống']);
        }

        if (empty($params["date_card_number"])) {
            return \response()->json(['result' => 0, 'message' => 'Ngày cấp CMND/CCCD không được bỏ trống']);
        }

        if (empty($params["place_card_number"])) {
            return \response()->json(['result' => 0, 'message' => 'Nơi cấp CMND/CCCD khách hàng không được bỏ trống']);
        }

        if (empty($params["insurance_agency_id"])) {
            return \response()->json(['result' => 0, 'message' => 'ID đại lý không được bỏ trống']);
        }

        DB::beginTransaction();
        try {
            $check = Customer::where('id_card_number', $params["id_card_number"])->first();
            if (!$check)
            {
                Customer::create([
                    'name'                  => mb_strtoupper($params["name"],'utf-8'),
                    'date_of_birth'         => date('Y-m-d',strtotime($params["date_of_birth"])),
                    'sex'                   => $params["sex"],
                    'province_id'           => $params["province_id"],
                    'phone_number'          => $params["phone_number"],
                    'email'                 => $params["email"],
                    'address'               => $params["address"],
                    'id_card_number'        => $params["id_card_number"],
                    'date_card_number'      => $params["date_card_number"],
                    'place_card_number'     => $params["place_card_number"],
                    'insurance_agency_id'   => $params["insurance_agency_id"],
                ]);
            }
            else {
                return \response()->json(['result' => 0, 'message' => 'Số CMND/CCCD khách hàng đã tồn tại']);
            }
            
            DB::commit();
            $data = [
                'result'       => 1,
                'current_time' => time(),
                'message'      => 'Tạo khách hàng thành công!',
            ];
        }catch(\Exception $e){
            DB::rollback();
            $data = [
                'result' => 0,
                'message' => $e->getMessage(),
            ];
        }

        return $this->apiResponse($data, null, 1);
    }

    // Danh sách tỉnh thành
    public function getListProvince(Request $request)
    {
        try {
            $data = Province::all()->toArray();
        } catch (\Exception $e) {
            $data = [
                'result' => 0,
                'message' => $e->getMessage(),
            ];
        }

        return $this->apiResponse($data, null, 1);
    }

    // Cập nhật thông tin khách hàng
    public function updateCustomer(Request $request)
    {
        $params = $request->all();
        if (empty($params["customer_id"])) {
            return \response()->json(['result' => 0, 'message' => 'ID khách hàng không được bỏ trống']);
        }

        if (empty($params["name"])) {
            return \response()->json(['result' => 0, 'message' => 'Họ và tên không được bỏ trống']);
        }

        if (empty($params["date_of_birth"])) {
            return \response()->json(['result' => 0, 'message' => 'Ngày sinh khách hàng không được bỏ trống']);
        }

        if(empty($params['sex']) && $params['sex'] != '0'){
            return \response()->json(['result' => 0, 'message' => 'Giới tính khách hàng không được bỏ trống']);
        }

        if (empty($params["province_id"])) {
            return \response()->json(['result' => 0, 'message' => 'Tỉnh thành không được bỏ trống']);
        }

        if (empty($params["phone_number"])) {
            return \response()->json(['result' => 0, 'message' => 'Số điện thoại khách hàng không được bỏ trống']);
        }

        if (empty($params["email"])) {
            return \response()->json(['result' => 0, 'message' => 'Email khách hàng không được bỏ trống']);
        }

        if(!filter_var($params['email'], FILTER_VALIDATE_EMAIL)){
            return \response()->json(['result' => 0, 'message' => 'Email khách hàng không đúng định dạng']);
        }

        if (empty($params["address"])) {
            return \response()->json(['result' => 0, 'message' => 'Địa chỉ khách hàng không được bỏ trống']);
        }

        if (empty($params["id_card_number"])) {
            return \response()->json(['result' => 0, 'message' => 'Số CMND/CCCD khách hàng không được bỏ trống']);
        }

        if (empty($params["date_card_number"])) {
            return \response()->json(['result' => 0, 'message' => 'Ngày cấp CMND/CCCD không được bỏ trống']);
        }

        if (empty($params["place_card_number"])) {
            return \response()->json(['result' => 0, 'message' => 'Nơi cấp CMND/CCCD khách hàng không được bỏ trống']);
        }

        DB::beginTransaction();
        try {
            $customer = Customer::find($params["customer_id"]);
            if ($customer) 
            {
                $check = Customer::whereNotIn('id_card_number', [$customer->id_card_number])->where('id_card_number', $params['id_card_number'])->first();
                if (!$check)
                {
                    $customer->update([
                        'name'                  => mb_strtoupper($params["name"],'utf-8'),
                        'date_of_birth'         => date('Y-m-d',strtotime($params["date_of_birth"])),
                        'sex'                   => $params["sex"],
                        'province_id'           => $params["province_id"],
                        'phone_number'          => $params["phone_number"],
                        'email'                 => $params["email"],
                        'address'               => $params["address"],
                        'id_card_number'        => $params["id_card_number"],
                        'date_card_number'      => $params["date_card_number"],
                        'place_card_number'     => $params["place_card_number"],
                    ]);
                }
                else {
                    return \response()->json(['result' => 0, 'message' => 'Số CMND/CCCD khách hàng đã tồn tại']);
                }
            }
            else {
                return \response()->json(['result' => 0, 'message' => 'Không tìm thấy khách hàng']);
            }
            
            DB::commit();
            $data = [
                'result'       => 1,
                'current_time' => time(),
                'message'      => 'Cập nhật thông tin thành công!',
            ];
        }catch(\Exception $e){
            DB::rollback();
            $data = [
                'result' => 0,
                'message' => $e->getMessage(),
            ];
        }

        return $this->apiResponse($data, null, 1);
    }
}
