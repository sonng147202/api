<?php

namespace App\Lib\InsuranceServices\VBI\V1;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Lib\InsuranceContractServiceInterface;
use App\Models\CustomerType;
use App\Models\InsuranceContract;
use App\Jobs\GetFile;

class VBIDriver implements InsuranceContractServiceInterface
{
    public $id; // id InsuranceContract
    public $data_request;

    protected $sandboxApiUrl = 'http://14.160.90.226:90';
    protected $apiUrl = 'https://m.baohiem-vbi.vn';
    protected $maDTac = 'EBHKD01';
    protected $maDVi = 'EBHKD01';
    protected $mode = 'sandbox'; // sandbox | production

    protected $mapFields = [
        'TEN'       => 'name',
        'ngay_sinh' => 'date_of_birth',
        'CMT'       => 'identity_card',
        'GIOI_TINH' => 'sex',
        'DCHI'      => 'address',
        'D_THOAI'   => 'phone_number',
        'EMAIL'     =>  'email'
    ];

    const ACCEPT_CODE = [
        'TOANCAU1_EBH' => [
            'cn_version' => 'CN.4.1',
            'file_type'  => 'GCN'
        ],
        'TOANCAU2_EBH' => [
            'cn_version' => 'CN.4.1',
            'file_type'  => 'GCN'
        ],
        'TOANCAU3_EBH' => [
            'cn_version' => 'CN.4.1',
            'file_type'  => 'GCN'
        ],
        'DNA1_EBH' => [
            'cn_version' => 'CN.4.1',
            'file_type'  => 'GCN'
        ],
        'DNA2_EBH' => [
            'cn_version' => 'CN.4.1',
            'file_type'  => 'GCN'
        ],
        'DNA3_EBH' => [
            'cn_version' => 'CN.4.1',
            'file_type'  => 'GCN'
        ],
        'CHAUA1_EBH' => [
            'cn_version' => 'CN.4.1',
            'file_type'  => 'GCN'
        ],
        'CHAUA2_EBH' => [
            'cn_version' => 'CN.4.1',
            'file_type'  => 'GCN'
        ],
        'CHAUA3_EBH' => [
            'cn_version' => 'CN.4.1',
            'file_type'  => 'GCN'
        ],
        'BIZTRAVEL1_EBH' => [
            'cn_version' => 'CN.4.1',
            'file_type'  => 'HD'
        ],
        'BIZTRAVEL2_EBH' => [
            'cn_version' => 'CN.4.1',
            'file_type'  => 'HD'
        ],
        'BIZTRAVEL3_EBH' => [
            'cn_version' => 'CN.4.1',
            'file_type'  => 'HD'
        ],
        'NOIDIA1_EBH' => [
            'cn_version' => 'CN.4.3',
            'file_type'  => 'GCN'
        ],
        'NOIDIA2_EBH' => [
            'cn_version' => 'CN.4.3',
            'file_type'  => 'GCN'
        ],
        'NOIDIA3_EBH' => [
            'cn_version' => 'CN.4.3',
            'file_type'  => 'GCN'
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
        ]
    ];

    const SANDBOX_ACCEPT_CODE = [
        'TOANCAU1_EBH' => [
            'cn_version' => 'CN.4.1',
            'file_type'  => 'GCN'
        ],
        'TOANCAU2_EBH' => [
            'cn_version' => 'CN.4.1',
            'file_type'  => 'GCN'
        ],
        'TOANCAU3_EBH' => [
            'cn_version' => 'CN.4.1',
            'file_type'  => 'GCN'
        ],
        'DNA1_EBH' => [
            'cn_version' => 'CN.4.1',
            'file_type'  => 'GCN'
        ],
        'DNA2_EBH' => [
            'cn_version' => 'CN.4.1',
            'file_type'  => 'GCN'
        ],
        'DNA3_EBH' => [
            'cn_version' => 'CN.4.1',
            'file_type'  => 'GCN'
        ],
        'CHAUA1_EBH' => [
            'cn_version' => 'CN.4.1',
            'file_type'  => 'GCN'
        ],
        'CHAUA2_EBH' => [
            'cn_version' => 'CN.4.1',
            'file_type'  => 'GCN'
        ],
        'CHAUA3_EBH' => [
            'cn_version' => 'CN.4.1',
            'file_type'  => 'GCN'
        ],
        'BIZTRAVEL1_EBH' => [
            'cn_version' => 'CN.4.1',
            'file_type'  => 'HD'
        ],
        'BIZTRAVEL2_EBH' => [
            'cn_version' => 'CN.4.1',
            'file_type'  => 'HD'
        ],
        'BIZTRAVEL3_EBH' => [
            'cn_version' => 'CN.4.1',
            'file_type'  => 'HD'
        ],
        'NOIDIA1_EBH' => [
            'cn_version' => 'CN.4.3',
            'file_type'  => 'GCN'
        ],
        'NOIDIA2_EBH' => [
            'cn_version' => 'CN.4.3',
            'file_type'  => 'GCN'
        ],
        'NOIDIA3_EBH' => [
            'cn_version' => 'CN.4.3',
            'file_type'  => 'GCN'
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
        ]
    ];

    /**
     * VBIService constructor.
     * @param string $mode
     */
    public function __construct($mode = '')
    {
        if (empty($mode)) {
            $this->mode = env('INSURANCE_SERVICE_MODE', 'production');
        } else {
            $this->mode = $mode;
        }
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
    protected function sendRequest($url, $method, $type, $data)
    {
        $url = ($this->mode == 'sandbox' ? $this->sandboxApiUrl : $this->apiUrl) . $url;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'content-type:application/json',
            'Accept:application/json',
            'Authority:' . config('insurance.auth_key')
        ]);
        curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE));
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        $result = curl_exec($ch);
        curl_close ($ch);

        try {
            //Log::info('Request data: ' . json_encode($data, JSON_UNESCAPED_UNICODE));
            $response = json_decode($result);
            return $response;
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . '. File ' . $ex->getFile() . '. Line: ' . $ex->getLine());
            Log::info('[VBI-Service] Body data: ' . $result);

            return false;
        }
    }

    /**
     * Provide insurance contract
     *
     * @param InsuranceContract $contract
     * @param $listBeneficiaries
     * @return array
     */
    public function provide(InsuranceContract $contract, $listBeneficiaries)
    {
        try {
            // Api info
            $url = '/api/p/4';
            $method = 'POST';
            $type = 'json';

            // Create contract
            $customerType = CustomerType::getDetail($contract->customer->type_id);
            $product = $contract->product;

            // Get contract addition attributes
            $additionAttributes = !empty($contract->addition_attributes) ? json_decode($contract->addition_attributes, true) : [];

            $sex = '';
            if (isset($contract->customer->sex)){
                $sex = $contract->customer->sex;
            }

            $productCode = !empty($contract->product_code) ? $contract->product_code : $product->code;

            // Add gcn list
            $gcn = [];
            foreach ($listBeneficiaries as $beneficiary){
                // Check beneficiary attributes
                if (!isset($beneficiary[$this->mapFields['ngay_sinh']]) && isset($beneficiary['attributes'])) {
                    $beneficiary= $beneficiary['attributes'];
                }

                // Check birthday
                try {
                    $date = Carbon::createFromFormat('d/m/Y', $beneficiary[$this->mapFields['ngay_sinh']]);
                }  catch (\Exception $ex) {
                    return ['error' => 'Ngày sinh của người hưởng bảo hiểm phải có định dạng: d/m/Y'];
                }

                $gcn[] = array(
                    'TEN'       => isset($beneficiary[$this->mapFields['TEN']]) ? $beneficiary[$this->mapFields['TEN']] : '',
                    'GOI_BH'    => $productCode,// "CHAUA_2"
                    'DCHI'      => isset($beneficiary[$this->mapFields['DCHI']]) ? $beneficiary[$this->mapFields['DCHI']] : '',
                    'ngay_sinh' => isset($beneficiary[$this->mapFields['ngay_sinh']])? $date->format('d/m/Y') :'',//'01/04/1991',
                    'GIOI_TINH' => isset($beneficiary[$this->mapFields['GIOI_TINH']]) ? mb_convert_case(str_slug($beneficiary[$this->mapFields['GIOI_TINH']]), MB_CASE_UPPER, "UTF-8") : '',
                    'CMT'       => $beneficiary[$this->mapFields['CMT']],
                    'SO_ID_DT_VBI' => '0.6608542286096755',
                    'DKBS'      => '',
                    'D_THOAI'   => isset($beneficiary[$this->mapFields['D_THOAI']]) ? $beneficiary[$this->mapFields['D_THOAI']] : '',
                    'EMAIL'     => isset($beneficiary[$this->mapFields['EMAIL']]) ? $beneficiary[$this->mapFields['EMAIL']] : '',
                    'SO_ID_DT_DTAC' => "",
                    'KHU_VUC'   => ""
                );
            }

            $data_request = [
                'CMT'       => $contract->customer->identity_card,
                'ctrinh'    => '',
                'cvu_dai_dien' => '',
                'dai_dien'  => '',
                'DCHI'      => $contract->customer->address,
                'DOI_TUONG' => (isset($type_cus->code))? mb_convert_case(str_slug($customerType->code), MB_CASE_UPPER, "UTF-8") :'CN',//'CN',
                'DTAC_MA'   => config('insurance.dtc_ma'),//'EBHKD01'
                'NSD'       => config('insurance.nsd'), //'EBAOHIEM01',
                'DTAC_KEY'  => config('insurance.dtac_key'),//'QVSPn60I4U6w2q66iDqu',
                'SO_ID_VBI' => '',
                'SO_ID_DTAC'=> 0,
                'nv'        => '',
                'TEN'       => $contract->customer->name,
                'NGAY_SINH' => isset($contract->customer->date_of_birth) && (int)$contract->customer->date_of_birth > 0 ? Carbon::parse($contract->customer->date_of_birth)->format('d/m/Y'):'', //'01/04/1991'
                'GIOI_TINH' => ($sex == 0) ? 'NAM' : 'NU',
                'GIO_HL'    => Carbon::parse($contract->start_time)->format('h:i'),
                'GIO_KT'    => Carbon::parse($contract->end_time)->format('h:i'),
                'D_THOAI'   => $contract->customer->phone_number,
                'FAX'       => '',
                'email'     => $contract->customer->email,
                'MST'       => '',
                'NGAY_HL'   => Carbon::parse($contract->start_time),
                'NGAY_KT'   => Carbon::parse($contract->end_time),
                'NOI_DI'    => isset($additionAttributes['noi_di']) ? $additionAttributes['noi_di'] : '',
                'NOI_DEN'   => isset($additionAttributes['noi_den']) ? $additionAttributes['noi_den'] : '',
                'TRANG_THAI'=> 'D', // D|C. D: PAID!
                'HINHTHUC_TT' => 1,
                'NHOM'      => $this->getCNVersion($productCode),
                'GCN'       => $gcn

            ];
            
            //check time
            $time_start = $data_request['NGAY_HL'];
            $time_end = $data_request['NGAY_KT'];
            $time = $time_start->diffInDays($time_end) + 1;

            $check_time = ceil($time / 180);
            $check_time = (int) $check_time;

            $id_hd = [];
            $error_hd = [];

            for ($i = 0; $i < $check_time; $i++) {
                if ($time <= 180) {
                    $data_request['NGAY_HL'] = $time_start->format('d/m/Y');
                    $data_request['NGAY_KT'] = $time_end->format('d/m/Y');
                    $result = $this->sendRequest($url, $method, $type, $data_request);

                    if (isset($result->resultmessage) && $result->resultmessage =='SUCCESS') {
                        $id_hd[] = $result->valueOut;
                        $id_hd = implode($id_hd,',');
                        $beneficiary_code = [];

                        if (!empty($result->resultlist)) {
                            foreach ($result->resultlist as $val) {
                                $beneficiary_code[$val->ten][] = $val->so_id_dt_vbi;

                                $job = (new GetFile($result->valueOut, $val->so_id_dt_vbi, $contract->id, $productCode, 'VBI'))
                                    ->delay(Carbon::now()->addSeconds(config('insurance.delay_get_file')));
                                dispatch($job);
                            }

                            // Get file for contract
                            $job = (new GetFile($result->valueOut, $val->so_id_dt_vbi, $contract->id, $productCode, 'VBI', 'contract'))
                                ->delay(Carbon::now()->addSeconds(config('insurance.delay_get_file')));
                            dispatch($job);
                        }
                        $data = ['id_hd' => $id_hd, 'beneficiary_code' => $beneficiary_code];
                    } else {
                        $error_hd[] = isset($result->resultmessage) ? $result->resultmessage : 'Dữ liệu trả về sai định dạng json';
                        $data['error'] =  $error_hd;
                        Log::error('[VBIServiceProvide] Error provide response: ' . json_encode($result));
                        Log::error('[VBIServiceProvide] Error provide for data: ' . json_encode($data_request));
                    }
                } else {
                    $data_request['NGAY_HL'] =  $time_start->format('d/m/Y');
                    $time_start = $time_start->addDay(179);
                    $data_request['NGAY_KT'] =  $time_start->format('d/m/Y');

                    $result = $this->sendRequest($url, $method, $type, $data_request);

                    if (isset($result->resultmessage) && $result->resultmessage =='SUCCESS') {
                        $id_hd[] = $result->valueOut;
                        foreach ($result->resultlist as $val) {
                            $beneficiary_code[$val->ten][] = $val->so_id_dt_vbi;
                            $job = (new GetFile($result->valueOut, $val->so_id_dt_vbi, $contract->id, $productCode, 'VBI'))
                                ->delay(Carbon::now()->addSeconds(config('insurance.delay_get_file')));
                            dispatch($job);
                        }

                        // Get file for contract
                        $job = (new GetFile($result->valueOut, $val->so_id_dt_vbi, $contract->id, $productCode, 'VBI', 'contract'))
                            ->delay(Carbon::now()->addSeconds(config('insurance.delay_get_file')));
                        dispatch($job);
                    } else {
                        $error_hd[] = isset($result->resultmessage) ? $result->resultmessage : 'Dữ liệu trả về sai định dạng json';
                        Log::error('[VBIServiceProvide] Error provide response: ' . json_encode($result));
                        Log::error('[VBIServiceProvide] Error provide for data: ' . json_encode($data_request));
                    }

                    $data_request['NGAY_HL']  = $time_start->format('d/m/Y');
                    $time = $time_start->diffInDays($time_end);
                }
            }
            return $data;
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . '. file ' . $ex->getFile() . '. Line: ' . $ex->getLine());

            return ['error' => $ex->getMessage()];
        }
    }

    /**
     * @param InsuranceContract $contract
     * @param $listBeneficiaries
     * @return array
     */
    public function previewContractFile(InsuranceContract $contract)
    {
        try {
            // Api info
            $url = '/api/p/72';
            $method = 'POST';
            $type = 'json';

            $product = $contract->product;

            $productCode = !empty($contract->product_code) ? $contract->product_code : $product->code;
            // Add gcn list
            $listPreviewUrl = [];
            foreach ($listBeneficiaries as $beneficiary){
                if (isset($beneficiary['attributes']) && !isset($beneficiary[$this->mapFields['ngay_sinh']])) {
                    $beneficiary = $beneficiary['attributes'];
                }
                // Check birthday
                try {
                    $date = Carbon::createFromFormat('d/m/Y', $beneficiary[$this->mapFields['ngay_sinh']]);
                }  catch (\Exception $ex) {
                    return ['error' => 'Ngày sinh của người hưởng bảo hiểm phải có định dạng: d/m/Y'];
                }

                $data_request = array(
                    'MA_DVI'    => config('insurance.dtc_ma'),
                    'GOI_BH'    => $productCode,
                    'TEN'       => isset($beneficiary[$this->mapFields['TEN']]) ? $beneficiary[$this->mapFields['TEN']] : '',
                    'DCHI'      => isset($beneficiary[$this->mapFields['DCHI']]) ? $beneficiary[$this->mapFields['DCHI']] : '',
                    'NGAY_SINH' => isset($beneficiary[$this->mapFields['ngay_sinh']])? $date->format('d/m/Y') :'',//'01/04/1991',
                    'GIOI_TINH' => isset($beneficiary[$this->mapFields['GIOI_TINH']]) ? mb_convert_case(str_slug($beneficiary[$this->mapFields['GIOI_TINH']]), MB_CASE_UPPER, "UTF-8") : '',
                    'CMT'       => $beneficiary[$this->mapFields['CMT']],
                    'D_THOAI'   => isset($beneficiary[$this->mapFields['D_THOAI']]) ? $beneficiary[$this->mapFields['D_THOAI']] : '',
                    'EMAIL'     => isset($beneficiary[$this->mapFields['EMAIL']]) ? $beneficiary[$this->mapFields['EMAIL']] : '',
                    'NGAY_HL'   => Carbon::parse($contract->start_time),
                    'NGAY_KT'   => Carbon::parse($contract->end_time),
                    'NV'        => $this->getCNVersion($productCode)
                );

                $time_start = $data_request['NGAY_HL'];
                $time_end = $data_request['NGAY_KT'];
                $time = $time_start->diffInDays($time_end);

                $check_time = ceil($time / 180);
                $check_time = (int) $check_time;

                for ($i = 0; $i < $check_time; $i++) {
                    if (!isset($listPreviewUrl[$data_request['TEN']])) {
                        $listPreviewUrl[$data_request['TEN']] = [];
                    }
                    if ($time <= 180) {
                        $data_request['NGAY_HL'] = $time_start->format('d/m/Y');
                        $data_request['NGAY_KT'] = $time_end->format('d/m/Y');
                        $result = $this->sendRequest($url, $method, $type, $data_request);
                        if (isset($result->resultmessage) && $result->resultmessage =='SUCCESS') {
                            $listPreviewUrl[$data_request['TEN']][$i] = $result->resultlist;
                        } else {
                            $error_hd[] = isset($result->resultmessage) ? $result->resultmessage : 'Dữ liệu trả về sai định dạng json';
                            $data['error'] =  $error_hd;
                            Log::error('[VBIServicePreview] Error Preview for data: ' . json_encode($data_request));
                        }
                    } else {
                        $data_request['NGAY_HL'] =  $time_start->format('d/m/Y');
                        $time_start = $time_start->addDay(179);
                        $data_request['NGAY_KT'] =  $time_start->format('d/m/Y');

                        $result = $this->sendRequest($url, $method, $type, $data_request);
                        if (isset($result->resultmessage) && $result->resultmessage =='SUCCESS') {
                            $listPreviewUrl[$data_request['TEN']][$i] = $result->resultlist;
                        } else {
                            $error_hd[] = isset($result->resultmessage) ? $result->resultmessage : 'Dữ liệu trả về sai định dạng json';
                            $data['error'] =  $error_hd;
                            Log::error('[VBIServicePreview] Error Preview for data: ' . json_encode($data_request));
                        }

                        $data_request['NGAY_HL']  = $time_start->format('d/m/Y');
                        $time = $time_start->diffInDays($time_end);
                    }
                }
            }
            return $listPreviewUrl;
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . '. file ' . $ex->getFile() . '. Line: ' . $ex->getLine());

            return ['error' => $ex->getMessage()];
        }
    }

    /**
     * Get price
     *
     * @param $data
     * @return mixed
     */
    public function getPrice($data)
    {
        //info api
        $url = '/api/p/3';
        $method = 'POST';
        $type = 'json';

        $result = $this->sendRequest($url, $method, $type, $data);

        if ($result->resultmessage =='SUCCESS') {
            return $result->valueOut;
        } else {
            return $result->resultmessage;
        }

    }

    /**
     * Get list GCN
     *
     * @param $data
     * @return mixed
     */
    public function getGcn($data)
    {
        //info api
        $url = '/api/p/52';
        $method = 'POST';
        $type = 'json';

        $result = $this->sendRequest($url, $method, $type, $data);

        if ($result->resultmessage =='SUCCESS') {
            return $result->valueOut;
        } else {
            return $result->resultmessage;
        }
    }

    /**
     * @param $id_hd
     * @param $id_dt
     * @return mixed
     */
    public function getFile($id_hd, $id_dt)
    {
        $data = [
            'dtac_ma' => $this->maDTac,
            'ma_dvi'  => $this->maDVi,
            'so_id'   => $id_hd,
            'so_id_dt'=> $id_dt
        ];
        //info api
        $url = '/api/p/8';
        $method = 'POST';
        $type = 'json';

        $result = $this->sendRequest($url, $method, $type, $data);
        return $result;
    }

    /**
     * @param $id_hd
     * @param $id_dt
     * @param $contract_id
     * @param $product_code
     * @param string $type
     * @return bool
     */
    public function saveFile($id_hd, $id_dt, $contract_id, $product_code, $type = 'beneficiary')
    {
        try {
            // Check file type
            $fileType = $this->getFileType($product_code);

            $filePrefixName = 'GCN_';

            if ($type == 'beneficiary') {
                $data = [
                    'dtac_ma' => $this->maDTac,
                    'ma_dvi'  => $this->maDVi,
                    'so_id'   => $id_hd,
                    'so_id_dt'=> $id_dt,
                    'loai'    => 'GCN'
                ];
            } else if ($type == 'contract') {
                $data = [
                    'dtac_ma' => $this->maDTac,
                    'ma_dvi'  => $this->maDVi,
                    'so_id'   => $id_hd,
                    'loai'    => 'HD'
                ];
                $filePrefixName = 'HD_';
            } else {
                $data = [];
            }

            if (!empty($data)) {
                //info api
                $url = '/api/p/8';
                $method = 'POST';
                $dataType = 'json';

                $result = $this->sendRequest($url, $method, $dataType, $data);

                if (isset($result->resultlist) && !empty($result->resultlist)) {
                    // Check result is file or preview url
                    $fileExt = pathinfo($result->resultlist, PATHINFO_EXTENSION);
                    if (!empty($fileExt)) {
                        $fileUrl = $result->resultlist;
                    } else {
                        try {
                            $protocol = 'https://';
                            $client = new Client();
                            $content = $client->get($result->resultlist);

                            $content = $content->getBody()->getContents();
                            // Try to get file url
                            $pattern = '/(initViewPDF2\(\')(.*)\'/';
                            preg_match($pattern, $content, $matches);

                            if (isset($matches[2]) && !empty($matches[2])) {
                                $fileUrl = $protocol . $matches[2];
                            }
                        } catch (\Exception $ex) {
                            Log::error($ex->getMessage());
                        }
                    }

                    if (isset($fileUrl) && !empty($fileUrl)) {
                        $file_name = $filePrefixName . basename($fileUrl);
                        Log::info('[VBIService SaveFile] File name: ' . $file_name);

                        if (!isset($client)) {
                            $client = new Client();
                        }

                        $savePath = $contract_id . DIRECTORY_SEPARATOR . 'certificate' . DIRECTORY_SEPARATOR . $file_name;

                        // Create file
                        Storage::disk('contract')->put($savePath, '');

                        $localPath = Storage::disk('contract')->getDriver()->getAdapter()->getPathPrefix();
                        // Get file
                        $client->get($fileUrl, ['sink' => $localPath . DIRECTORY_SEPARATOR . $savePath]);

                        $check_file = Storage::disk('contract')->exists($savePath);
                        if ($check_file) {
                            $file = Storage::disk('contract')->url($savePath);
                            $data = ['name' => $file_name, 'url' => $file];
                        } else {
                            $data = [];
                        }

                        return $data;
                    } else {
                        Log::error('[VBI-GetFile] File url not found.');
                        return false;
                    }
                } else {
                    Log::error('[VBI-GetFile] result list empty. ' . json_encode($result));
                    return false;
                }
            } else {
                Log::error('[VBI-GetFile] missing request data.');
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
    public function checkVBI($product_code){
        if(empty($product_code)){
            return false;
        }

        $acceptCode = $this->mode == 'sandbox' ? self::SANDBOX_ACCEPT_CODE : self::ACCEPT_CODE;

        // Get list check code
        $listCheckCode = array_keys($acceptCode);

        if(in_array($product_code, $listCheckCode)){
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
        if(empty($product_code)){
            return false;
        }

        $acceptCode = $this->mode == 'sandbox' ? self::SANDBOX_ACCEPT_CODE : self::ACCEPT_CODE;

        if(isset($acceptCode[$product_code])){
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
        if(empty($product_code)){
            return false;
        }

        $acceptCode = $this->mode == 'sandbox' ? self::SANDBOX_ACCEPT_CODE : self::ACCEPT_CODE;

        if(isset($acceptCode[$product_code])){
            return $acceptCode[$product_code]['file_type'];
        }
        return '';
    }

    protected function getFileUrl($fileKey, $ext = 'pdf')
    {
        return 'https://tracuu.evbi.vn/ViewReports/outputs/' . $fileKey . '.' . $ext;
    }
}
