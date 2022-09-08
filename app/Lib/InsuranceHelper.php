<?php

namespace Modules\Api\Lib;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Modules\Core\Lib\PermissionHelper;
use Modules\Core\Lib\Service;
use Carbon\Carbon;
use Modules\Insurance\Jobs\SendQuotation;
use Modules\Insurance\Jobs\SendQuotationAgency;
use Modules\Insurance\Jobs\SendQuotationEmail;
use Modules\Insurance\Models\CarBrand;
use Modules\Insurance\Models\CarModel;
use Modules\Insurance\Models\CarModelTrim;
use Modules\Insurance\Models\Customer;
use Modules\Insurance\Models\CustomerType;
use Modules\Insurance\Models\InsuranceAgency;
use Modules\Insurance\Models\InsuranceContract;
use Modules\Insurance\Jobs\GetFile;
use Modules\Insurance\Models\InsurancePriceAttribute;
use Modules\Insurance\Models\InsuranceQuotation;
use App\Models\InsuranceType;
use Modules\Insurance\Notifications\QuotationSent;
use Modules\Product\Libraries\ProductPriceHelper;
use Modules\Product\Models\Product;

class InsuranceHelper
{
    /**
     * @param $filterData
     * @param $insuranceTypeId
     */
    public static function getFilterPriceDataToString($filterData, $insuranceTypeId)
    {
        // Get price attribute by insurance type id
        $priceAttributes = self::convertPriceAttributesToFilterElms($insuranceTypeId);

        if (!empty($filterData)) {
            if (!is_array($filterData)) {
                $filterData = json_decode($filterData, true);
            }

            $data = [];
            if ($filterData) {
                foreach ($filterData as $key => $value) {
                    if (isset($priceAttributes[$key])) {
                        $data[$priceAttributes[$key]['title']] = self::getFilterDataValue($priceAttributes[$key], $value);
                    }
                }
            }

            return $data;
        }

        return false;
    }

    /**
     * @param $insuranceTypeId
     * @param bool $addContractTime
     * @param bool $getOptionAsArray
     * @return array
     */
    public static function getFilterForm($insuranceTypeId, $addContractTime = false)
    {
        // Get price attributes
        $priceAttributes = InsurancePriceAttribute::getListWithKeyCodeByTypeId($insuranceTypeId);

        $elements = [];

        foreach ($priceAttributes as $priceAttribute) {
            if ($priceAttribute['show_in_filter_form'] == 1) {
                if ($priceAttribute['code'] == 'car_price') {
                    $carBrands = CarBrand::getListBrand();
                    // Add car use type
                    $elements[] = [
                        'type' => 'select',
                        'name' => 'car_use_type',
                        'title' => 'Mục đích sử dụng',
                        'default_value' => '',
                        'is_require' => 1,
                        'options' => [
                            [
                                'value' => 0,
                                'title' => 'Không kinh doanh'
                            ],
                            [
                                'value' => 1,
                                'title' => 'Kinh doanh vận tải'
                            ]
                        ],
                        'is_filter' => 1
                    ];
                    // Add car brand
                    $options = [];
                    foreach ($carBrands as $key => $value) {
                        $options[] = [
                            'value' => $key,
                            'title' => $value
                        ];
                    }
                    $elements[] = [
                        'type' => 'select',
                        'name' => 'car_brand',
                        'title' => 'Hãng sản xuất',
                        'default_value' => '',
                        'is_require' => 1,
                        'options' => $options,
                        'is_filter' => 1
                    ];
                    // Add car model
                    $elements[] = [
                        'type' => 'select',
                        'name' => 'car_model',
                        'title' => 'Dòng xe',
                        'default_value' => '',
                        'is_require' => 1,
                        'options' => [],
                        'is_filter' => 1,
                        'request_url' => config('core.api_url') . '/api/v1/car/models',
                        'require_params' => 'car_brand'
                    ];
                    // Add car year manufacture
                    $elements[] = [
                        'type' => 'select',
                        'name' => 'year_manufacture',
                        'title' => 'Năm sản xuất',
                        'default_value' => '',
                        'is_require' => 1,
                        'options' => [],
                        'is_filter' => 1,
                        'request_url' => config('core.api_url') . '/api/v1/car/year-manufactures',
                        'require_params' => 'car_model'
                    ];
                    // Add car trim
                    $elements[] = [
                        'type' => 'select',
                        'name' => 'car_trim',
                        'title' => 'Phiên bản',
                        'default_value' => '',
                        'is_require' => 1,
                        'options' => [],
                        'is_filter' => 1,
                        'request_url' => config('core.api_url') . '/api/v1/car/trims',
                        'require_params' => 'year_manufacture,car_model'
                    ];
                } elseif ($priceAttribute['code'] == 'car_used_year') {

                } elseif ($priceAttribute['code'] == 'age_range') {
                    $elements[] = [
                        'type' => 'number',
                        'name' => 'age_range',
                        'title' => 'Độ tuổi',
                        'default_value' => '',
                        'is_require' => 1,
                        'options' => [],
                        'is_filter' => 1
                    ];
                } elseif ($priceAttribute['code'] == 'trip_days') {
                    $elements[] = [
                        'type' => 'date',
                        'name' => 'trip_start',
                        'title' => 'Ngày đi',
                        'default_value' => '',
                        'is_require' => 1,
                        'options' => [],
                        'is_filter' => 1
                    ];
                    $elements[] = [
                        'type' => 'date',
                        'name' => 'trip_end',
                        'title' => 'Ngày về',
                        'default_value' => '',
                        'is_require' => 1,
                        'options' => [],
                        'is_filter' => 1
                    ];
                } elseif ($priceAttribute['code'] == 'house_used_year') {
                    $elements[] = [
                        'type' => 'year',
                        'name' => 'house_create_year',
                        'title' => 'Năm xây dựng',
                        'default_value' => '',
                        'is_require' => 1,
                        'options' => [],
                        'is_filter' => 1
                    ];
                } else {
                    $options = [];
                    if ($priceAttribute['data_type'] == 'select') {
                        $arr1 = explode(';', $priceAttribute['default_value']);
                        foreach ($arr1 as $arr) {
                            $arr2 = explode(':', $arr);
                            $options[] = [
                                'value' => $arr2[0],
                                'title' => $arr2[1]
                            ];
                        }
                    }

                    $elements[] = [
                        'type' => $priceAttribute['data_type'],
                        'name' => $priceAttribute['code'],
                        'title' => $priceAttribute['title'],
                        'default_value' => '',
                        'is_require' => 1,
                        'options' => $options,
                        'is_filter' => 1
                    ];
                }
            }
        }

        if ($addContractTime === true) {
            if ($insuranceTypeId == 1) {//if insruance type health
                $optionYearInterVal =  [
                    [
                        'value' => '1',
                        'title' => '1 năm'
                    ]
                ];
            } else {
                $optionYearInterVal =  [
                    [
                        'value' => '1',
                        'title' => '1 năm'
                    ],
                    [
                        'value' => '2',
                        'title' => '2 năm'
                    ],
                    [
                        'value' => '3',
                        'title' => '3 năm'
                    ]
                ];
            }

            $insuranceType = InsuranceType::getDetail($insuranceTypeId);
            // Add contract time to filter form
            switch ($insuranceType->fee_interval_type) {
                case 'days':
                    break;
                case 'years':
                    $elements[] = [
                        'type' => 'date',
                        'name' => 'start_time',
                        'title' => 'Ngày bắt đầu hợp đồng',
                        'default_value' => '',
                        'is_require' => 1,
                        'options' => [],
                        'is_filter' => 0
                    ];

                    $elements[] = [
                        'type' => 'select',
                        'name' => 'year_interval_value',
                        'title' => 'Thời gian ký hợp đồng',
                        'default_value' => '',
                        'is_require' => 1,
                        'options' => $optionYearInterVal,
                        'is_filter' => 0
                    ];
                    break;
            }
        }

        return $elements;
    }

    /**
     * Get value for one filter data item
     * @param $priceAttribute
     * @param $filterDataValue
     * @return mixed|string
     */
    protected static function getFilterDataValue($priceAttribute, $filterDataValue)
    {
        if (!empty($priceAttribute) && !empty($filterDataValue)) {
            // Check attribute data type
            switch ($priceAttribute['data_type']) {
                case 'select':
                    // Get list value
                    switch ($priceAttribute['code']) {
                        case 'car_brand':
                            $values = CarBrand::getListBrand();
                            break;
                        case 'car_model':
                            $values = CarModel::getName($filterDataValue);
                            break;
                        case 'year_manufacture':
                            $values = $filterDataValue;
                            break;
                        case 'car_trim':
                            $values = CarModelTrim::getName($filterDataValue);
                            break;
                        default:
                            $values = [];
                            $arr1 = explode(';', $priceAttribute['default_value']);
                            if (!empty($arr1)) {
                                foreach ($arr1 as $arr) {
                                    $arr2 = explode(':', $arr);
                                    if (isset($arr2[0]) && isset($arr2[1])) {
                                        $values[$arr2[0]] = $arr2[1];
                                    }
                                }
                            }

                            break;
                    }

                    if (is_array($values)) {
                        if (array_key_exists($filterDataValue, $values)) {
                            return $values[$filterDataValue];
                        }
                    } else {
                        return $values;
                    }

                    break;
                default:
                    return $filterDataValue;
                    break;
            }
        }

        return '';
    }

    public static function convertPriceAttributesToFilterElms($insuranceTypeId)
    {
        // Get all price attributes
        $priceAttributes = InsurancePriceAttribute::getListWithKeyCodeByTypeId($insuranceTypeId);

        foreach ($priceAttributes as $key => $item) {
            switch ($key) {
                case 'trip_days':
                    // Split to trip_start and trip_end
                    $priceAttributes['trip_start'] = [
                        'code' => 'trip_start',
                        'title' => 'Ngày đi',
                        'data_type' => 'date',
                        'default_value' => ''
                    ];
                    $priceAttributes['trip_end'] = [
                        'code' => 'trip_end',
                        'title' => 'Ngày về',
                        'data_type' => 'date',
                        'default_value' => ''
                    ];
                    unset($priceAttributes[$key]);
                    break;
                case 'car_price':
                    // Split to trip_start and trip_end
                    $priceAttributes['car_brand'] = [
                        'code' => 'car_brand',
                        'title' => 'Hãng xe',
                        'data_type' => 'select',
                        'default_value' => ''
                    ];
                    $priceAttributes['car_model'] = [
                        'code' => 'car_model',
                        'title' => 'Dòng xe',
                        'data_type' => 'select',
                        'default_value' => ''
                    ];
                    $priceAttributes['year_manufacture'] = [
                        'code' => 'year_manufacture',
                        'title' => 'Năm sản xuất',
                        'data_type' => 'select',
                        'default_value' => ''
                    ];
                    $priceAttributes['car_trim'] = [
                        'code' => 'car_trim',
                        'title' => 'Phiên bản',
                        'data_type' => 'select',
                        'default_value' => ''
                    ];
                    unset($priceAttributes[$key]);
                    break;
                case 'car_used_year':
                    unset($priceAttributes[$key]);
                    break;
            }
        }

        return $priceAttributes;
    }

    /**
     * @param $insuranceQuotationId
     * @param $customerId
     * @return array
     */
    public static function sendQuotation($insuranceQuotationId, $customerId)
    {
        // Check quotation and customer_id is exist. Check current user is manager of request customer.
        if (InsuranceQuotation::isExists($insuranceQuotationId)) {
            $customer = Customer::getDetail($customerId);
            if ($customer) {
                $quotation = InsuranceQuotation::getDetail($insuranceQuotationId);
                // Send quotation
                $userId = Auth::user() ? Auth::user()->id : 0;
                dispatch(new SendQuotation($customerId, $insuranceQuotationId, $userId));

                // Sent notification to customer
                Notification::send($customer, new QuotationSent($insuranceQuotationId, ['push_mobile']));

                $quotation->customer_id = $customer->id;
                $quotation->save();

                return ['success' => true, 'message' => 'Báo giá đã được gửi tới khách hàng.',
                    'quotation_id' => $quotation->id,
                    'customer' => [
                        'id'   => $customer->id,
                        'url'  => route('insurance.customer.view', ['id' => $customerId]),
                        'name' => $customer->name
                    ]];
            } else {
                return ['success' => false, 'message' => 'Không tìm thấy thông tin khách hàng.'];
            }
        } else {
            return ['success' => false, 'message' => 'Không tìm thấy thông tin báo giá.'];
        }
    }

    /**
     * @param $insuranceQuotationId
     * @param $agencyId
     * @return array
     */
    public static function sendQuotationAgency($insuranceQuotationId, $agencyId)
    {
        // Check quotation is exist.
        if (InsuranceQuotation::isExists($insuranceQuotationId)) {
            $agency = InsuranceAgency::getDetail($agencyId);
            $quotation = InsuranceQuotation::getDetail($insuranceQuotationId);

            $userId = Auth::user() ? Auth::user()->id : 0;
            // Send quotation
            dispatch(new SendQuotationAgency($agencyId, $insuranceQuotationId, $userId));

            $quotation->agency_id = $agency->id;
            $quotation->save();

            return ['success' => true, 'message' => 'Báo giá đã được gửi tới đại lý.',
                'quotation_id' => $quotation->id,
                'agency' => [
                    'id'   => $agency->id,
                    'url'  => route('insurance.agency.view', ['id' => $agencyId]),
                    'name' => $agency->name
                ]];
        } else {
            return ['success' => false, 'message' => 'Không tìm thấy thông tin báo giá.'];
        }
    }

    /**
     * @param $insuranceQuotationId
     * @param $email
     * @param int $sendUserId
     * @return array
     */
    public static function sendQuotationEmail($insuranceQuotationId, $email, $sendUserId = 0)
    {
        // Check quotation is exist.
        if (InsuranceQuotation::isExists($insuranceQuotationId)) {
            $quotation = InsuranceQuotation::getDetail($insuranceQuotationId);

            // Send quotation
            dispatch(new SendQuotationEmail($email, $insuranceQuotationId, $sendUserId));

            return [
                'success' => true, 'message' => 'Báo giá đã được gửi tới địa chỉ email.',
                'quotation_id' => $quotation->id
            ];
        } else {
            return ['success' => false, 'message' => 'Không tìm thấy thông tin báo giá.'];
        }
    }

    /**
     * @param $insuranceTypeId
     * @param $filterData
     * @param int $productId
     * @param array $beneficiaries
     * @param array $priceTypes
     * @param array $extraProducts
     * @param array $extraFees
     * @param array $extraFeeAttributes
     * @param bool $useMainFee
     * @return array|bool
     */
    public static function getContractPrices($insuranceTypeId, $filterData, $productId = 0, $beneficiaries = [], $priceTypes = [], $extraProducts = [], $extraFees = [], $extraFeeAttributes = [], $useMainFee = true)
    {
        $productPrice = [];
        // Format data
        if (!is_array($filterData)) {
            $filterData = json_decode($filterData, true);
        }

        if (!is_array($beneficiaries)) {
            $beneficiaries = json_decode($beneficiaries, true);
        }

        // Check insurance type and apply_fee_type
        $insuranceType = InsuranceType::getDetail($insuranceTypeId);

        if ($insuranceType) {
            $discountAmount = 0;
            $netAmount = $grossAmount = 0;
            $taxAmount = 0;
            switch ($insuranceType->apply_fee_type) {
                case InsuranceType::APPLY_FEE_TYPE_BENEFICIARY:
                    if (is_array($beneficiaries) && !empty($beneficiaries)) {
                        foreach ($beneficiaries as $beneficiary) {
                            // Check beneficiary product and price type
                            if (!empty($beneficiary['product_id'])) {
                                // Get beneficiary price type
                                $priceTypes = [];
                                if (isset($beneficiary['price_types'])) {
                                    if (!is_array($beneficiary['price_types'])) {
                                        $priceTypes = explode(',', $beneficiary['price_types']);
                                    } else {
                                        $priceTypes = $beneficiary['price_types'];
                                    }
                                } else if (isset($beneficiary['price_type'])) {
                                    if (!is_array($beneficiary['price_type'])) {
                                        $priceTypes = explode(',', $beneficiary['price_type']);
                                    } else {
                                        $priceTypes = $beneficiary['price_type'];
                                    }
                                }
                                // Get price with selected price types
                                $productPrice = ProductPriceHelper::getProductPrice($beneficiary['product_id'], $insuranceTypeId, $beneficiary, $priceTypes);
                                if (!isset($productPrice['prices']['price']) || empty($productPrice['prices']['price'])) {
                                    //return ['success' => false, 'message' => 'Không lấy được phí bảo hiểm cho đối tượng bảo hiểm'];
                                }
                                $netAmount += isset($productPrice['prices']['price']) ? (float)$productPrice['prices']['price'] : 0;
                            }
                        }
                    } else {
                        Log::info('[getContractPrices APPLY_FEE_TYPE_BENEFICIARY] List beneficiary is empty');
                    }

                    $taxAmount = ((int)$insuranceType->vat / 100) * $netAmount;

                    if (!empty($insuranceType->vat)) {
                        $grossAmount = $taxAmount + $netAmount;
                    } else {
                        $grossAmount = $netAmount;
                    }
                    break;
                case InsuranceType::APPLY_FEE_TYPE_CONTRACT:
                    // Get main fee amount
                    $mainFee = [];
                    if (!empty($productId) && $useMainFee == true) {
                        if (!empty($priceTypes) && !is_array($priceTypes)) {
                            $priceTypes = explode(',', $priceTypes);
                        }

                        $productPrice = ProductPriceHelper::getProductPrice($productId, $insuranceTypeId, $filterData, $priceTypes);
                        if (isset($productPrice['prices']) && !empty($productPrice['prices'])) {
                            $mainFee = [
                                'product_code' => isset($productPrice['prices']['product_code']) ? $productPrice['prices']['product_code'] : '',
                                'product_price' => isset($productPrice['prices']['price']) ? $productPrice['prices']['price'] : 0,
                                'product_price_str' => isset($productPrice['prices']['price']) ? number_format($productPrice['prices']['price'], 0) : 0
                            ];
                        }
                    }

                    $totalBeneficiary = count($beneficiaries);

                    // Get net-amount, gross-amount
                    $netAmount = $totalBeneficiary * (isset($mainFee['product_price']) ? $mainFee['product_price'] : 0);

                    $taxAmount = ((int)$insuranceType->vat / 100) * $netAmount;

                    if (!empty($insuranceType->vat)) {
                        $grossAmount = $taxAmount + $netAmount;
                    } else {
                        $grossAmount = $netAmount;
                    }
                    break;
            }

            // Get extra products amount
            $extraProductsAmount = 0;
            $extraProductPrices = [];
            if (!empty($extraProducts)) {
                foreach ($extraProducts as $extraProduct) {
                    // Get extra product insuracce type
                    $product = Product::getProduct($extraProduct);
                    if ($product) {
                        $typeId = $product->insurance_type->insurance_type_id;
                        $productPrice = ProductPriceHelper::getProductPrice($extraProduct, $typeId, $filterData, $priceTypes);
                        if (isset($productPrice['prices']['price'])) {
                            // Add tax
                            $_insuranceType = $product->insurance_type->insurance_type;
                            if ($_insuranceType->vat > 0) {
                                $price = (float)$productPrice['prices']['price'] + ((float)$productPrice['prices']['price'] * $_insuranceType->vat / 100);
                            } else {
                                $price = (float)$productPrice['prices']['price'];
                            }
                            $extraProductPrices[$extraProduct] = $price;
                            $extraProductsAmount += $price;
                        }
                    }
                }
            }

            // Get extra fees amount
            $extraFeeAmounts = 0;
            $extraFeePrices = [];
            if (!empty($extraFees)) {
                foreach ($extraFees as $extraFeeId) {
                    $price = ProductPriceHelper::getExtraFeePrice($extraFeeId, $filterData, $extraFeeAttributes, $productId);
                    if (isset($price['price'])) {
                        $extraFeePrices[$extraFeeId] = $price;
                        $extraFeeAmounts += (float)$price['price'];
                    }
                }
            }

            $requirePayAmount = (isset($grossAmount) ? (float)$grossAmount : 0) + $extraFeeAmounts + $extraProductsAmount;

            $productHealthPrice = 0;
            if (!empty($productPrice)) {
                if (!empty($productPrice['prices']['price'])) {
                    $productHealthPrice = $productPrice['prices']['price'];
                }
            }
            return [
                'product_price'        => isset($mainFee['product_price']) ? $mainFee['product_price'] : 0,
                'product_code'         => isset($mainFee['product_code']) ? $mainFee['product_code'] : '',
                'net_amount'           => $netAmount,
                'gross_amount'         => $grossAmount,
                'tax'                  => (int)$insuranceType->vat,
                'tax_amount'           => $taxAmount,
                'discount'             => 0,
                'discount_amount'      => $discountAmount,
                'require_pay_amount'   => $requirePayAmount,
                'extra_fee_prices'     => $extraFeePrices,
                'extra_product_prices' => $extraProductPrices,
                'product_health_price' => $productHealthPrice
            ];
        } else {
            return false;
        }
    }
}