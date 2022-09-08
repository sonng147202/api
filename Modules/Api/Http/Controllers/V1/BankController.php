<?php

namespace Modules\Api\Http\Controllers\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Http\Controllers\ApiController;
use App\Models\User;
use App\Models\Bank;

class BankController extends ApiController
{
    // Danh sách ngân hàng
    public function getListBank(Request $request)
    {
        try {
            $params = $request->all();
            $user = User::where('group_id', 3)->find($params['user_id']);
            if($user == null)
            {
                return \response()->json([
                    'result'       => 0,
                    'current_time' => time(),
                    'message'      => 'Error! Không có user như vậy trong hệ thống!',
                    'data'         => null
                ]);
            }

            $listData = Bank::select(
                'banks.id',
                'banks.bank_name'
            );

            if(!empty($params['search'])){
                $listData = $listData->where('banks.bank_name','like','%'.$params['search'].'%');
            }

            $listData = $listData->orderBy('banks.bank_name','ASC')->paginate(12)->toArray();

            $data = $listData;
            
        } catch (\Exception $e) {
            $data = ['error_msg' => $e->getMessage()];
        }
        return $this->apiResponse($data);
    }
}
