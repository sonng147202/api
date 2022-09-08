<?php
namespace Modules\Api\Http\Controllers\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\ApiController;
use App\Models\PasswordReset;
use App\Models\User;
use App\Models\MailQueue;
use App\Jobs\SendMailOTP;
use Hash;

class ForgotPasswordController extends ApiController
{
    //Api gửi mã OTP để đặt lại mật khậu cho người dùng
    public function sendOTP(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required', 
            ], [
                'email.required' => "Email không được để trống"
            ]);
            
            if ($validator->fails()) {
                $data = [
                    'error_msg' => $validator->errors()->first()
                ];
            } else {
                $email = $request->email;
    
                // Kiểm tra email người dùng đã đăng kí chưa
                if (User::where('email', $email)->exists()) {
                    $active_code = rand(1000,9999);; // Mã OTP
                    
                    // Lưu mã OTP vào DB và nếu tồn thì cập nhật lại mã OTP
                    $passwordReset = PasswordReset::updateOrCreate([
                        'email' => $email,
                    ], [
                        'active_code' => $active_code,
                    ]);
    
                    if ($passwordReset) {
                        // Gửi mã OTP tới tài khoản email
                        MailQueue::SendMailNow([
                            'templete' => 'email.forgotPassword',
                            'variable' => [
                                'data' => $active_code,
                            ],
                            'subject' => 'Đặt lại mật khẩu',
                            'to' => [$email],
                        ]);

                        $data = [
                            'message' => "Đã gửi mã OTP tới email '$email'"
                        ];
                    }
                } else {
                    $data = [
                        'error_msg' => "Email '$email' chưa đăng kí tài khoản"
                    ];
                }
            }
        } catch (\Exception $e) {
            $data = ['error_msg' => $e->getMessage()];
        }
        return $this->apiResponse($data);
    }

    // Api nhập mã xác nhận mã OTP
    public function confirmOTP(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required', 
                'password_reset_code' => 'required', 
            ], [
                'email.required' => "Email không được để trống",
                'password_reset_code.required' => "Mã OTP không được để trống",
            ]);

            if ($validator->fails()) {
                $data = [
                    'error_msg' => $validator->errors()->first()
                ];
            } else {
                $email = $request->email; // email
                $password_reset_code = $request->password_reset_code; // mã OTP
    
                // Kiểm tra xem email người dùng có trong danh sách quên mật khẩu không
                if (PasswordReset::where('email', $email)->exists()) {
                    // Đối chiếu với DB 
                    $password_reset = PasswordReset::where([
                        'email' => $email,
                        'active_code' => $password_reset_code,
                    ])->first();
                    // Nếu đúng thì gắn mã OTP bằng NULL đồng nghĩa là email này đã xác nhận mã OTP
                    if ($password_reset) {
                        $password_reset->active_code = null;
                        $password_reset->update();
    
                        $data = [
                            'message' => $email
                        ];
                    } else {
                        $data = [
                            'error_msg' => "Mã OTP không đúng"
                        ];
                    }
                } else {
                    $data = [
                        'error_msg' => "Không tìm thấy email này trong danh sách"
                    ];
                }
            }
        } catch (\Exception $e) {
            $data = ['error_msg' => $e->getMessage()];
        }
        return $this->apiResponse($data);
    }

    // Api thay đổi mật khẩu
    public function resetPassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required', 
                'password' => 'required|confirmed|min:8', 
            ], [
                'email.required' => "Email không được để trống",
                'password.required' => "Mật khẩu không được để trống",
                'password.confirmed' => "Mật khẩu xác nhận không được để trống",
                'password.min' => "Mật khẩu không được ít hơn 8 kí tự",
            ]);

            if ($validator->fails()) {
                $data = [
                    'error_msg' => $validator->errors()->first()
                ];
            } else {
                $email = $request->email; // email
                $password = $request->password; // password
            
                // Kiểm tra xem email đã active mã OTP chưa
                $password_reset = PasswordReset::where(['email'=> $email, 'active_code' => '']);
    
                if ($password_reset->exists()) {
                    $user = User::where('email', $email)->first();
                    $user->password = $password;
                    $user->save();
    
                    $password_reset->delete();
    
                    $data = [
                        'message' => "Đổi mật khẩu thành công"
                    ];
                } else {
                    $data = [
                        'error_msg' => "Email '$email' chưa xác thực mã OTP"
                    ];
                }
            }
        } catch (\Exception $e) {
            $data = ['error_msg' => $e->getMessage()];
        } 
        return $this->apiResponse($data, null, 1);
    }
}
