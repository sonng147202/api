<?php

namespace App\Lib\InsuranceServices\VBI\V2;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Jobs\GetFile;
use App\Lib\InsuranceServices\VBI\Interfaces\ServiceDriverInterface;
use App\Models\CarBrand;
use App\Models\CarModel;
use App\Models\CarModelTrim;
use App\Models\InsuranceContract;
use App\Models\InsuranceContractBeneficiary;
use Modules\Product\Models\Product;
use File;

class VBIDriver implements ServiceDriverInterface
{
    public $id; // id InsuranceContract
    public $data_request;
    
    protected $sandboxApiUrl = 'https://apitest1.evbi.vn';
    protected $apiUrl = 'https://api.evbi.vn';
    protected $dtacKey = 'kd01@medici.vn-C5F39BEF1D145B33C565A858E723190B';
    protected $mode = 'sandbox'; // sandbox | production
    protected $nsd = 'kd01@medici.vn';
    protected $secret_key = 'C5F39BEF1D145B33C565A858E723190B';
    protected $maxContractDays = 180;
    
    protected $mapFields = [
        'TEN' => 'name',
        'NGAY_SINH' => 'date_of_birth',
        'CMT' => 'identity_card',
        // 'CMT1' => 'CMT1',
        'GIOI_TINH' => 'sex',
        'DCHI' => 'address',
        'D_THOAI' => 'phone_number',
        'EMAIL' => 'email',
        'LOAI_NHA' => 'loai_nha',
        'DIEN_TICH' => 'dien_tich',
        'SO_HUU' => 'so_huu',
        'NAM_SD' => 'nam_sd',
        'SO_TANG' => 'so_tang',
        'GIA_TRI' => 'gia_tri',
        'MOI_QH' => 'relationship',
        'DKBS' => 'price_types'
    ];
    
    const ACCEPT_CODE = [
        'TOANCAU1_EBH' => [
            'cn_version' => 'CN.4.1',
            'file_type' => 'GCN'
        ],
        'TOANCAU2_EBH' => [
            'cn_version' => 'CN.4.1',
            'file_type' => 'GCN'
        ],
        'TOANCAU3_EBH' => [
            'cn_version' => 'CN.4.1',
            'file_type' => 'GCN'
        ],
        'DNA1_EBH' => [
            'cn_version' => 'CN.4.1',
            'file_type' => 'GCN'
        ],
        'DNA2_EBH' => [
            'cn_version' => 'CN.4.1',
            'file_type' => 'GCN'
        ],
        'DNA3_EBH' => [
            'cn_version' => 'CN.4.1',
            'file_type' => 'GCN'
        ],
        'CHAUA1_EBH' => [
            'cn_version' => 'CN.4.1',
            'file_type' => 'GCN'
        ],
        'CHAUA2_EBH' => [
            'cn_version' => 'CN.4.1',
            'file_type' => 'GCN'
        ],
        'CHAUA3_EBH' => [
            'cn_version' => 'CN.4.1',
            'file_type' => 'GCN'
        ],
        'BIZTRAVEL1_EBH' => [
            'cn_version' => 'CN.4.1',
            'file_type' => 'HD',
            'get_file' => false
        ],
        'BIZTRAVEL2_EBH' => [
            'cn_version' => 'CN.4.1',
            'file_type' => 'HD',
            'get_file' => false
        ],
        'BIZTRAVEL3_EBH' => [
            'cn_version' => 'CN.4.1',
            'file_type' => 'HD',
            'get_file' => false
        ],
        'NOIDIA1_EBH' => [
            'cn_version' => 'CN.4.3',
            'file_type' => 'GCN',
            'get_file' => false
        ],
        'NOIDIA2_EBH' => [
            'cn_version' => 'CN.4.3',
            'file_type' => 'GCN',
            'get_file' => false
        ],
        'NOIDIA3_EBH' => [
            'cn_version' => 'CN.4.3',
            'file_type' => 'GCN',
            'get_file' => false
        ],
        'MR_EBH' => [
            'cn_version' => 'CN.6',
            'file_type' => 'GCN',
            'get_file' => false
        ],
        'NC_EBH' => [
            'cn_version' => 'CN.6',
            'file_type' => 'GCN',
            'get_file' => false
        ],
        'DB_EBH' => [
            'cn_version' => 'CN.6',
            'file_type' => 'GCN',
            'get_file' => false
        ],
        'Vbihome' => [
            'cn_version' => 'TS.3',
            'file_type' => 'GCN',
            'get_file' => false
        ],
        
        'EBH_CT1' => [
            'cn_version' => 'CN.6',
            'file_type' => 'GCN',
            'get_file' => false
        ],
        'EBH_CT2' => [
            'cn_version' => 'CN.6',
            'file_type' => 'GCN',
            'get_file' => false
        ],
        'EBH_CT3' => [
            'cn_version' => 'CN.6',
            'file_type' => 'GCN',
            'get_file' => false
        ],
        'EBH_CT4' => [
            'cn_version' => 'CN.6',
            'file_type' => 'GCN',
            'get_file' => false
        ],
        'CB' => [
            'cn_version' => 'CN.6',
            'file_type' => 'GCN',
            'get_file' => false
        ],
        
        'DB' => [
            'cn_version' => 'CN.6',
            'file_type' => 'GCN',
            'get_file' => false
        ],
        'MR' => [
            'cn_version' => 'CN.6',
            'file_type' => 'GCN',
            'get_file' => false
        ],
        
        'NC' => [
            'cn_version' => 'CN.6',
            'file_type' => 'GCN',
            'get_file' => false
        ],
    
    
        'TV' => [ // Bảo hiểm vật chất xe
            'cn_version' => 'CN.6',
            'file_type' => 'GCN',
            'get_file' => false
        ],
    
        'TL' => [ // Người ngồi trên xe
            'cn_version' => 'CN.6',
            'file_type' => 'GCN',
            'get_file' => false
        ],
    
        'BN' => [ // Bảo hiểm trách nhận dân sự bắt buộc
            'cn_version' => 'CN.6',
            'file_type' => 'GCN',
            'get_file' => false
        ],
    
        'TH' => [ // Bảo hiểm hàng hóa trên xe
            'cn_version' => 'CN.6',
            'file_type' => 'GCN',
            'get_file' => false
        ],
        'BIZTRAVEL1_PREMIUM_EBH' => [
            'cn_version' => 'CN.4.1',
            'file_type'  => 'GCN'
        ],
        'BIZTRAVEL2_PREMIUM_EBH' => [
            'cn_version' => 'CN.4.1',
            'file_type'  => 'GCN'
        ],
        'BIZTRAVEL3_PREMIUM_EBH' => [
            'cn_version' => 'CN.4.1',
            'file_type'  => 'GCN'
        ],
        'DNA1_PREMIUM_EBH' => [
            'cn_version' => 'CN.4.1',
            'file_type'  => 'GCN'
        ],
        'DNA2_PREMIUM_EBH' => [
            'cn_version' => 'CN.4.1',
            'file_type'  => 'GCN'
        ],
        'DNA3_PREMIUM_EBH' => [
            'cn_version' => 'CN.4.1',
            'file_type'  => 'GCN'
        ],
        'TOANCAU1_PREMIUM_EBH' => [
            'cn_version' => 'CN.4.1',
            'file_type'  => 'GCN'
        ],
        'TOANCAU2_PREMIUM_EBH' => [
            'cn_version' => 'CN.4.1',
            'file_type'  => 'GCN'
        ],
        'TOANCAU3_PREMIUM_EBH' => [
            'cn_version' => 'CN.4.1',
            'file_type'  => 'GCN'
        ],
        'CHAUA1_PREMIUM_EBH' => [
            'cn_version' => 'CN.4.1',
            'file_type'  => 'GCN'
        ],
        'CHAUA2_PREMIUM_EBH' => [
            'cn_version' => 'CN.4.1',
            'file_type'  => 'GCN'
        ],
        'CHAUA3_PREMIUM_EBH' => [
            'cn_version' => 'CN.4.1',
            'file_type'  => 'GCN'
        ],
        'TOMATO_GOI_TITAN' => [
            'cn_version' => 'CN.6',
            'file_type'  => 'GCN'
        ],
        'TOMATO_GOI_BACH_KIM' => [
            'cn_version' => 'CN.6',
            'file_type'  => 'GCN'
        ],
        'TOMATO_GOI_BAC' => [
            'cn_version' => 'CN.6',
            'file_type'  => 'GCN'
        ],
        'TOMATO_GOI_VANG' => [
            'cn_version' => 'CN.6',
            'file_type'  => 'GCN'
        ],
    ];
    
    const SANDBOX_ACCEPT_CODE = [
        'TOANCAU1_EBHasdasd' => [
            'cn_version' => 'CN.4.1',
            'file_type' => 'GCN'
        ],
        'TOANCAU2_EBH' => [
            'cn_version' => 'CN.4.1',
            'file_type' => 'GCN'
        ],
        'TOANCAU3_EBH' => [
            'cn_version' => 'CN.4.1',
            'file_type' => 'GCN'
        ],
        'DNA1_EBH' => [
            'cn_version' => 'CN.4.1',
            'file_type' => 'GCN'
        ],
        'DNA2_EBH' => [
            'cn_version' => 'CN.4.1',
            'file_type' => 'GCN'
        ],
        'DNA3_EBH' => [
            'cn_version' => 'CN.4.1',
            'file_type' => 'GCN'
        ],
        'CHAUA1_EBH' => [
            'cn_version' => 'CN.4.1',
            'file_type' => 'GCN'
        ],
        'CHAUA2_EBH' => [
            'cn_version' => 'CN.4.1',
            'file_type' => 'GCN'
        ],
        'CHAUA3_EBH' => [
            'cn_version' => 'CN.4.1',
            'file_type' => 'GCN'
        ],
        'BIZTRAVEL1_EBH' => [
            'cn_version' => 'CN.4.1',
            'file_type' => 'HD'
        ],
        'BIZTRAVEL2_EBH' => [
            'cn_version' => 'CN.4.1',
            'file_type' => 'HD'
        ],
        'BIZTRAVEL3_EBH' => [
            'cn_version' => 'CN.4.1',
            'file_type' => 'HD'
        ],
        'NOIDIA1_EBH' => [
            'cn_version' => 'CN.4.3',
            'file_type' => 'GCN'
        ],
        'NOIDIA2_EBH' => [
            'cn_version' => 'CN.4.3',
            'file_type' => 'GCN'
        ],
        'NOIDIA3_EBH' => [
            'cn_version' => 'CN.4.3',
            'file_type' => 'GCN'
        ],
        'MR_EBH' => [
            'cn_version' => 'CN.6',
            'file_type' => 'GCN',
            'get_file' => false
        ],
        'NC_EBH' => [
            'cn_version' => 'CN.6',
            'file_type' => 'GCN',
            'get_file' => false
        ],
        'DB_EBH' => [
            'cn_version' => 'CN.6',
            'file_type' => 'GCN',
            'get_file' => false
        ],
        'Vbihome' => [
            'cn_version' => 'TS.3',
            'file_type' => 'GCN',
            'get_file' => false
        ],
        
        'EBH_CT1' => [
            'cn_version' => 'CN.6',
            'file_type' => 'GCN',
            'get_file' => false
        ],
        'EBH_CT2' => [
            'cn_version' => 'CN.6',
            'file_type' => 'GCN',
            'get_file' => false
        ],
        'EBH_CT3' => [
            'cn_version' => 'CN.6',
            'file_type' => 'GCN',
            'get_file' => false
        ],
        'EBH_CT4' => [
            'cn_version' => 'CN.6',
            'file_type' => 'GCN',
            'get_file' => false
        ],
        'CB' => [
            'cn_version' => 'CN.6',
            'file_type' => 'GCN',
            'get_file' => false
        ],
        
        'DB' => [
            'cn_version' => 'CN.6',
            'file_type' => 'GCN',
            'get_file' => false
        ],
        'MR' => [
            'cn_version' => 'CN.6',
            'file_type' => 'GCN',
            'get_file' => false
        ],
        
        'NC' => [
            'cn_version' => 'CN.6',
            'file_type' => 'GCN',
            'get_file' => false
        ],
    
        'TV' => [ // Bảo hiểm vật chất xe
            'cn_version' => 'CN.6',
            'file_type' => 'GCN',
            'get_file' => false
        ],
    
        'TL' => [ // Người ngồi trên xe
            'cn_version' => 'CN.6',
            'file_type' => 'GCN',
            'get_file' => false
        ],
    
        'BN' => [ // Bảo hiểm trách nhận dân sự bắt buộc
            'cn_version' => 'CN.6',
            'file_type' => 'GCN',
            'get_file' => false
        ],
    
        'TH' => [ // Bảo hiểm hàng hóa trên xe
            'cn_version' => 'CN.6',
            'file_type' => 'GCN',
            'get_file' => false
        ],
        'BIZTRAVEL1_PREMIUM_EBH' => [
            'cn_version' => 'CN.4.1',
            'file_type'  => 'GCN'
        ],
        'BIZTRAVEL2_PREMIUM_EBH' => [
            'cn_version' => 'CN.4.1',
            'file_type'  => 'GCN'
        ],
        'BIZTRAVEL3_PREMIUM_EBH' => [
            'cn_version' => 'CN.4.1',
            'file_type'  => 'GCN'
        ],
        
        'DNA1_PREMIUM_EBH' => [
            'cn_version' => 'CN.4.1',
            'file_type'  => 'GCN'
        ],
        'DNA2_PREMIUM_EBH' => [
            'cn_version' => 'CN.4.1',
            'file_type'  => 'GCN'
        ],
        'DNA3_PREMIUM_EBH' => [
            'cn_version' => 'CN.4.1',
            'file_type'  => 'GCN'
        ],
        'TOANCAU1_PREMIUM_EBH' => [
            'cn_version' => 'CN.4.1',
            'file_type'  => 'GCN'
        ],
        'TOANCAU2_PREMIUM_EBH' => [
            'cn_version' => 'CN.4.1',
            'file_type'  => 'GCN'
        ],
        'TOANCAU3_PREMIUM_EBH' => [
            'cn_version' => 'CN.4.1',
            'file_type'  => 'GCN'
        ],
        'CHAUA1_PREMIUM_EBH' => [
            'cn_version' => 'CN.4.1',
            'file_type'  => 'GCN'
        ],
        'CHAUA2_PREMIUM_EBH' => [
            'cn_version' => 'CN.4.1',
            'file_type'  => 'GCN'
        ],
        'CHAUA3_PREMIUM_EBH' => [
            'cn_version' => 'CN.4.1',
            'file_type'  => 'GCN'
        ],
        'TOMATO_GOI_TITAN' => [
            'cn_version' => 'CN.6',
            'file_type'  => 'GCN'
        ],
        'TOMATO_GOI_BACH_KIM' => [
            'cn_version' => 'CN.6',
            'file_type'  => 'GCN'
        ],
        'TOMATO_GOI_BAC' => [
            'cn_version' => 'CN.6',
            'file_type'  => 'GCN'
        ],
        'TOMATO_GOI_VANG' => [
            'cn_version' => 'CN.6',
            'file_type'  => 'GCN'
        ],
        'BAN_LE_VANG' => [
            'cn_version' => 'CN.6',
            'file_type'  => 'GCN'
        ],
        'BAN_LE_TITAN' => [
            'cn_version' => 'CN.6',
            'file_type'  => 'GCN'
        ],
        'BAN_LE_BACH_KIM' => [
            'cn_version' => 'CN.6',
            'file_type'  => 'GCN'
        ],
        'BAN_LE_DONG' => [
            'cn_version' => 'CN.6',
            'file_type'  => 'GCN'
        ],
        'BAN_LE_BAC' => [
            'cn_version' => 'CN.6',
            'file_type'  => 'GCN'
        ],
    ];
    
    /**
     * VBIService constructor.
     * @param $mode
     */
    public function __construct($mode = '')
    {
        if (empty($mode)) {
            $this->mode = env('INSURANCE_SERVICE_MODE', 'production');
        } else {
            $this->mode = $mode;
        }
        
        $this->dtacKey = env('VBI_SERVICE_DTAC_KEY', 'EdberDFg8BE0100dfEAhk889');
        $this->nsd = env('VBI_SERVICE_NSD', 'EdberDFg8BE0100dfEAhk889');
    }
    
    public function getMode()
    {
        return $this->mode;
    }
    
    /**
     * @param $url
     * @param $method
     * @param $type
     * @param $data
     * @return mixed
     */
    public function sendRequest($url, $data, $method, $type)
    {
        $url = ($this->mode == 'sandbox' ? $this->sandboxApiUrl : $this->apiUrl) . $url;
        $header = [
            'Accept:application/json',
            'Authority:' . $this->dtacKey,
            'Content-Type:application/json'
        ];
      
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        
        $result = curl_exec($ch);//dd($result);
        curl_close($ch);
        
        try {
            if (empty($result)) {
                Log::info('[VBI-Service] Send request - Url: ' . $url . '. Method: ' . $method . '. Mode: ' . $this->mode);
                Log::info('[VBI-Service] Send request - Headers: ' . json_encode($header));
            }
            
            Log::info('[VBI-Service] Send request - Response: ' . $result);
            
            $response = json_decode($result);
            return $response;
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . '. File ' . $ex->getFile() . '. Line: ' . $ex->getLine());
            Log::info('[VBI-Service] Send request: body data: ' . $result);
            
            return false;
        }
    }
    
    /**
     * Provide insurance contract
     *
     * @param InsuranceContract $contract
     * @param bool $paymentStatus
     * @param bool $getSendData
     * @return array
     */
    public function provide(InsuranceContract $contract, $paymentStatus = false, $getSendData = false)
    {
        // Get payment status value
        $paymentStatusStr = 'D';
        try {
            // Api info
            $url = '/api/p/3';
            $method = 'POST';
            $type = 'json';
            
            $product = $contract->product;
            $productCode = !empty($contract->product_code) ? $contract->product_code : $product->code;
            
            // Check status get certificate by beneficiary
            $getBeneficiaryFile = true;
            $acceptCodes = self::ACCEPT_CODE;
            if (isset($acceptCodes[$productCode]['get_file']) && $acceptCodes[$productCode]['get_file'] == false) {
                $getBeneficiaryFile = false;
            }
            // Get contract beneficiaries
            $beneficiaries = InsuranceContractBeneficiary::getListByContract($contract->id);
            // Add gcn list
            $gcn = $this->_createListGCN($beneficiaries, $productCode);
            if (!empty($gcn['error'])) {
                return $gcn;
            }
            // Contract info
            $data_request = $this->_createContractData($contract, $productCode, $gcn, $paymentStatusStr);
            // dd($data_request);
            // dd(json_encode($data_request));
            if (!empty($data_request)) {
                $time_start = Carbon::parse($contract->start_time);
                $time_end = Carbon::parse($contract->end_time);
                
                // Bảo hiểm nhà tư nhân
                // if ($contract->type_id == 4) {
                //     $data_request['ngay_hl'] = $time_start->format('Ymd');
                //     $data_request['ngay_kt'] = $time_end->format('Ymd');
                    
                //     if (count($gcn) > 0) {
                //         $data_request['dien_tich'] = $gcn[0]['dien_tich'];
                //         $data_request['ddiem'] = $gcn[0]['dchi'];
                //         $data_request['so_tang'] = $gcn[0]['so_tang'];
                //     }
                //     $url = '/api/p/home';
                // }
                
                //Bảo hiểm ô tô
                // if ($contract->type_id == 2) {
                //     $data_request['ngay_hl'] = $time_start->format('Ymd');
                //     $data_request['ngay_kt'] = $time_end->format('Ymd');
                    
                    
                //     $url = '/api/xe/xe_nhap';
                // }
                
                
                //check time
                
                $time = $time_start->diffInDays($time_end) + 1;

                $check_time = ceil($time / $this->maxContractDays);
                $check_time = (int)$check_time;
                
                $so_hd = [];
                $id_hd = [];
                $error_hd = [];
                for ($i = 0; $i < $check_time; $i++) {
                    if ($time <= $this->maxContractDays) {
                        // $data_request['ngay_hl'] = $time_start->format('Ymd');
                        // $data_request['ngay_kt'] = $time_end->format('Ymd');
                        
                        // Update ngay_hl, ngay_kt for beneficiaries
                        if (!empty($data_request['gcns'])) {
                            $tmpData = [];
                            foreach ($data_request['gcns'] as $gcn_item) {
                                $gcn_item['ngay_hl'] = $data_request['ngay_hl'];
                                $gcn_item['ngay_kt'] = $data_request['ngay_kt'];
                                
                                if (empty($gcn_item['ten'])) {
                                    $gcn_item['ten'] = $data_request['ten'];
                                }
                                
                                $tmpData[] = $gcn_item;
                            }
                            
                            $data_request['gcns'] = $tmpData;
                        }
                        
                        file_put_contents(public_path('gcn.json'), json_encode($data_request));

                        $result = $this->sendRequest($url, $data_request, $method, $type);
                        if (isset($result->response_code) && $result->response_code == '00') {
                            
                            $id_hd = array();
                            $so_hd = array();
                            
                            if (!empty($result->so_hd) && !in_array($result->so_hd, $so_hd)) {
                                $so_hd[] = $result->so_hd;
                            } else {
                                Log::error('[VBIService] Missing so_hd. Response data: ' . json_encode($result));
                            }
                            
                            $id_hd[] = $result->so_id_vbi;
                            $id_hd = implode(',', $id_hd);
                            $so_hd = implode(',', $so_hd);
                            $beneficiary_code = [];
                            if (!isset($result->GCNS)) {
                                Log::error('[VBIServiceProvide] Provide response missing GCNS. ' . json_encode($result));
                            } else {
                                if (!empty($result->GCNS)) {
                                    $index = 0;
                                    foreach ($result->GCNS as $val) {
                                        // $beneficiary_id = $beneficiaries[$index]->update([
                                        //     'beneficiary_code' => $val->so_id_dt_vbi
                                        // ]);
                                        $beneficiary_code[$beneficiaries[$index]['id']][] = $val->so_id_dt_vbi;
                                        
                                        if ($paymentStatus && $getBeneficiaryFile) {
                                            Log::info('[VBIServiceProvide] Dispatch get file GCN for contract #' . $contract->id . ', beneficiary #' . $val->so_id_dt_vbi);
                                            $job = (new GetFile($result->so_id_vbi, $val->so_id_dt_vbi, $contract->id, $productCode, 'VBI', 'beneficiary'))
                                                ->delay(Carbon::now()->addSeconds(config('insurance.delay_get_file')));

                                            dispatch($job);
                                        }
                                        
                                        $index++;
                                    }
                                    
                                    // Get file for contract
                                    // if ($paymentStatus) {
                                    //     Log::info('[VBIServiceProvide] Dispatch get file HD for contract #' . $contract->id);
                                    //     $job = (new GetFile($result->so_id_vbi, '', $contract->id, $productCode, 'VBI', 'contract'))
                                    //         ->delay(Carbon::now()->addSeconds(config('insurance.delay_get_file')));
                                    //     dispatch($job);
                                    // }
                                }
                            }
                            $data = ['id_hd' => $id_hd, 'so_hd' => $so_hd, 'beneficiary_code' => $beneficiary_code];
                            // dd($data);
                        } else {
                            $error_hd[] = isset($result->response_message) ? $result->response_message : 'Dữ liệu trả về sai định dạng json';
                            $data['error'] = $error_hd;
                            Log::error('[VBIServiceProvide] Error provide, response: ' . json_encode($result) . '. Mode: ' . $this->mode);
                            Log::error('[VBIServiceProvide] Error provide for data: ' . json_encode($data_request));
                        }
                    } else {
                        $data_request['ngay_hl'] = $time_start->format('Ymd');
                        $data_request['ngay_kt'] = $time_end->format('Ymd');
                        
                        // Update ngay_hl, ngay_kt for beneficiaries
                        if (!empty($data_request['gcns'])) {
                            $tmpData = [];
                            foreach ($data_request['gcns'] as $gcn_item) {
                                $gcn_item['ngay_hl'] = $data_request['ngay_hl'];
                                $gcn_item['ngay_kt'] = $data_request['ngay_kt'];
                                
                                if (empty($gcn_item['ten'])) {
                                    $gcn_item['ten'] = $data_request['ten'];
                                }
                                
                                $tmpData[] = $gcn_item;
                            }
                            
                            $data_request['gcns'] = $tmpData;
                        }
                        
                        file_put_contents(public_path('gcn.json'), json_encode($data_request));

                        $result = $this->sendRequest($url, $data_request, $method, $type);
                        
                        if (isset($result->response_code) && $result->response_code == '00') {
                            
                            $id_hd = array();
                            $so_hd = array();
                            
                            if (!empty($result->so_hd) && !in_array($result->so_hd, $so_hd)) {
                                $so_hd[] = $result->so_hd;
                            } else {
                                Log::error('[VBIService] Missing so_hd. Response data: ' . json_encode($result));
                            }
                            
                            $id_hd[] = $result->so_id_vbi;
                            $id_hd = implode(',', $id_hd);
                            $so_hd = implode(',', $so_hd);
                            $beneficiary_code = [];
                            
                            if (!isset($result->GCNS)) {
                                Log::error('[VBIServiceProvide] Provide response missing GCNS. ' . json_encode($result));
                            } else {
                                if (!empty($result->GCNS)) {
                                    $index = 0;
                                    foreach ($result->GCNS as $val) {
                                        $beneficiary_code[$beneficiaries[$index]['id']][] = $val->so_id_dt_vbi;
                                        
                                        if ($paymentStatus && $getBeneficiaryFile) {
                                            $job = (new GetFile($result->so_id_vbi, $val->so_id_dt_vbi, $contract->id, $productCode, 'VBI'))
                                                ->delay(Carbon::now()->addSeconds(config('insurance.delay_get_file')));
                                            dispatch($job);
                                        }
                                        
                                        $index++;
                                    }
                                    
                                    if ($paymentStatus) {
                                        // Get file for contract
                                        $job = (new GetFile($result->so_id_vbi, '', $contract->id, $productCode, 'VBI', 'contract'))
                                            ->delay(Carbon::now()->addSeconds(config('insurance.delay_get_file')));
                                        dispatch($job);
                                    }
                                }
                            }
                            $data = ['id_hd' => $id_hd, 'so_hd' => $so_hd, 'beneficiary_code' => $beneficiary_code];
                        } else {
                            $error_hd[] = isset($result->response_message) ? $result->response_message : 'Dữ liệu trả về sai định dạng json';
                            $data['error'] = $error_hd;
                            Log::error('[VBIServiceProvide] Error provide, response: ' . json_encode($result) . '. Mode: ' . $this->mode);
                            Log::error('[VBIServiceProvide] Error provide for data: ' . json_encode($data_request));
                        }
                        
                        $data_request['ngay_hl'] = $time_start->format('d/m/Y');
                        $time = $time_start->diffInDays($time_end);
                    }
                }
                return $data;
            } else {
                return ['error' => 'Error create contract data'];
            }
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . '. file ' . $ex->getFile() . '. Line: ' . $ex->getLine());
            
            return ['error' => $ex->getMessage() . '. ' . $ex->getFile() . ':' . $ex->getLine()];
        }
    }    
    /**
     * Active certificate for contract
     *
     * @param InsuranceContract $contract
     * @return array
     */
    public function activeCertificate(InsuranceContract $contract)
    {
        return $this->provide($contract, true);
    }
    
    /**
     * @param InsuranceContract $contract
     * @return array
     */
    public function previewContractFile(InsuranceContract $contract)
    {
    
    }
    
    /**
     * @param $contract_id
     * @param $beneficiary_id
     * @param $file_type
     * @return array
     */
    public function getCertificateFile($contract_id, $beneficiary_id, $file_type, $type_id = 0)
    {
        // Accept file types
        $acceptTypes = ['HD', 'GCN'];
        
        if (in_array($file_type, $acceptTypes)) {
            $data = [
                'so_id_vbi' => $contract_id,
                'so_id_dt_vbi' => $beneficiary_id,
                'loai' => $file_type
            ];
            
            // Fix HO
            if ($type_id == 4 || $type_id == 3) {
                //$data['so_id_dt_vbi'] = $contract_id;
                //$data['loai'] = 'GCN';
            }
            
            
            //info api
            $url = '/api/p/viewFile';
            $method = 'POST';
            $type = 'json';
            
	    //var_dump($data);exit();
            $result = $this->sendRequest($url, $data, $method, $type);
            
            if (isset($result->response_code) && $result->response_code == '00') {
                return ['success' => true, 'message' => '', 'view_url' => $result->resultlist];
            } else {
                return ['success' => false, 'message' => 'Response error: ' . json_encode($result)];
            }
        } else {
            return ['success' => false, 'message' => 'Invalid file type. Accept types: ' . implode(', ', $acceptTypes)];
        }
    }
    
    /**
     * @param $id_hd
     * @param $id_dt
     * @param $fileType
     * @return mixed
     */
    public function getFile($id_hd, $id_dt, $fileType)
    {
        $data = [
            'so_id_vbi' => $id_hd,
            'so_id_dt_vbi' => $id_dt,
            'loai' => $fileType
        ];
        //info api
        $url = '/api/p/viewFile';
        $method = 'POST';
        $type = 'json';
        
        $result = $this->sendRequest($url, $data, $method, $type);
        return $result;
    }
    
    /**
     * @param $id_hd
     * @param $id_dt
     * @param $contract_id
     * @param string $type
     * @return bool
     */
    public function saveFile($id_hd, $id_dt, $contract_id, $type = 'beneficiary')
    {
        Log::info('[VBI Save file] start run for #' . $id_hd. $id_dt);
        try {
            $filePrefixName = 'GCN_';

            if ($type == 'beneficiary') {
                if (!empty($id_dt)) {
                    $result = $this->getFile($id_hd, $id_dt, 'GCN');
                } else {
                    Log::error('[VBI-GetFile] missing $id_dt. Type: ' . $type);
                    return false;
                }
            } else if ($type == 'contract') {
                $filePrefixName = 'HD_';
                $result = $this->getFile($id_hd, $id_dt, 'HD');
            } else {
                $result = [];
            }
            if (!empty($result)) {
                if (isset($result->resultlist) && !empty($result->resultlist)) {
                    $data = [
                        'name' => "",
                        'url' => $result->resultlist,
                    ];

                    return $data;
                    // Check result is file or preview url
                    // $fileExt = pathinfo($result->resultlist, PATHINFO_EXTENSION);
                    // if (!empty($fileExt)) {
                    //     $fileUrl = $result->resultlist;
                    // } else {
                    //     try {
                    //         $protocol = 'https://';
                    //         $client = new Client();
                    //         $content = $client->get($result->resultlist);
                            
                    //         $content = $content->getBody()->getContents();
                    //         // Try to get file url
                    //         $pattern = '/(initViewPDF2\(\')(.*)\'/';
                    //         preg_match($pattern, $content, $matches);
                            
                    //         if (isset($matches[2]) && !empty($matches[2])) {
                    //             $fileUrl = $protocol . $matches[2];
                    //         }
                    //     } catch (\Exception $ex) {
                    //         Log::error($ex->getMessage());
                    //     }
                    // }

                    // $fileUrl = $result->resultlist;
                    // if (isset($fileUrl) && !empty($fileUrl)) {
                    //     $file_name = $filePrefixName . basename($fileUrl);
                    //     Log::info('[VBIService SaveFile] Contract #' . $contract_id . ' File name: ' . $file_name);
                        
                    //     if (!isset($client)) {
                    //         $client = new Client();
                    //     }
                        
                    //     $savePath = $contract_id . DIRECTORY_SEPARATOR . 'certificate' . DIRECTORY_SEPARATOR . $file_name;
                        
                    //     // Create file
                    //     Storage::disk('contract')->put($savePath, '');
                        
                    //     $localPath = Storage::disk('contract')->getDriver()->getAdapter()->getPathPrefix();
                    //     // Get file
                    //     $client->get($fileUrl, ['sink' => $localPath . DIRECTORY_SEPARATOR . $savePath]);
                        
                    //     $check_file = Storage::disk('contract')->exists($savePath);
                    //     if ($check_file) {
                    //         $file = Storage::disk('contract')->url($savePath);
                    //         $data = ['name' => $file_name, 'url' => $file];
                    //     } else {
                    //         $data = [];
                    //     }
                        
                    //     return $data;
                    // } else {
                    //     Log::error('[VBI-GetFile] File url not found. Contract #' . $contract_id . '. Matches found: ' . (isset($matches) ? json_encode($matches) : ''));
                    //     return false;
                    // }
                } else {
                    Log::error('[VBI-GetFile] result list empty. Contract #' . $contract_id . '. Data response: ' . json_encode($result));
                    return false;
                }
            } else {
                Log::error('[VBI-GetFile] missing request data. Type: ' . $type);
                return false;
            }
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . '. File: ' . $ex->getFile() . '. Line: ' . $ex->getLine());
            
            return false;
        }
    }
    
    /**
     * Check VBI code
     * @param $product_code
     * @return bool
     */
    public function checkVBI($product_code)
    {
        if (empty($product_code)) {
            return false;
        }
        
        $acceptCode = $this->mode == 'sandbox' ? self::SANDBOX_ACCEPT_CODE : self::ACCEPT_CODE;
        // Get list check code
        $listCheckCode = array_keys($acceptCode);
        if (in_array($product_code, $listCheckCode)) {
            return true;
        }
        return false;
    }
    
    public function hasService($productCode)
    {
        return $this->checkVBI($productCode);
    }
    
    /**
     * @param $product_code
     * @return bool
     */
    public function getCNVersion($product_code)
    {
        if (empty($product_code)) {
            return false;
        }
        
        $acceptCode = $this->mode == 'sandbox' ? self::SANDBOX_ACCEPT_CODE : self::ACCEPT_CODE;
        
        if (isset($acceptCode[$product_code])) {
            return $acceptCode[$product_code]['cn_version'];
        }
        return '';
    }
    
    /**
     * @param $product_code
     * @return bool
     */
    public function getFileType($product_code)
    {
        if (empty($product_code)) {
            return false;
        }
        
        $acceptCode = $this->mode == 'sandbox' ? self::SANDBOX_ACCEPT_CODE : self::ACCEPT_CODE;
        
        if (isset($acceptCode[$product_code])) {
            return $acceptCode[$product_code]['file_type'];
        }
        return '';
    }
    
    protected function getFileUrl($fileKey, $ext = 'pdf')
    {
        return 'https://tracuu.evbi.vn/ViewReports/outputs/' . $fileKey . '.' . $ext;
    }
    
    /**
     * @param $beneficiaries
     * @param $productCode
     * @return array
     */
    protected function _createListGCN($beneficiaries, $productCode, $idDTac = '201710250102022')
    {
        $gcn = [];
        if ($beneficiaries) {
            foreach ($beneficiaries as $beneficiary) {
                // Get beneficiary attributes
                $attributes = !empty($beneficiary['value']) ? json_decode($beneficiary['value'], true) : [];
                
                if (!empty($attributes)) {
                    // Check birthday
                    if (isset($attributes[$this->mapFields['NGAY_SINH']])) {
                        try {
                            $date = Carbon::createFromFormat('d/m/Y', trim($attributes[$this->mapFields['NGAY_SINH']]));
                        } catch (\Exception $ex) {
                            return ['error' => 'Ngày sinh của người hưởng bảo hiểm phải có định dạng: d/m/Y'];
                        }
                    }
                    if (isset($attributes[$this->mapFields['DKBS']])) {
                        $dkbs = array();
                        foreach ($attributes[$this->mapFields['DKBS']] as $key => $val) {
                            // if ($val == 'dentistry_fee') {
                            //     $dkbs[] = 'D7';
                            // } elseif ($val == 'out_fee') {
                            //     $dkbs[] = 'B3';
                            // } elseif ($val == 'maternity_fee') {
                            //     $dkbs[] = 'C';
                            // }
                                $dkbs[] = $val;
                        }
                        $dkbs = implode(',', $dkbs);
                    }
                    // $packageArray = array('BAN_LE_VANG', 'BAN_LE_TITAN','BAN_LE_BACH_KIM', 'BAN_LE_DONG', 'BAN_LE_BAC');
                    // if(in_array($productCode, $packageArray)){
                    //     if($attributes[$this->mapFields['MOI_QH']] == 'Bố/Mẹ'){
                    //         $moiqh = 'QH00001';
                    //     }
                    //     elseif($attributes[$this->mapFields['MOI_QH']] == 'Chủ HD'){
                    //         $moiqh = 'QH00000';
                    //     }
                    //     elseif($attributes[$this->mapFields['MOI_QH']] == 'Vợ/chồng'){
                    //         $moiqh = 'QH00002';
                    //     }
                    //     elseif($attributes[$this->mapFields['MOI_QH']] == 'Con'){
                    //         $moiqh = 'QH00007';
                    //     }
                    //     elseif($attributes[$this->mapFields['MOI_QH']] == 'Anh/chị/em ruột'){
                    //         $moiqh = 'QH00003';
                    //     }
                    //     elseif($attributes[$this->mapFields['MOI_QH']] == 'Khác'){
                    //         $moiqh = 'QH00003';
                    //     }else{
                    //         $moiqh = '';
                    //     }
                    // }else{
                        $moiqh = isset($attributes[$this->mapFields['MOI_QH']]) ? $attributes[$this->mapFields['MOI_QH']] : '';
                    // }

                    $today = date('Ymd');
                    $today_last_year = date('Ymd', strtotime("+1 year"));
                    $gcn[] = array(
                        'so_id_dt_vbi' => !empty($beneficiary['beneficiary_code']) ? $beneficiary['beneficiary_code'] : '',
                        'so_id_dt_dtac' => $beneficiary['contract_id'],
                        'goi_bh' => $productCode,
                        'ten' => isset($attributes[$this->mapFields['TEN']]) ? $attributes[$this->mapFields['TEN']] : '',
                        'dchi' => isset($attributes[$this->mapFields['DCHI']]) ? $attributes[$this->mapFields['DCHI']] : '',
                        'ngay_sinh' => isset($attributes[$this->mapFields['NGAY_SINH']]) ? $date->format('Ymd') : '',
                        'gioi_tinh' => isset($attributes[$this->mapFields['GIOI_TINH']]) ? mb_convert_case(str_slug($attributes[$this->mapFields['GIOI_TINH']]), MB_CASE_UPPER, "UTF-8") : '',
                        'cmt' => isset($attributes[$this->mapFields['CMT']]) ? $attributes[$this->mapFields['CMT']] : '',
                        'cmt_ngay_cap' => '',
                        'cmt_noi_cap' => '',
                        'd_thoai' => isset($attributes[$this->mapFields['D_THOAI']]) ? $attributes[$this->mapFields['D_THOAI']] : '',
                        'email' => isset($attributes[$this->mapFields['EMAIL']]) ? $attributes[$this->mapFields['EMAIL']] : 'khachhang@eroscare.com',
                        'ngay_hl' => $today,
                        'ngay_kt' => $today_last_year,
                        'moi_qh' => $moiqh,
                        'khu_vuc' => '',
                        'dkbs' => isset($dkbs) ? $dkbs : '',
                        // 'tien_bh_nha' => '',
                        // 'trang_thai' => 'C',
                        // 'dien_tich' => isset($attributes[$this->mapFields['DIEN_TICH']]) ? $attributes[$this->mapFields['DIEN_TICH']] : '',
                        // 'so_tang' => isset($attributes[$this->mapFields['SO_TANG']]) ? $attributes[$this->mapFields['SO_TANG']] : '',
                    );
                }
            }
        }

        return $gcn;
    }
    
    protected function _createContractData(InsuranceContract $contract, $productCode, $listGcn, $paymentStatus)
    {
        // Customer birth day
        if (!empty($contract->customer->date_of_birth)) {
            $customerBirthDay = Carbon::parse($contract->customer->date_of_birth)->format('Ymd');
        } else {
            Log::error('[VBIService] Provide contract error: missing customer birthday');
            $customerBirthDay = '';
        }
        
        $gender = $contract->customer->sex ? 'NAM' : 'NU';
        // if (isset($contract->customer->sex)) {
        //     $gender = $contract->customer->sex;
        // }
        
        // Get contract addition attributes
        $additionAttributes = !empty($contract->addition_attributes) ? json_decode($contract->addition_attributes, true) : [];
        
        $filter_data = json_decode($contract->filter_data, true);
        
        //Get data of car insurance
        if (isset($filter_data['car_brand'])) {
            $carBrand = CarBrand::findOrFail($filter_data['car_brand']);
            if (isset($filter_data['car_model']) && !empty($carBrand)) {
                // Get list model
                $carModel = CarModel::findOrFail($filter_data['car_model']);
                
                if (isset($filter_data['year_manufacture'])) {
                    $carTrim = CarModelTrim::findOrFail($filter_data['car_trim']);
                }
            }
        }

//        dd($contract);
        $today = date('Ymd');
        $today_last_year = date('Ymd', strtotime("+1 year"));
        $string_signature = 'nsd='.$this->nsd.'&so_id_dtac='.$contract->id.'&nv='.$this->getCNVersion($productCode).'&ten='.$contract->customer->name.'&dia_chi='.$contract->customer->address.'&ngay_sinh='.$customerBirthDay.'&gioi_tinh='.$gender.'&cmt='.$contract->customer->id_card_number;
        $signature = hash_hmac('sha256', $string_signature, $this->secret_key);

        $first_return_array = [
            'dtac_key'       => $this->dtacKey,
            'nsd'            => $this->nsd,
            'so_id_vbi'      => 0,
            'so_id_dtac'     => $contract->id,
            'nv'             => $this->getCNVersion($productCode),
            'ten'            => $contract->customer->name,
            'dchi'           => !empty($contract->customer->address) ? $contract->customer->address : '87 Vương Thừa Vũ',
            // 'loai_kh'        => !empty($contract->customer->type_id) && $contract->customer->type_id == 2 ? 'DN' : 'CN',
            // 'mst'            => (isset($contract->customer->identity_card) && $contract->customer->type_id == 2) ? $contract->customer->identity_card : "",
            'ngay_sinh'      => $customerBirthDay,
            'gioi_tinh'      => $gender,
            'cmt'            => $contract->customer->id_card_number,
            'cmt_ngay_cap'   => '',
            'cmt_noi_cap'    => '',
            'd_thoai'        => $contract->customer->phone_number,
            'email'          => $contract->customer->email,
            'ngay_hl'        => $today,
            'ngay_kt'        => $today_last_year,
            'trang_thai_tt'  => $paymentStatus,
            'hinh_thuc_tt'   => '',
            'signature'      => $signature,
            'gcns'           => $listGcn
        ];
        // dd($string_signature, $this->secret_key, $signature, $contract->customer->name, $first_return_array);

        // if ($contract->type_id == 2) {
        //     $bien_so_xe = $contract->beneficiary;
        //     $bien_so_xe = \GuzzleHttp\json_decode($bien_so_xe[0]->value, true);
        //     $ngay_hl = Carbon::parse($contract->start_time)->format('Ymd');
        //     $ngay_kt = Carbon::parse($contract->end_time)->format('Ymd');
            
        //     $extra_fees = !empty($contract->extra_fees) ? \GuzzleHttp\json_decode($contract->extra_fees, true) : [];
        //     $extra_fee_attributes = !empty($contract->extra_fee_attributes) ? \GuzzleHttp\json_decode($contract->extra_fee_attributes, true) : [];
            
        //     $extra_products = !empty($contract->extra_products) ? \GuzzleHttp\json_decode($contract->extra_products, true) : [];
        //     // $extra_product_filter_data = !empty($contract->extra_product_filter_data) ? \GuzzleHttp\json_decode($contract->extra_product_filter_data) : [];
            
        //     //id bao hiem trach nhiem dan su oto
        //     $tnds = Product::where('code', 'bh_tnds')->first();

        //     // dd($extra_fees, $extra_fee_attributes, $extra_products,  $contract);
        //     $list_dk = [
        //         [
        //             'tien_bh' => $filter_data["car_price"],
        //             'loai' => 'TV',
        //             'mien_thuong' => '',
        //             'ktru' => '',
        //             'ngay_hl' => $ngay_hl,
        //             'ngay_kt' => $ngay_kt
        //         ],
        //         [
        //             'tien_bh' => isset($extra_fees["bh_tn_nntx"]) && !empty($extra_fee_attributes["tn_nntx_amount"]) ? $extra_fee_attributes["tn_nntx_amount"] : 0,
        //             'loai' => 'TL',
        //             'mien_thuong' => '',
        //             'ktru' => '',
        //             'ngay_hl' => $ngay_hl,
        //             'ngay_kt' => $ngay_kt
        //         ],
        //         [
        //             //trach nhiem dan su
        //             'tien_bh' => !empty($tnds->id) && !empty($extra_products[$tnds->id]) ? $extra_products[$tnds->id] : '',
        //             'loai' => 'BN',
        //             'mien_thuong' => '',
        //             'ktru' => '',
        //             'ngay_hl' => $ngay_hl,
        //             'ngay_kt' => $ngay_kt
        //         ],
        //         [
        //             //hang hoa tren xe
        //             'tien_bh' => isset($extra_fees['Liability-cargo']) && isset($extra_fee_attributes['Tons']) ? $extra_fee_attributes['Tons'] : 0,
        //             'loai' => 'TH',
        //             'mien_thuong' => '',
        //             'ktru' => '',
        //             'ngay_hl' => $ngay_hl,
        //             'ngay_kt' => $ngay_kt
        //         ]
        //     ];
            
        //     $second_return_array = [
        //         'bien_xe' => !empty($bien_so_xe["BKS"]) ? $bien_so_xe["BKS"] : "",
        //         'so_khung' => !empty($bien_so_xe["SK"]) ? $bien_so_xe["SK"] : "",
        //         'so_may' => !empty($bien_so_xe["SM"]) ? $bien_so_xe["SM"] : "",
        //         'noi_nhan' => '',
        //         'hang_xe' => !empty($carBrand) ? $carBrand->code : '',
        //         'hieu_xe' => !empty($carModel) ? $carModel->code : '',
        //         'nhom_xe' => !empty($carModel) ? $carModel->code : '',
        //         'nam_sx' => !empty($filter_data['year_manufacture']) ? $filter_data['year_manufacture'] : '',
        //         'loai_xe' => !empty($carTrim) ? $carTrim->code : '',
        //         'so_cho' => !empty($carTrim) ? $carTrim->num_seat : '',
        //         'ttai' => !empty($carTrim) ? $carTrim->weight : '',
        //         'md_sd' => !empty($filter_data['car_use_type']) ? 'C' : 'K',
        //         'dkbs' => '',
        //         'gtri_xe' => $contract->gross_amount,
        //         'list_dk' => $list_dk,
        //     ];
            
        // } else {
        //     $packageArray = array('TOMATO_GOI_BAC', 'TOMATO_GOI_BACHKIM','TOMATO_GOI_TITAN', 'TOMATO_GOI_VANG');
        //     if(in_array($contract->product_code, $packageArray)){
        //         $ctrinh = 'TOMATO';
        //     }else{
        //         $ctrinh = '';
        //     }
        //     $second_return_array = [
        //         'ng_huong1_ten' => '',
        //         'ng_huong1_dchi' => '',
        //         'ng_huong2_ten' => '',
        //         'ng_huong2_dchi' => '',
        //         'hd_tin_dung' => '',
        //         'tien_vay' => '',
        //         'thoi_han_vay' => '',
        //         'kieu_phi' => '',
        //         'mst' => (isset($contract->customer->identity_card) && $contract->customer->type_id == 2) ? $contract->customer->identity_card : '',
        //         'dai_dien' => '',
        //         'cvu_dai_dien' => '',
        //         'ctrinh' => $ctrinh,
        //         'noi_di' => isset($additionAttributes['noi_di']) ? $additionAttributes['noi_di'] : '',
        //         'noi_den' => isset($additionAttributes['noi_den']) ? $additionAttributes['noi_den'] : '',
        //         'khu_vuc' => '',
        //         'trang_thai_tt' => $paymentStatus,
        //         'hinh_thuc_tt' => '',
        //         'ddiem' => '',
        //         'loai_nha' => !empty($filter_data['house_class']) ? $this->getLoainha($filter_data['house_class']) : '',
        //         'dien_tich' => '',
        //         'so_huu' => !empty($filter_data['house_own']) ? $filter_data['house_own'] : '',
        //         'nam_sd' => !empty($filter_data['house_create_year']) ? $filter_data['house_create_year'] : '',
        //         'gia_tri' => !empty($filter_data['compensate_amount']) ? $filter_data['compensate_amount'] : 0,
        //         'so_tien' => !empty($filter_data['compensate_amount']) ? $filter_data['compensate_amount'] : 0,
        //         'chua_chay_tu_dong' => !empty($filter_data['system_fire_auto']) ? $filter_data['system_fire_auto'] : 0,
        //         'trang_thai' => 'C',
        //         'gcns' => $listGcn
        //     ];
        // }

//        dd($contract,$extra_fees, array_merge($first_return_array, $second_return_array));
        // return array_merge($first_return_array, $second_return_array);

        return $first_return_array;
    }
    
    /**
     * Format currency
     * @param $money
     * @return int
     */
    private function formatCurrency($money)
    {
        return (int)preg_replace('/\,/i', '', $money);
    }
    
    /**
     * Get loại nhà
     * @param $house_class
     * @return string
     */
    private function getLoainha($house_class)
    {
        // Nhà liền kề
        if ($house_class == 1) {
            return "0002.01";
        } else if ($house_class == 2) {
            // Nhà biệt thự
            return "0002.02";
        } else if ($house_class == 3) {
            // Nhà chung cư
            return "0002.03";
        }
        return "";
    }
}
