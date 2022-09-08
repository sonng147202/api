<?php
namespace Modules\Api\Http\Controllers\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Http\Controllers\ApiController;
use Hash;

class ChangePasswordController extends ApiController
{
    // API thay đổi mật khẩu 
    public function changePassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required', 
                'current_password' => 'required|min:8', 
                'new_password' => 'required|confirmed|min:8', 
            ], [
                'user_id.required' => "ID của người dùng không được để trống",
                'current_password.required' => "Mật khẩu hiện tại không được để trống",
                'current_password.min' => "Mật khẩu hiện tại không được ít hơn 8 kí tự",
                'new_password.required' => "Mật khẩu mới không được để trống",
                'new_password.confirmed' => "Xác nhận mật khẩu mới không được để trống",
                'new_password.min' => "Mật khẩu mới không được ít hơn 8 kí tự",
            ]);

            if ($validator->fails()) {
                $data = [
                    'error_msg' => $validator->errors()->first()
                ];
            } else {
                $user = User::find($request->user_id);

                if (!(Hash::check($request->current_password, $user->password))) {
                    $data = ['error_msg' => "Mật khẩu hiện tại của bạn không khớp với mật khẩu bạn đã cung cấp"];
                } else {
                    if (strcmp($request->current_password, $request->new_password) == 0) {
                        $data = ['error_msg' => "Mật khẩu mới không được giống với mật khẩu hiện tại của bạn"];
                    } else {
                        $user->password = $request->new_password;
                        if ($user->save()) {
                            $data = [
                                'message' => "Đổi mật khẩu thành công"
                            ];
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $data = ['error_msg' => $e->getMessage()];
        }
        return $this->apiResponse($data);
    }
}
