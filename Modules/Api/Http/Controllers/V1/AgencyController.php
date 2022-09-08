<?php

namespace Modules\Api\Http\Controllers\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Models\InsuranceAgencyInherit;
use App\Models\User;
use App\Models\Level;
use App\Models\RevenueDaily;
use App\Models\Approve;
use App\Models\AgencyStartupSupport;
use App\Models\OfficeFee;
use Carbon\Carbon;
use App\Models\InsuranceAgency;
use App\Http\Controllers\ApiController;
use App\Models\AgencyInfoFwd;
use DB;
use Image;
use Illuminate\Support\Facades\Log;
use PDF;
use Illuminate\Support\Facades\Storage;
use File;

class AgencyController extends ApiController
{
    // Chi tiết nhân viên
    public function getUserDetail(Request $request)
    {
        try {
            if(empty($request->user_id)){
                return \response()->json(['result' => 0, 'message' => 'ID nhân viên không được trống']);
            }

            $data = User::where('group_id', '!=', 3)->with('insuranceAgency')->findOrFail($request->user_id);
        } catch (\Exception $e) {
            $data = [
                'result' => 0,
                'message' => $e->getMessage(),
            ];
        }
        return $this->apiResponse($data);
    }

    // Danh sách cấp
    public function getListLevel()
    {
        try {
            $data = Level::all();
        } catch (\Exception $e) {
            $data = [
                'result' => 0,
                'message' => $e->getMessage(),
            ];
        }
        return $this->apiResponse($data);
    }

    // Danh sách đại lý tuyến dưới
    public function getListAgencyBranch(Request $request)
    {
        try {
            $params = $request->all();

            if(empty($params['user_id'])){
                return \response()->json(['result' => 0, 'message' => 'ID đại lý không được trống']);
            }

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

            $agencyId = $user->insuranceAgency->id;
            $listData = InsuranceAgency::searchIndex($params, $agencyId);

            $data = $listData;
            
        } catch (\Exception $e) {
            $data = [
                'result' => 0,
                'message' => $e->getMessage(),
            ];
        }
        return $this->apiResponse($data);
    }

    // Đăng ký đại lý
    public function addRegisterOnline(Request $request) {
        $req = $request->all();

        if(empty($req['user_id'])){
            return \response()->json(['result' => 0, 'message' => 'ID đại lý không được trống']);
        }

        $user = User::where('group_id', 3)->find($req['user_id']);
        $idEdit = !empty($req['id']) ? $req['id'] : null;
       
        $dataEdit = [];
        if(!empty($idEdit)){
            $dataEdit = InsuranceAgency::where('id', $idEdit)->first();
        }

        // Validate
        if(empty($req['name'])){
            return \response()->json(['result' => 0, 'message' => 'Họ và tên không được bỏ trống']);
        }
        if(empty($req['birthday'])){
            return \response()->json(['result' => 0, 'message' => 'Ngày sinh không được bỏ trống']);
        }

        if(empty($req['sex']) && $req['sex'] != '0'){
            return \response()->json(['result' => 0, 'message' => 'Giới tính không được bỏ trống']);
        }
        if(empty($req['id_card_number'])){
            return \response()->json(['result' => 0, 'message' => 'Số CMND không được bỏ trống']);
        }
        if(empty($req['date_card_number'])){
            return \response()->json(['result' => 0, 'message' => 'Ngày cấp không được bỏ trống']);
        }
        if(empty($req['place_card_number'])){
            return \response()->json(['result' => 0, 'message' => 'Nơi cấp không được bỏ trống']);
        }
        if(empty($req['phone'])){
            return \response()->json(['result' => 0, 'message' => 'Số điện thoại không được bỏ trống']);
        }
        if(empty($req['email'])){
            return \response()->json(['result' => 0, 'message' => 'Email không được bỏ trống']);
        }
        if(!filter_var($req['email'], FILTER_VALIDATE_EMAIL)){
            return \response()->json(['result' => 0, 'message' => 'Email không đúng định dạng']);
        }
        if(empty($req['level_id'])){
            return \response()->json(['result' => 0, 'message' => 'Cấp thành viên không được bỏ trống']);
        }
        if(empty($req['expiration_date'])){
            return \response()->json(['result' => 0, 'message' => 'Ngày hết hạn không được bỏ trống']);
        }
        if(empty($req['address'])){
            return \response()->json(['result' => 0, 'message' => 'Địa chỉ không được bỏ trống']);
        }


        if(!empty($idEdit)){
            $check_id_card_number = InsuranceAgency::where('id','<>', $idEdit)
                                    ->where('id_card_number', $req["id_card_number"])
                                    ->first();
            $check_email = InsuranceAgency::where('insurance_agencies.id','<>', $idEdit)
                                    ->leftjoin('users as u', 'u.id', 'insurance_agencies.user_id')
                                    ->where('u.email', $req["email"])
                                    ->first();   
        }else {
            $check_id_card_number = InsuranceAgency::where('id_card_number', $req["id_card_number"])
                                    ->first();
            $check_email = InsuranceAgency::where('u.email', $req["email"])
                                    ->leftjoin('users as u', 'u.id', 'insurance_agencies.user_id')
                                    ->first();     
            $check_phone_agency = InsuranceAgency::where('phone', $req["phone"])
            ->first();                        
        }

        if(!empty($check_id_card_number)){
            return \response()->json(['result' => 0, 'message' => 'Số CMND đã tồn tại']);
        }
        if(!empty($check_email)){
            return \response()->json(['result' => 0, 'message' => 'Email đã tồn tại']);
        }
        if(!empty($check_phone_agency)){
            return \response()->json(['result' => 0, 'message' => 'Điện thoại đã tồn tại']);
        }
        $checkInherit_1 = false;
        $checkInherit_2 = false;
        if(!empty($req["name_inherit_1"]) || !empty($req["relationship_inherit_1"]) || !empty($req["phone_inherit_1"])){
            if(empty($req["name_inherit_1"])) {
                return \response()->json(['result' => 0, 'message' => 'Họ và tên người thừa kế không được bỏ trống']);
            }

            if(empty($req["relationship_inherit_1"])) {
                return \response()->json(['result' => 0, 'message' => 'Mối quan hệ không được bỏ trống']);
            }

            if(empty($req["phone_inherit_1"])) {
                return \response()->json(['result' => 0, 'message' => 'Số điện thoại không được bỏ trống']);
            }
            $checkInherit_1 = true;
        }

        if(!empty($req["name_inherit_2"]) || !empty($req["relationship_inherit_2"]) || !empty($req["phone_inherit_2"])){
            if(empty($req["name_inherit_2"])) {
                return \response()->json(['result' => 0, 'message' => 'Họ và tên người thừa kế không được bỏ trống']);
            }
            
            if(empty($req["relationship_inherit_2"])) {
                return \response()->json(['result' => 0, 'message' => 'Mối quan hệ không được bỏ trống']);
            }

            if(empty($req["phone_inherit_2"])) {
                return \response()->json(['result' => 0, 'message' => 'Số điện thoại không được bỏ trống']);
            }
            $checkInherit_2 = true;
        }

        if(empty($req['code_agency_parent'])){
            return \response()->json(['result' => 0, 'message' => 'Mã người giới thiệu không được bỏ trống']);
        }else {
            $agency_parent = InsuranceAgency::where('code_agency', $req['code_agency_parent'])->first();
            $level = $req["level_id"];
            if (!$agency_parent) {
                return \response()->json(['result' => 0, 'message' => 'Mã người giới thiệu không tồn tại']);
                
            }else if($level > $agency_parent->level_id){
                return \response()->json(['result' => 0, 'message' => 'Chỉ được giới thiệu thành viên ngang cấp']);
            }else if($level = $agency_parent->level_id){
                $parent = InsuranceAgency::where('id', $user->insuranceAgency->id)->first();

                if($agency_parent->parent_id != $parent->id && $user->insuranceAgency->id != $agency_parent->id){
                    return \response()->json(['result' => 0, 'message' => 'Người giới thiệu phải thuộc hệ thống của quản lý trực tiếp']);
                }
            }
        }

        DB::beginTransaction();
        try {
            $signature = !empty($idEdit) ? $dataEdit->signature : '';
            if (!$request->file('signature')) {
                if(empty($idEdit)){
                    return \response()->json(['result' => 0, 'message' => 'Ảnh chữ ký không được bỏ trống']);
                }
            }else {
                $file = $request->file('signature');
                $filename = 'chuky_'.uniqid().'_'.$req["id_card_number"].'.'.$file->getClientOriginalExtension();
                $signature = '/signature/' . $filename;
                Storage::disk('s3')->put($signature, file_get_contents($request->file('signature')));
            }
            $expiration_date = date("Y-m-d",strtotime(str_replace('/', '-', $req["expiration_date"])));
            $birthday = date("Y-m-d",strtotime(str_replace('/', '-', $req["birthday"])));
            $date_card_number = date("Y-m-d",strtotime(str_replace('/', '-', $req["date_card_number"])));
            if(!empty($idEdit)){
                $userNew = $dataEdit->user;
                $userNew->update([
                    'email' => $req["email"]
                ]);

                $agency_manager = $user->insuranceAgency->id;
                $agency = $dataEdit->update([
                    "id" => $idEdit,
                    "name" => mb_strtoupper($req["name"],'UTF-8'),
                    "office_id" => $agency_parent->office_id,
                    "level_id" => $req["level_id"],
                    "user_id" =>  $userNew->id,
                    "invite_id" =>  $agency_parent->id,
                    "parent_id" =>  $agency_manager,
                    'code_agency_invite' => $agency_parent->code_agency,
                    'id_card_number' => $req["id_card_number"],
                    'phone' => $req["phone"],
                    'sex' => $req["sex"],
                    'birthday' =>  $birthday,
                    'date_card_number' => $date_card_number,
                    'expiration_date' => $expiration_date,
                    'place_card_number' => $req["place_card_number"],
                    'address' => $req["address"],
                    'status_info' => $dataEdit->status_info,
                    'signature' => $signature,
                    'register_online' => 1,
                ]);
                InsuranceAgencyInherit::where('insurance_agency_id', $idEdit)->delete();
                $agency = InsuranceAgency::where('id', $idEdit)->first();
            }else {
                $userNew = new User();
                $userNew->id = !empty($dataEdit) ? $dataEdit->user_id : null;
                $userNew->group_id = 3;
                $userNew->password = config('core.password_default');
                $userNew->email = $req["email"];
                $userNew->status = config('core.user_status.confirm');
                $userNew->save();
                
                $agency_manager = $user->insuranceAgency->id;
                $agency = InsuranceAgency::create([
                    "id" => $idEdit,
                    "name" => mb_strtoupper($req["name"],'UTF-8'),
                    "office_id" => $agency_parent->office_id,
                    "level_id" => $req["level_id"],
                    "user_id" =>  $userNew->id,
                    "invite_id" =>  $agency_parent->id,
                    "parent_id" =>  $agency_manager,
                    'code_agency_invite' => $agency_parent->code_agency,
                    'id_card_number' => $req["id_card_number"],
                    'phone' => $req["phone"],
                    'sex' => $req["sex"],
                    'birthday' =>  $birthday,
                    'date_card_number' => $date_card_number,
                    'expiration_date' => $expiration_date,
                    'place_card_number' => $req["place_card_number"],
                    'address' => $req["address"],
                    'status_info' => 0,
                    'signature' => $signature,
                    'register_online' => 1,
                ]);
            }
            $inherit = [];
            if($checkInherit_1){
                array_push($inherit,[
                    'name' => $req["name_inherit_1"], 
                    'relationship' => $req["relationship_inherit_1"], 
                    'phone' => $req["phone_inherit_1"], 
                    'insurance_agency_id' => $agency->id
                ]);
            }
            if($checkInherit_2){
                array_push($inherit,[
                    'name' => $req["name_inherit_2"], 
                    'relationship' => $req["relationship_inherit_2"], 
                    'phone' => $req["phone_inherit_2"], 
                    'insurance_agency_id' => $agency->id
                ]);
            }

            if(!empty($inherit)){
                InsuranceAgencyInherit::insert($inherit);
            }

            $agency_manage = $user->insuranceAgency;
            $view = \View::make('api::pdf_forms/agency-register', [
                'data' => $req,
                'agency' => $agency,
                'agency_invite' => $agency_parent,
                'agency_parent' => $agency_manage,
                'signature' => $signature
            ]);
            
            $html = $view->render();
            PDF::Reset();
            PDF::SetTitle('Register PDF');
            PDF::SetFont('freeserif', '', 12.5, '', true);;
            PDF::AddPage();
            PDF::SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
            PDF::SetFooterMargin(PDF_MARGIN_FOOTER);
            // set auto page breaks
            PDF::SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
            PDF::writeHTML($html, true, false, true, false, '');

            $pdf_agency_register = '/pdfs/'.uniqid().'_pdf_register.pdf';
            PDF::output(public_path().$pdf_agency_register, 'F');

            $content_file_pdf_register = File::get(public_path().$pdf_agency_register);
            Storage::disk('s3')->put($pdf_agency_register, $content_file_pdf_register);
            File::delete(public_path($pdf_agency_register));
            
            $agency->update(['pdf_agency_register' => $pdf_agency_register ]);

            $checkLevel = 0;
            $checkLevel = ($agency->level_id >= 1 && $agency->level_id <= 2) ? 1 : (($agency->level_id >= 3 && $agency->level_id <= 6) ? 2 : 0);
            if(empty($idEdit)){
                $agencyRoot = InsuranceAgency::where('parent_id', 0)->first();
                if($agencyRoot){
                    if($checkLevel == 1){
                        Approve::create([
                            'insurance_agency_id' => $agency->id,
                            'approver' => $agencyRoot->id,
                            'register_online' => 1,
                            'status' => 1,
                            'date_approve' => date('Y-m-d H:i:s'),
                        ]);
                    }else if($checkLevel == 2){
                        Approve::create([
                            'insurance_agency_id' => $agency->id,
                            'approver' => $agencyRoot->id,
                            'register_online' => 1, 
                            'status' => 1,
                            'date_approve' => date('Y-m-d H:i:s'),
                            'status_offer' => 1,
                            'date_offer' => date('Y-m-d H:i:s'),
                        ]);

                        $pdf_offer = $this->sendOffer($agency);
                        InsuranceAgency::where('id', $agency->id)->update(['pdf_offer' => $pdf_offer]);

                    }else{
                        Approve::create([
                            'insurance_agency_id' => $agency->id,
                            'approver' => $agencyRoot->id,
                            'register_online' => 1 
                        ]);
                    }
                    
                }

                if($checkLevel == 1 || $checkLevel == 2) {
                    User::where('id', $userNew->id)->update(['status' => config('core.user_status.lock')]);
                    // MailQueue::saveMailToQueue([
                    //     'templete' => 'email.register',
                    //     'variable' => [
                    //         'agency_invite' => $agency_parent->name,
                    //         'agency_code' => '',
                    //         'name' => $agency->name,
                    //         'office' => !empty($agency->office) ? $agency->office->name : '',
                    //         'level' => $agency->level->name,
                    //         'email' => $userNew->email,
                    //         'password' => config('core.password_default'),
                    //     ],
                    //     'subject' => '['.env('APP_NAME').'] Tạo tài khoản thành viên thành công',
                    //     'user_id' => $agency->user_id,
                    // ]);
                }

                // MailQueue::saveMailToQueue([
                //     'templete' => 'email.register-online',
                //     'variable' => [
                //         'data' => $agency,
                //     ],
                //     'subject' => '['.env('APP_NAME').'] Tạo đơn đăng ký thành viên thành công',
                //     'user_id' => $agency->user_id,
                // ]);

                // MailQueue::saveMailToQueue([
                //     'templete' => 'email.register-online',
                //     'variable' => [
                //         'data' => $agency,
                //     ],
                //     'subject' => '['.env('APP_NAME').'] Tạo đơn đăng ký thành viên thành công',
                //     'user_id' => $agency_parent->user_id,
                // ]);

                // MailQueue::SendMailNow([
                //     'templete' => 'email.register-online',
                //     'variable' => [
                //         'data' => $agency,
                //     ],
                //     'subject' => '['.env('APP_NAME').'] Tạo đơn đăng ký thành viên thành công',
                //     'to' => ['hethong@medici.vn'],
                // ]);
            }
            
            DB::commit();
            return \response()->json(['result'=>1, 'data'=>'success']);
        } catch (\Exception $e) {
            DB::rollback();
            Log::alert($e);
            return \response()->json([
                'result'       => 0,
                'current_time' => time(),
                'message'      => $e->getMessage(),
                'data'         => null
            ]);
        }
    }

    // Gửi lời đề nghị
    public function sendOffer($agency){
        $level = $agency->level_id;
        $parentData = InsuranceAgency::where('id', $agency->parent_id)->first();
        $approve = Approve::where('insurance_agency_id', $agency->id)->first();
        $parentSex = $parentData->sex == 1 ? 'Anh' : 'Chị';
        $form = 'offer-pdf-level-'.$level;
        $date = $approve->date_approve;
        $view = \View::make('api::pdf_forms/'.$form, [
            'name' => $agency->name,
            'id_card_number' => $agency->id_card_number,
            'sex' => $agency->sex == 1 ? 'Anh' : 'Chị',
            'level' => $agency->level->name .' ('.$agency->level->code.') - Cấp '.$agency->level->level,
            'level_2' => $agency->level->name.' - '.$agency->level->code,
            'parent' => $parentSex.' '.$parentData->name.' - '.$parentData->level->name.' - Cấp ' . $parentData->level->level,
            'expire' => date('d/m/Y', strtotime($approve->date_approve.'+15 day')),
            'date_approve' => date('d/m/Y', strtotime($approve->date_approve))
        ]);
        
        $html = $view->render();
        PDF::Reset();
        PDF::SetTitle('Offer PDF');    
        PDF::SetFont('freeserif', '', 12.5, '', true);;
        PDF::AddPage();
        PDF::SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        PDF::SetFooterMargin(PDF_MARGIN_FOOTER);
        PDF::SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        PDF::writeHTML($html, true, false, true, false, '');
        $pdf_offer = '/pdfs/'.uniqid().'_pdf_offer_level_'.$level.'.pdf';
        PDF::output(public_path().$pdf_offer, 'F');

        $content_file_pdf_register = File::get(public_path().$pdf_offer);
        Storage::disk('s3')->put($pdf_offer, $content_file_pdf_register);
        File::delete(public_path($pdf_offer));

        // MailQueue::saveMailToQueue([
        //     'templete' => 'email.send-offer',
        //     'variable' => [
        //         'name' => $agency->name,
        //         'sex' => $agency->sex == 1 ? 'Anh' : 'Chị',
        //         'pdf_offer' => env('LINK_HOMEPAGE').$pdf_offer,
        //         'level' => $level
        //     ],
        //     'subject' => '['.env('APP_NAME').'] Thư mời hợp tác kinh doanh',
        //     'user_id' => $agency->user_id,
        // ]);

        return $pdf_offer;

    }

    // Danh sách bảng lương theo chu kỳ
    public function getPayrolls(Request $request) 
    {   
        try {
            $params = $request->all();

            if(empty($params['user_id'])){
                return \response()->json(['result' => 0, 'message' => 'ID đại lý không được trống']);
            }

            if(empty($params['month_year'])){
                return \response()->json(['result' => 0, 'message' => 'Tháng/năm không được trống']);
            }

            if(empty($params['cycle'])){
                return \response()->json(['result' => 0, 'message' => 'Chu kỳ không được trống']);
            }

            $revenue_cycle = 'Tháng '. $params['month_year'] .' - Chu kỳ '. $params['cycle'];
            $end_last_month = new Carbon('last day of last month');
            $end_last_month = $end_last_month->toDateString();
            $explode = explode('/', $params['month_year']);
            $month = $explode[0];
            $year = $explode[1];

            $income = RevenueDaily::select(
                "insurance_agencies.id as insurance_agency_id",
                "insurance_agencies.code_agency as code_agency",
                "insurance_agencies.name as name",
                "levels.code as level",
                "revenue_dailys.revenue_cycle",
                "revenue_dailys.status",
                // DT cá nhân
                // DB::raw("SUM(personal_revenue) as total_personal_revenue"),
                // Thu nhập cá nhân trước thuế
                DB::raw("SUM(personal_income_before_tax) as total_personal_income_before_tax"),
                // Thu nhập hệ thống trước thuế
                DB::raw("SUM(branch_income_before_tax) as total_branch_income_before_tax"),
                // Thu nhập đồng cấp
                DB::raw("SUM(peer_income_before_tax) as total_peer_income_before_tax"),
                // Thu nhập cá nhân sau thuế
                // DB::raw("SUM(personal_income_after_tax) as total_personal_income_after_tax"),
                // Thu nhập hệ thống sau thuế
                // DB::raw("SUM(branch_income_after_tax) as total_branch_income_after_tax"),
                // Doanh thu đồng cấp
                // DB::raw("SUM(peer_revenue) as total_peer_revenue"),
                // Tổng doanh thu
                // DB::raw("SUM(personal_revenue + branch_revenue + peer_revenue) as total_revenue"),
                // Doanh thu hệ thống
                // DB::raw("SUM(branch_revenue) as total_branch_revenue"),
                // Tổng thu nhập
                DB::raw("SUM(personal_income_before_tax + branch_income_before_tax + peer_income_before_tax) as total_income_before_tax"),
                // Tổng thu nhập sau thuế
                DB::raw("SUM(personal_income_after_tax + branch_income_after_tax + peer_income_after_tax) as total_income_after_tax"),
                // Thuế
                DB::raw("SUM(personal_income_before_tax + branch_income_before_tax + peer_income_before_tax - personal_income_after_tax - branch_income_after_tax - peer_income_after_tax) as tax")
            )
            ->leftjoin('insurance_agencies', 'insurance_agencies.id', 'revenue_dailys.insurance_agency_id')
            ->leftjoin('levels', 'levels.level', 'revenue_dailys.level_id')
            ->groupBy("insurance_agency_id")
            ->groupBy("revenue_cycle")
            ->orderBy('revenue_dailys.level_id', 'desc')
            ->orderBy('insurance_agencies.id', 'asc')
            ->where('revenue_dailys.is_life_insurance_contract', 1)
            ->where('revenue_dailys.revenue_cycle', $revenue_cycle)
            ->where('revenue_dailys.insurance_agency_id', $params['user_id'])
            ->orderBy('revenue_dailys.revenue_cycle','desc')->orderBy('revenue_dailys.level_id','desc')->first();

            $agency_startup_support = $params['cycle'] == 2 ? AgencyStartupSupport::select("agency_startup_support.cash_received")
                ->join('insurance_agencies as i', 'i.id', 'agency_startup_support.insurance_agency_id')
                ->where('appoint_date', '<=', $end_last_month)
                ->where('insurance_agency_id', $params['user_id'])
                ->where('start_date', date($year.'-'.$month.'-01'))->first() : null;
            
            $support_office = $params['cycle'] == 2 ? 
                OfficeFee::select(DB::raw("(SUM(fee) + SUM(fee_by_child)) as total_fee"))
                    ->where('insurance_agency_id', $params['user_id'])
                    ->where('date', $year.'-'.$month)
                    ->groupBy("insurance_agency_id")
                    ->first()
                : null;

            $data = [
                'income' => $income,
                'co_benefit_income' => 0,
                'startup_support' => $agency_startup_support,
                'support_office' => $support_office,
            ];
        } catch (\Exception $e) {
            $data = ['error_msg' => $e->getMessage()];
        }

        return $this->apiResponse($data, null, 1);
    }

    public function getInfoBank(Request $request)
    {
        DB::beginTransaction();
        try {
            $params = $request->all();   

            if(empty($params['insurance_agency_id'])){
                return \response()->json(['result' => 0, 'message' => 'ID đại lý không được trống']);
            }

            $data = InsuranceAgency::select('bank_account_number', 'bank_account_name', 'banks.id as bank_id', 'banks.bank_name')
            ->join('banks', 'banks.id', '=', 'insurance_agencies.bank_id')
            ->find($params['insurance_agency_id'])
            ->toArray();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            Log::alert($e);
            return \response()->json([
                'result'       => 0,
                'current_time' => time(),
                'message'      => $e->getMessage(),
                'data'         => null
            ]);
        }
        
        return $this->apiResponse($data);
    }

    public function updateInfoBank(Request $request)
    {
        DB::beginTransaction();
        try {
            $params = $request->all();   

            if(empty($params['insurance_agency_id'])){
                return \response()->json(['result' => 0, 'message' => 'ID đại lý không được trống']);
            }

            $data = InsuranceAgency::find($params['insurance_agency_id']);

            if(isset($params['bank_account_number'])){
                $data->bank_account_number = $params['bank_account_number'];
            }

            if(isset($params['bank_account_name'])){
                $data->bank_account_name = $params['bank_account_name'];
            }

            if(isset($params['bank_id'])){
                $data->bank_id = $params['bank_id'];
            }

            $data->save();

            DB::commit();

            return \response()->json([
                'result'       => 1,
                'current_time' => time(),
                'message'      => 'Cập nhật thông tin ngân hàng thành công!',
                'data'         => $data
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::alert($e);
            return \response()->json([
                'result'       => 0,
                'current_time' => time(),
                'message'      => $e->getMessage(),
                'data'         => null
            ]);
        }
    }

    // Chi tiết đại lý 
    public function getProfile(Request $request)
    {
        DB::beginTransaction();
        try {
            $params = $request->all();   

            if(empty($params['insurance_agency_id'])){
                return \response()->json(['result' => 0, 'message' => 'ID đại lý không được trống']);
            }

            $data = InsuranceAgency::select(
                'insurance_agencies.name',
                'phone',
                'insurance_agencies.avatar',
                'insurance_agencies.status',
                'code_agency',
                'birthday',
                'sex',
                'users.email',
                'address',
                'id_card_number',
                'date_card_number',
                'expiration_date',
                'levels.name as level_name',
                'levels.code as level_code',
                'a.date_approve'
            )
            ->join('users', 'users.id', '=', 'insurance_agencies.user_id')
            ->join('levels', 'levels.id', '=', 'insurance_agencies.level_id')
            ->leftjoin('approves as a', 'a.insurance_agency_id', 'insurance_agencies.id')
            ->find($params['insurance_agency_id']);

            DB::commit();

            return $this->apiResponse($data);
        } catch (\Exception $e) {
            DB::rollback();
            Log::alert($e);
            return \response()->json([
                'result'       => 0,
                'current_time' => time(),
                'message'      => $e->getMessage(),
                'data'         => null
            ]);
        }
    }

    // Update thông tin cá nhân
    public function updateProfile(Request $request)
    {
        DB::beginTransaction();
        try {
            $params = $request->all();   

            if(empty($params['insurance_agency_id'])){
                return \response()->json(['result' => 0, 'message' => 'ID đại lý không được trống']);
            }

            if(empty($params['name'])){
                return \response()->json(['result' => 0, 'message' => 'Tên đại lý không được trống']);
            }

            if(empty($params['phone'])){
                return \response()->json(['result' => 0, 'message' => 'Số điện thoại không được trống']);
            }

            if(empty($params['birthday'])){
                return \response()->json(['result' => 0, 'message' => 'Ngày sinh không được trống']);
            }

            if(empty($params['sex'])){
                return \response()->json(['result' => 0, 'message' => 'Giới tính không được trống']);
            }

            if(empty($params['email'])){
                return \response()->json(['result' => 0, 'message' => 'Email không được trống']);
            }

            if(!filter_var($params['email'], FILTER_VALIDATE_EMAIL)){
                return \response()->json(['result' => 0, 'message' => 'Email không đúng định dạng']);
            }

            if(empty($params['address'])){
                return \response()->json(['result' => 0, 'message' => 'Địa chỉ không được trống']);
            }

            if(empty($params['id_card_number'])){
                return \response()->json(['result' => 0, 'message' => 'Số CMND/CCCD không được trống']);
            }

            if(empty($params['date_card_number'])){
                return \response()->json(['result' => 0, 'message' => 'Ngày cấp không được trống']);
            }

            if(empty($params['expiration_date'])){
                return \response()->json(['result' => 0, 'message' => 'Ngày hết hạn không được trống']);
            }

            $data = InsuranceAgency::find($params['insurance_agency_id']);

            $check_email = User::where('id', '!=', $data->user_id)->where('email', $params['email'])->exists();

            $check_phone_agency = InsuranceAgency::where('id', '!=', $params['insurance_agency_id'])->where('phone', $params["phone"])->exists();
            
            $check_id_card_number = InsuranceAgency::where('id', '!=', $params['insurance_agency_id'])->where('id_card_number', $params["id_card_number"])->exists();

            if($check_email){
                return \response()->json(['result' => 0, 'message' => 'Email đã tồn tại']);
            }

            if($check_phone_agency){
                return \response()->json(['result' => 0, 'message' => 'Điện thoại đã tồn tại']);
            }   
            
            if($check_id_card_number){
                return \response()->json(['result' => 0, 'message' => 'Số CMND/CCCD đã tồn tại']);
            }   
            
            $data->update([
                'name' => $params['name'],
                'phone' => $params['phone'],
                'birthday' => date("Y-m-d",strtotime(str_replace('/', '-', $params["birthday"]))),
                'sex' => $params['sex'],
                'address' => $params['address'],
                'id_card_number' => $params['id_card_number'],
                'date_card_number' => date("Y-m-d",strtotime(str_replace('/', '-', $params['date_card_number']))),
                'expiration_date' => date("Y-m-d",strtotime(str_replace('/', '-', $params['expiration_date']))),
            ]);

            $data->user->update([
                'email' => $params['email']
            ]);

            DB::commit();

            return \response()->json([
                'result'       => 1,
                'current_time' => time(),
                'message'      => 'Cập nhật thông tin thành công!',
                'data'         => $data
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::alert($e);
            return \response()->json([
                'result'       => 0,
                'current_time' => time(),
                'message'      => $e->getMessage(),
                'data'         => null
            ]);
        }
    }

    // Lấy link Cây hệ thống
    public function systemTree(Request $request)
    {
        try {
            $params = $request->all();   

            if(empty($params['user_id'])){
                return \response()->json(['result' => 0, 'message' => 'ID người dùng không được trống']);
            }

            $user = User::where('group_id', 3)->find($params['user_id']);
            if(empty($user)){
                return \response()->json(['result' => 0, 'message' => 'Người dùng không tồn tại trong hệ thống']);
            }

            $data = [
                'link' => url('/api/v1/agency/view-system-tree?user_id=').$params["user_id"]
            ];
        } catch (\Exception $e) {
            $data = [
                'result' => 0,
                'message' => $e->getMessage(),
            ];
        }

        return $this->apiResponse($data, null, 1);
    }

    public function checkUpdateInfoAgency($agency) {
        if($agency->status_info == 0){
            return 0;
        }
        return 1;
    }

    // Dánh sách đại lý con f1
    public function getAgencyChildList(Request $request){
        try {
            $params = $request->all();   

            if(empty($params['insurance_agency_id'])){
                return \response()->json(['result' => 0, 'message' => 'ID đại lý không được trống']);
            }

            $parents = InsuranceAgency::where('parent_id', $params['insurance_agency_id'])->get();
            $dataList = [];
            if(!empty($parents)){
                foreach($parents as $parent) {
                    if($parent->user->status != 3 && $parent->user->status != 5 && !empty($parent->code_agency)){
                        $count_child = \DB::table('insurance_agencies as agency')
                        ->where('agency.parent_id', $parent['id'])
                        ->whereNotNull('agency.code_agency')
                        ->whereNotIn('u.status', [3,5])
                        ->leftjoin('users as u', 'agency.user_id', 'u.id')
                        ->count();
                        array_push($dataList,[
                            'id' => $parent->id,
                            'parent_id' => $parent->parent_id,
                            'code_agency' => $parent->code_agency,
                            'name' => $parent->name,
                            'level_code' => $parent->level->code,
                            'count_child' => $count_child,
                            'check_update_info_agency'=> $this->checkUpdateInfoAgency($parent)
                        ]);
                    }
                }
            }

            $data = $dataList;
        } catch (\Exception $e) {
            $data = [
                'result' => 0,
                'message' => $e->getMessage(),
            ];
        }

        return $this->apiResponse($data, null, 1);
    }

    // View cây hệ thống
    public function viewSystemTree(Request $request)
    {
        $params = $request->all();

        if(empty($params['user_id'])){
            return \response()->json(['result' => 0, 'message' => 'ID tài khoản không được trống']);
        }

        $user = User::where('group_id', 3)->find($params['user_id']);

        $data = $user->insuranceAgency;
        $agencyParent = InsuranceAgency::where('id', $data->parent_id)->first();
        if(!empty($agencyParent)){
             $dataList = [
                'id' => $agencyParent->id,
                'parent_id' => $agencyParent->parent_id,
                'code_agency' => $agencyParent->code_agency,
                'name' => $agencyParent->name,
                'level_code' => ($agencyParent->parent_id != 0) ? $agencyParent->level->code : '',
                'check_update_info_agency'=> ($agencyParent->parent_id != 0) ? $this->checkUpdateInfoAgency($agencyParent) : 1,
                'childs' => [
                    [
                        'id' => $data->id,
                        'parent_id' => $data->parent_id,
                        'code_agency' => $data->code_agency,
                        'name' => $data->name,
                        'level_code' => $data->level->code,
                        'childs' => [],
                        'check_update_info_agency'=> $this->checkUpdateInfoAgency($data)
                    ]
                ]
            ];
            $parent_lv3 = InsuranceAgency::where('parent_id', $data['id'])->get();
            if(!empty($parent_lv3)){
                foreach($parent_lv3 as $lv3) {
                    if($lv3->user->status  != 3 && $lv3->user->status != 5 && !empty($lv3->code_agency)){
                        $count_child = \DB::table('insurance_agencies as agency')
                        ->where('agency.parent_id', $lv3['id'])
                        ->whereNotNull('agency.code_agency')
                        ->whereNotIn('u.status', [3,5])
                        ->leftjoin('users as u', 'agency.user_id', 'u.id')
                        ->count();
                        array_push($dataList['childs'][0]['childs'],[
                            'id' => $lv3->id,
                            'parent_id' => $lv3->parent_id,
                            'code_agency' => $lv3->code_agency,
                            'name' => $lv3->name,
                            'level_code' => $lv3->level->code,
                            'count_child' => $count_child,
                            'check_update_info_agency'=> $this->checkUpdateInfoAgency($lv3)
                        ]);
                    }
                }
            }
        }

        return view('api::agency/system-tree', [
            "dataList" => $dataList,
            "user" => $user
        ]);
    }

    // Lấy danh sách đại lý F1 (Dùng cho view cây hệ thống)
    public function postAgencyChildList(Request $request){
        $parents = InsuranceAgency::where('parent_id', $request->id)->get();
        $dataList = [];
        if(!empty($parents)){
            foreach($parents as $parent) {
                if($parent->user->status != 3 && $parent->user->status != 5 && !empty($parent->code_agency)){
                    $count_child = \DB::table('insurance_agencies as agency')
                    ->where('agency.parent_id', $parent['id'])
                    ->whereNotNull('agency.code_agency')
                    ->whereNotIn('u.status', [3,5])
                    ->leftjoin('users as u', 'agency.user_id', 'u.id')
                    ->count();
                    array_push($dataList,[
                        'id' => $parent->id,
                        'parent_id' => $parent->parent_id,
                        'code_agency' => $parent->code_agency,
                        'name' => $parent->name,
                        'level_code' => $parent->level->code,
                        'count_child' => $count_child,
                        'check_update_info_agency'=> $this->checkUpdateInfoAgency($parent)
                    ]);
                }
            }
        }
        return \response()->json([
            'status'=> 1,
            'data' => $dataList,
        ]);
    }
    
    // Chi tiết đại lý theo mã đại lý
    public function getAgencyDetailByCode(Request $request)
    {
        try {
            $params = $request->all();

            $agency = InsuranceAgency::select(
                'insurance_agencies.id as agency_id',
                'insurance_agencies.code_agency',
                'insurance_agencies.name as insurance_agency_name',
                'insurance_agencies.level_id',
                'levels.name as level_name',
                'insurance_agencies.id_card_number',
                'insurance_agencies.date_card_number',
                'insurance_agencies.place_card_number',
                'insurance_agencies.birthday',
                'insurance_agencies.phone',
                DB::raw("(CASE WHEN insurance_agencies.sex = 1 
                            THEN 'Nam' 
                            ELSE 'Nữ' 
                          END) AS sex"),
                'u.email as email',
                'insurance_agencies.address',
                'insurance_agencies.status',
                'insurance_agencies.status_info',
                'insurance_agencies.code_agency_invite',
                'insurance_agencies.province_id',
                'insurance_agencies.district_id',
                'insurance_agencies.ward_id',
                'insurance_agencies.bank_account_number',
                'insurance_agencies.bank_account_name',
                'insurance_agencies.bank_id',
                'insurance_agencies.created_at'
            )
            ->where('insurance_agencies.code_agency', $params['code_agency'])
            ->leftjoin('levels', 'levels.id', 'insurance_agencies.level_id')
            ->leftjoin('users as u', 'u.id', 'insurance_agencies.user_id')
            ->first()
            ->toArray();

            $data = $agency;

        } catch (\Exception $e) {
            $data = ['error_msg' => $e->getMessage()];
        }
        return $this->apiResponse($data);
    }

    // Chi tiết thông tin đại lý bảo hiểm (FWD/CATHAY)
    public function getInsuranceAgencyDetailByCode(Request $request)
    {
        try {
            $params = $request->all();

            $agency = AgencyInfoFwd::select('agency_info_fwd.*')
            ->where('agency_info_fwd.code_agency_official', $params['code_agency_official'])
            ->first();

            if($agency != null)
            {
                $agency = $agency->toArray();
            }

            $data = $agency;

        } catch (\Exception $e) {
            $data = ['error_msg' => $e->getMessage()];
        }
        return $this->apiResponse($data);
    }

    public function test(Request $request)
    {
        // dd(config('config.max_get_file_times'));

        // $fileUrl = $request->link;
        // $content_file_gcn = file_get_contents($fileUrl);
        // dd($content_file_gcn);


        // $pdf_test = '/pdfs/'.uniqid().'test.pdf';

        // Storage::disk('local')->put($pdf_test, $content_file_gcn);
        // dd('Image Successfully Saved');



        
    }
}
