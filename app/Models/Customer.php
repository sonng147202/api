<?php

namespace App\Models;

use App\Mail\CustomerResetPassword;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Models\OauthAccessToken;
//use Modules\Core\Models\User;
use App\Models\MailQueue;

class Customer extends Model
{
    use SoftDeletes, Notifiable;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];
    protected $fillable = [
        'type_id', 'email', 'name', 'phone_number', 'code_customer', 'image_id', 'avatar', 'source', 'classify', 
        'date_of_birth', 'sex', 'age', 'facebook', 'zalo', 'password', 'password_yolo', 'id_card_number', 'address', 
        'ward_id', 'district_id', 'province_id', 'invitation_code', 'customer_manager_id', 'insurance_agency_id', 'is_vip', 
        'status', 'used_amount', 'point', 'active_code', 'raw_password_vpbank', 'updated_by', 'updated_at', 'customer_id',        
        'created_by', 'created_at', 'deleted_at', 'insurance_demand','date_card_number','place_card_number','agency_info_fwd_id'
    ];

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    const STATUS_DELETED = -1;

    const MALE = 0;
    const FEMALE = 1;

    const CUSTOMER_SOURCE_UNKNOWN = -1;
    const CUSTOMER_SOURCE_FACEBOOK = 0;
    const CUSTOMER_SOURCE_EMAIL = 1;
    const CUSTOMER_SOURCE_CLIENT = 2;
    const CUSTOMER_SOURCE_BUYER_CHANNEL = 3;
    const CUSTOMER_SOURCE_GOOGLE_ADS = 4;
    const CUSTOMER_SOURCE_TELESALES = 5;
    const CUSTOMER_SOURCE_AGENCY = 6;
    const CUSTOMER_SOURCE_WEBSITE = 7;
    const CUSTOMER_SOURCE_HOTLINE = 8;
    const CUSTOMER_SOURCE_OTHER = 9;

    public static $sourceText = [
        self::CUSTOMER_SOURCE_UNKNOWN => 'Chưa rõ',
        self::CUSTOMER_SOURCE_FACEBOOK => 'Facebook',
        self::CUSTOMER_SOURCE_EMAIL => 'Email marketing',
        self::CUSTOMER_SOURCE_CLIENT => 'Khách hàng giới thiệu',
        self::CUSTOMER_SOURCE_BUYER_CHANNEL => 'Kênh bán',
        self::CUSTOMER_SOURCE_GOOGLE_ADS => 'Google Ads',
        self::CUSTOMER_SOURCE_TELESALES => 'Telesales',
        self::CUSTOMER_SOURCE_AGENCY => 'Đại lý',
        self::CUSTOMER_SOURCE_WEBSITE => 'Website',
        self::CUSTOMER_SOURCE_HOTLINE => 'Hotline',
        self::CUSTOMER_SOURCE_OTHER => 'Khác'
    ];

    const CLASSIFY_OPPORTUNITY = 0;
    const CLASSIFY_POTENTIAL = 1;
    const CLASSIFY_BUYER = 2;
    const CLASSIFY_CONTINUE = 3;
    const CLASSIFY_PRIORITY = 4;

    public static $classifyText = [
        self::CLASSIFY_OPPORTUNITY => 'Cơ hội',
        self::CLASSIFY_POTENTIAL => 'Tiềm năng',
        self::CLASSIFY_PRIORITY => 'Ưu tiên',
        self::CLASSIFY_BUYER => 'Mua hàng',
        self::CLASSIFY_CONTINUE => 'Tái tục'
    ];

    const ACTIVE = 1;
    const DISABLE = 0;
    const IS_DELETED = -1;

    public static $customerStatusText = [
        self::ACTIVE => 'Kích hoạt',
        self::DISABLE => 'Không sử dụng',
        self::IS_DELETED => 'Đã xóa'
    ];

    const AVATAR_TYPE_USER = 1;
    const AVATAR_TYPE_CUSTOMER = 0;

    public function getStatusName() {
        if ($this->status == Customer::STATUS_ACTIVE)
            return "Đang kích hoạt";
        elseif ($this->status == Customer::STATUS_INACTIVE)
            return "Không sử dụng";
        else
            return "Đã xóa";
    }

    public function customerWallet()
    {
        return $this->hasOne('App\Models\CustomerWallet', 'id_customer');
    }
    public function customerWalletExchange()
    {
        return $this->hasMany('App\Models\CustomerWalletExchange', 'id_customer');
    }

    public static function searchByCondition($params, $id = null) {
        $p = Customer::with('customer_type', 'customerWallet');
        if (!empty($params["name"])) {
            $p = $p->where('name', 'like', '%'.$params["name"].'%');
        }
        
        if (!empty($params["email"])) {
            $p = $p->where('email', 'like', '%'.$params["email"].'%');
        }
        if (!empty($params["phone_number"])) {
            $p = $p->where('phone_number', 'like', '%'.$params["phone_number"].'%');
        }
        if (isset($params["id_card_number"])){
            $p = $p->where('id_card_number', 'like', '%'.$params["id_card_number"].'%');
        }
        if (isset($params["status"]) && in_array($params["status"], [-1, 0, 1])) {
            $p = $p->where('status', $params["status"]);
        }
        if (isset($params["start"])) {
            $p = $p->whereBetween('created_at',[$params['start'], $params['end']]);
        }

        if (isset($params['classify']) && $params['classify'] != -1) {
            $p = $p->where('classify', $params['classify']);
        }
        if (!empty($params["user_id"])) {
            $p = $p->where('created_by', $params["user_id"]);
        }

        if (!empty($params['start_date']) ) {
            //$contract_all->where('start_time','>=',$startDate);
            $startDate = Carbon::createFromFormat('d/m/Y',$params['start_date'])->toDateString();
            if (empty($params['end_date'])) {
                $endDate = Carbon::now()->toDateString();
            } else {
                $endDate = Carbon::createFromFormat('d/m/Y',$params['end_date'])->toDateString();
            }
            //$contract_all->whereBetween('created_at',[$startDate, $endDate]);
            $p->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate);
        }

        if(!empty($params['export']) && $params['export'] == 1){
            return $p->orderBy('created_at', 'desc')->get();
        }
//        return $p->orderBy('created_at', 'desc')->paginate(10);
        if(!empty($id))
        {   
            return $p->where('insurance_agency_id', $id)->orderBy('created_at', 'desc')->paginate(50);
        }else{
            return $p->orderBy('created_at', 'desc')->paginate(50);
        }
       
    }

    public function setPasswordAttribute($pass)
    {
        $this->attributes['password'] = Hash::make($pass);
    }
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */


    public function getCodeCustomer(){
        do{
            $rand = $this->generateRandomString(6);
        }while(!empty($this->where('code_customer',$rand)->first()));
        return $rand;
    }

    protected $hidden = [];

    /**
     * The relationship
     */
    public function type_support_customer_image()
    {
        return $this->hasMany('App\Models\TypeSupportCustemerImage', 'customer_id');
    }

    public function customer_type()
    {
        return $this->belongsTo('App\Models\CustomerType', 'type_id');
    }

    public function invites()
    {
        return $this->hasMany('App\Models\CustomerInvite', 'invite_id');
    }

    public function invited_customers()
    {
        return $this->hasManyThrough(
            'App\Models\Customer','App\Models\CustomerInvite',
            'invite_id', 'id'
        );
    }

    /**
     * Get detail customer
     *
     * @param $id
     * @return Model|null|static
     */
    public static function getDetail($id)
    {
        return self::where('id', $id)->first();
    }

    /**
     * @param array $filterData
     * @param int $page
     * @param int $pageSize
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public static function getList($filterData = [], $page = 1, $pageSize = 50)
    {
        $query = self::select(['id', 'email', 'name', 'phone_number', 'code_customer', 'id_card_number']);

        if (isset($filterData['user_id']) && !empty($filterData['user_id'])) {
            $query->where('customer_manager_id', $filterData['user_id']);
        }

        if (isset($filterData['search_key']) && !empty($filterData['search_key'])) {
            $query->where(function ($query) use ($filterData) {
                $query->where('name', $filterData['search_key'])
                    ->orWhere('email', $filterData['search_key']);
            });
        }

        // Get active list
        $query->where('status', '>', self::STATUS_DELETED);

        return $query->offset(($page - 1) * $pageSize)->limit($pageSize)->get();
    }

    /**
     * @param $customerId
     * @param int $managerId
     * @return bool
     */
    public static function isExists($customerId, $managerId = 0)
    {
        $query = self::where('id', $customerId);

        if ($managerId > 0) {
            $query->where('customer_manager_id', $managerId);
        }

        return $query->exists();
    }

    /**
     * @param $customerId
     * @param $amount
     * @return int
     */
    public static function incrementUsedAmount($customerId, $amount)
    {
        return self::where('id', $customerId)->increment('used_amount', $amount);
    }

    public function manager()
    {
        return $this->belongsTo('Modules\Core\Models\User','customer_manager_id');
    }

    public function routeNotificationForESms()
    {
        return $this->phone_number;
    }

    /**
     * Get row
     * @param $row
     * @return array
     */
    public static function getRow($row){
        return array(
            'id'
        );
    }

    /**
     * Get customer devices for push notify
     * @return array
     */
    public function routeNotificationForPushMobile()
    {
        // Get all device for this customer
        return OauthAccessToken::getByCustomer($this->id);
    }

    protected $dates = ['deleted_at'];

    /**
     * count page
     * input: total pages, current page
     */
    public static function countPage($totalPages, $currentPage)
    {
        if ($totalPages <= 10) {
            // less than 10 total pages so show all
            $startPage = 1;
            $endPage = $totalPages;
        } else {
            // more than 10 total pages so calculate start and end pages
            if ($currentPage <= 6) {
                $startPage = 1;
                $endPage = 10;
            } elseif ($currentPage + 4 >= $totalPages) {
                $startPage = $totalPages - 9;
                $endPage = $totalPages;
            } else {
                $startPage = $currentPage - 5;
                $endPage = $currentPage + 4;
            }
        }
        $pages = [];
        for ($i = $startPage; $i<$endPage + 1; $i++) {
            array_push($pages, $i);
        }
        return ['pages'=>$pages];
    }

    /**
     * Get pagination for customer : contract, quotation, activity, comment
     */
    public static function getPagination($query, $request)
    {
        $count = $query->count();
        empty($request['limit']) ? $limit = 10 : $limit = $request['limit'];
        empty($request['page']) ? $page = 1 : $page = $request['page'];
        $offSet = ($limit * $page) - $limit;
        $data = $query->offset($offSet)->limit($limit)->get();
        return ['data'=>$data, 'count'=>$count];
    }

    /**
     * Get list contract
     */
    public static function getListContract($request)
    {
        $query = InsuranceContract::where('customer_id', $request['customer_id']);
        return self::getPagination($query, $request);
    }

    /**
     * Get list quotation
     */
    public static function getListQuotation($request)
    {
        $query = InsuranceQuotation::where('customer_id', $request['customer_id']);
        return self::getPagination($query, $request);
    }

    /**
     * Get list activity
     */
    public static function getListActivity($request)
    {
        $query = CustomerActivity::where('customer_id', $request['customer_id'])->orderBy('id','DESC');
        return self::getPagination($query, $request);
    }

    /**
     * Get list comment
     */
    public static function getListComment($request)
    {
        $query = CustomerComment::where('customer_id', $request['customer_id'])->orderBy('id','DESC');
        return self::getPagination($query, $request);
    }

    /**
     * Create comment
     */
    public static function createComment($data)
    {
        $user = Auth::user();
        $comment = new CustomerComment();
        $comment->content = $data['content'];
        $comment->author_id = $user->id;
        $comment->customer_id = $data['customer_id'];
        $comment->save();
    }

    /**
     * Change avatar for user and customer
     */
    public static function changeAvatar($data, $avatarJson, $img_code_after, $img_code_before)
    {
        $obj = Customer::find($data['id']);
        $obj->avatar = $avatarJson;
        if(isset($data['img_code_after'])){
            $obj->img_code_after = $img_code_after;
        }
        if(isset($data['img_code_before'])){
            $obj->img_code_before = $img_code_before;
        }
        $obj->save();
    }

    /**
     * Change password of user and customer
     */
    public static function changePassword($params, $object)
    {
        if ($object != null) {
            $oldPasswordToCheck = $object->password;
//            if (Hash::check($params['old_password'], $oldPasswordToCheck)) {
                $object->password = $params['password'];
                $object->save();

                if(!empty($object->email)) {
                    $dataSend = [
                        'send_to' => [$object->email],
                        'sender' =>  env('MAIL_FROM_NAME').' <'.env('MAIL_FROM_ADDRESS').'>',
                        'subject' => (new CustomerResetPassword($object))->subjectEmail(),
                        'variable' => [
                            'data' => [
                                'name' => $object->name,
                                'phone_number' => $object->phone_number,
                                'email' => $object->email,
                                'password' => $params['password'],
                            ],
                        ],
                        'templete' => 'mail.customer_reset_password'
                    ];
                    MailQueue::SendMailNow($dataSend);
                }

                return ['result'=>1, 'error_msg'=>''];
//            } else {
//                return ['result'=>0, 'error_msg'=>'mật khẩu cũ không đúng'];
//            }
        } else {
            return ['result'=>0, 'error_msg'=>'người dùng không tồn tại'];
        }
    }
}
