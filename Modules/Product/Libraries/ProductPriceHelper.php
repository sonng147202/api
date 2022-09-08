<?php
/**
 * Created by PhpStorm.
 * User: phongbd
 * Date: 7/18/17
 * Time: 08:57
 */

namespace Modules\Product\Libraries;


use Carbon\Carbon;
use Carbon\Exceptions\InvalidDateException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Car\Models\CarModelTrim;
use Modules\Car\Models\CarTrimPriceHistory;
use Modules\Car\Models\CarVehicle;
use Modules\Car\Models\CarVehiclePriceHistory;
use App\Models\InsuranceExtraFee;
use App\Models\InsuranceFormula;
use App\Models\InsurancePriceAttribute;
use App\Models\InsurancePriceType;
use App\Models\InsuranceType;
use Modules\Product\Models\Product;
use Modules\Product\Models\ProductPriceCondition;

class ProductPriceHelper
{
    /**
     * @param $condition
     * @param $dataType
     * @return string
     */
    public static function getPriceConditionText($condition, $dataType, $dataTypeValues = [])
    {
        $str = '';

        switch ($condition->attr_operator) {
            case 'between':
                if ($dataType == 'currency') {
                    $condition->attr_min_value = number_format($condition->attr_min_value, 0);
                    $condition->attr_max_value = number_format($condition->attr_max_value, 0);
                }
                $str = 'từ ' . $condition->attr_min_value . ' tới ' . $condition->attr_max_value;
                break;
            case 'greater':
                if ($dataType == 'currency') {
                    $condition->attr_value = number_format($condition->attr_value, 0);
                }
                $str = 'lớn hơn ' . (!empty($dataTypeValues) && isset($dataTypeValues[$condition->attr_value]) ? $dataTypeValues[$condition->attr_value] : $condition->attr_value);
                break;
            case 'less':
                if ($dataType == 'currency') {
                    $condition->attr_value = number_format($condition->attr_value, 0);
                }
                $str = 'nhỏ hơn ' . (!empty($dataTypeValues) && isset($dataTypeValues[$condition->attr_value]) ? $dataTypeValues[$condition->attr_value] : $condition->attr_value);
                break;
            case 'equal':
                if ($dataType == 'currency') {
                    $condition->attr_value = number_format($condition->attr_value, 0);
                }
                $str = ': ' . (!empty($dataTypeValues) && isset($dataTypeValues[$condition->attr_value]) ? $dataTypeValues[$condition->attr_value] : $condition->attr_value);
                break;
        }

        return $str;
    }

    /**
     * @param $filterData
     * @return array
     */
    public static function getFilterData($filterData)
    {
        // List addition params for formula:
        $additionParams = [];
        $conditions = $filterData;
        $moreData = [];

        if (isset($filterData['car_trim'])) {
            // Car insurance
            // Get car_price
            $carTrim = CarModelTrim::where('id', $filterData['car_trim'])->first();

            if ($carTrim) {
                // Get car price first
                $carPrice = CarTrimPriceHistory::where('trim_id', $carTrim->id)
                    ->orderBy('created_at', 'DESC')->first();
                $price = isset($carPrice->price) ? $carPrice->price : $carTrim->price;

                // Get year used
                $yearUsed = date('Y') - $carTrim->year;

                if (!isset($filterData['car_price']) || empty($filterData['car_price'])) {
                    $conditions['car_price'] = (int)$price;
                    // Add car price to addition params
                    $additionParams['car_price'] = (int)$price;
                } else {
                    $conditions['car_price'] = (int)$filterData['car_price'];
                    // Add car price to addition params
                    $additionParams['car_price'] = (int)$filterData['car_price'];
                }
                $conditions['car_use_type'] = isset($filterData['car_use_type']) ? (int)$filterData['car_use_type'] : 0;
                $conditions['car_used_year'] = (int)$yearUsed;
                $conditions['num_seat'] = (int)$carTrim->num_seat;

                // Get more data: car price, car used year, number of seat
                $moreData = [
                    'car_price'     => [
                        'title' => 'Giá trị xe',
                        'value' => (int)$price,
                        'display_value' => number_format($price, 0) . ' VNĐ'
                    ],
                    /*'car_used_year' => [
                        'title' => 'Số năm sử dụng',
                        'value' => (int)$yearUsed,
                        'display_value' => (int)$yearUsed
                    ],*/
                    'car_num_seat'  => [
                        'title' => 'Số chỗ ngồi',
                        'value' => (int)$carTrim->num_seat,
                        'display_value' => (int)$carTrim->num_seat
                    ],
                ];
            }
        } else if (isset($filterData['trip_end']) || isset($filterData['travel_insurance_method'])) {
            // Travel insurance
            // Check insurance method
            if (isset($filterData['travel_insurance_method']) && $filterData['travel_insurance_method'] == 1) {
                // By year
                $conditions['trip_days'] = 365;

                // Add to condition
                $additionParams['trip_days'] = 1;
            } else if (isset($filterData['trip_start']) && isset($filterData['trip_end'])) {
                try {
                    // Get trip_days from trip_start and trip_end
                    $tripStart = Carbon::createFromFormat('d/m/Y', $filterData['trip_start']);
                    $tripEnd = Carbon::createFromFormat('d/m/Y', $filterData['trip_end']);
                    $tripDays = $tripEnd->diffInDays($tripStart) + 1;
                } catch (\Exception $ex) {
                    Log::error('[getProductsPrices]' . $ex->getMessage());

                    $tripStart = Carbon::parse($filterData['trip_start']);
                    $tripEnd = Carbon::parse($filterData['trip_end']);
                    $tripDays = $tripEnd->diffInDays($tripStart) + 1;
                }

                // BH DL
                $conditions['trip_type'] = isset($filterData['trip_type']) ? (int)$filterData['trip_type'] : 0;
                $conditions['trip_days'] = (int)$tripDays;

                // Add to condition
                $additionParams['trip_days'] = (int)$tripDays;
            }
            $moreData = [
                'trip_days' => [
                    'title' => 'Thời gian chuyến đi',
                    'value' => (int)$tripDays,
                    'display_value' => (int)$tripDays . ' ngày.'
                ]];
        } else if (isset($filterData['house_class'])) {
            // House insurance
            $conditions['house_class'] = isset($filterData['house_class']) ? (int)$filterData['house_class'] : 0;
            $conditions['house_used_year'] = isset($filterData['house_create_year']) ? (int)(date('Y') - $filterData['house_create_year']) : 0;
            $conditions['compensate_amount'] = isset($filterData['compensate_amount']) ? (int)$filterData['compensate_amount'] : 0;
            //$conditions['house_own'] = isset($filterData['house_own']) ? (string)$filterData['house_own'] : 0;

            $additionParams['compensate_amount'] = $conditions['compensate_amount'];
        } else if (isset($filterData['date_of_birth'])) {
            // Get age range for healthy insurance
            $date = Carbon::createFromFormat('d/m/Y', $filterData['date_of_birth']);
            $age = $date->diffInYears(Carbon::now()) + 1;

            $conditions['age_range'] = !empty($conditions["age"]) ? (int)$conditions['age'] : (int)$age;
        }

        if(isset($filterData['age_range'])) {
            $conditions['age_range'] =  (int) $conditions['age_range'];
        }

        if(isset($filterData['sex'])) {
            $conditions['sex'] = (string) $conditions['sex'];
        }

        if(isset($filterData['BHTN_time'])) {
            $conditions['BHTN_time'] =  (int) $conditions['BHTN_time'];
        }
        return ['conditions' => $conditions, 'more_data' => $moreData, 'addition_params' => $additionParams];
    }

    /**
     * @param $productIds
     * @param $insuranceTypeId
     * @param $filterData
     * @param array $selectedPriceType
     * @param array $priceTypeCondition
     * @return array
     */
    public static function getProductsPrices($productIds, $insuranceTypeId, $filterData, $selectedPriceType = [], $priceTypeCondition = [])
    {
        // Get prices for products
        $prices = [];
        foreach ($productIds as $productId) {
            $price = self::getProductPrice($productId, $insuranceTypeId, $filterData, $selectedPriceType, $priceTypeCondition);
            if (isset($price['prices']['price'])) {
                $prices[$productId] = [];
                $prices[$productId]['price'] = $price['prices']['price'];
            }
        }

        $filterData = self::getFilterData($filterData);
        return ['prices' => $prices, 'conditions' => $filterData['conditions']];
    }

    /**
     * @param $productId
     * @param $insuranceTypeId
     * @param $filterData
     * @param array $selectedPriceType
     * @param array $priceTypeCondition
     * @return array
     */
    public static function getProductPrice($productId, $insuranceTypeId, $filterData, $selectedPriceType = [], $priceTypeCondition = [], $customPriceTypeHealthInsurance=[])
    {
        // Get price attribute by insurance type id
        // Get formula
        $product = Product::getProduct($productId);
        $insuranceType = InsuranceType::getDetail($insuranceTypeId);

        if (!empty($product->insurance_formula_id)) {
            // Get formula
            $formula = InsuranceFormula::getDetail($product->insurance_formula_id);
        } else {
            $formula = InsuranceFormula::getDefaultFormulaByType($insuranceTypeId);
        }
        // Get price type
        $priceTypes = InsurancePriceType::getListWithCodeAsKey($insuranceTypeId);
        // Get price attributes
        $priceAttributes = InsurancePriceAttribute::getListWithKeyCodeByTypeId($insuranceTypeId);
        $filterData = self::getFilterData($filterData);
        //check neu la bh nam vien
        if($insuranceTypeId == 22){
            $filterData['conditions']['bhnv_age_range'] = $filterData['conditions']['age_range'];
        }
        // if($insuranceTypeId && isset($request->beneficiary)){
        //     foreach ($beneficiary as $key) {
        //         $date_of_birth_beneficiary = $key['date_of_birth'];
        //         $year = date('Y', strtotime($date_of_birth_beneficiary));
        //         $this_day = date('Y', strtotime($this_day));
        //         $year_of_beneficiary = $this_day - $year;
        //     }
        //     $filterData['conditions']['age_range'] = $year_of_beneficiary;
        // }

        $prices = [];
        if (isset($filterData['conditions'])) {
            $conditions = $filterData['conditions'];
            $additionParams = $filterData['addition_params'];
            // Get condition by insurance price attributes
            $acceptConditions = array_keys($priceAttributes);
            if (!empty($conditions)) {
                foreach ($conditions as $key => $item) {
                    if (!in_array($key, $acceptConditions)) {
                        unset($conditions[$key]);
                    }
                }
            }

            $productPrice = self::getProductPriceByConditions($productId, $conditions);
            $productPrice = self::getProductPriceByUnitCustomPriceType($product, $productPrice, $customPriceTypeHealthInsurance);
            if (!empty($productPrice) && !empty($formula)) {
                if (!empty($selectedPriceType) && is_array($selectedPriceType)) {
                    $selectedPriceType = array_values($selectedPriceType);
                } else {
                    Log::info('[getProductPrice] $selectedPriceType is empty');
                }

                // Filter selected price type
                $acceptPriceTypes = array_keys($priceTypes);
                if (!empty($acceptPriceTypes)) {
                    foreach ($selectedPriceType as $key => $code) {
                        if (!in_array($code, $acceptPriceTypes)) {
                            unset($selectedPriceType[$key]);
                        }
                    }
                }

                // Check product price type setting
                if (isset($product->config_price_types)) {
                    $product->config_price_types = json_decode($product->config_price_types, true);
                }

                $prices = [
                    'product_code' => $productPrice['product_code'],
                    'price_id'     => $productPrice['price_id'],
                    'price'        => 0
                ];

                $listPriceType = [];
                // Get active condition only
                foreach ($priceTypes as $code => $item) {
                    if ((empty($selectedPriceType) && isset($item['default_require']) && $item['default_require'] == 1) || in_array($code, $selectedPriceType)) {
                        // Calc for price_type with use type = 0
                        if ($item['use_type'] == InsurancePriceType::USE_TYPE_BY_ATTR_CONDITIONS) {
                            $listPriceType[$code] = isset($productPrice[$code]) ? $productPrice[$code] : 0;
                        } else {
                            // Process for InsurancePriceType::USE_TYPE_BY_PRODUCT
                            // Check product config first
                            $conditionValue = isset($priceTypeCondition[$code]) ? $priceTypeCondition[$code] : 0;
                            if (isset($product->config_price_types) && isset($product->config_price_types[$item['code']])) {
                                $listPriceType[$code] = self::_getPriceWithListOptions($conditionValue, $product->config_price_types[$item['code']]);
                            } else {
                                // Get by default
                                $listPriceType[$code] = self::_getPriceWithListOptions($conditionValue, json_decode($item['default_value'], true));
                            }
                        }
                    }  else {
                        $listPriceType[$code] = 0;
                    }
                }
                // Get current price
                // Replace with addition params
                if (!empty($additionParams)) {
                    $formulaStr = str_replace(array_keys($additionParams), array_values($additionParams), $formula->formula);
                } else {
                    $formulaStr = $formula->formula;
                }

                // Replace params in formula by price_params
                //$priceParams = array_keys($listPriceType);
                //$paramValues = array_values($listPriceType);

                //$formulaStr = str_replace($priceParams, $paramValues, $formulaStr);
                $formulaStr = strtr($formulaStr, $listPriceType);
                $formulaStr = preg_replace('/[a-zA-Z]/', '', $formulaStr);//remove all letters from string

                // Get price
                $parser = new FormulaParser($formulaStr);
                $result = $parser->getResult();

                if (isset($result[0]) && $result[0] == 'done') {
                    $currPrice = $result[1];
                } else {
                    $currPrice = 0;
                }

                $price = $currPrice;

                if (isset($productPrice['price_type']) && $productPrice['price_type'] > 0) {
                    // Get price attribute key
                    $priceConditions = ProductPriceCondition::getDetailByPrice($productPrice['price_id']);
                    $fields = [];
                    if ($priceConditions) {
                        foreach ($priceConditions as $priceCondition) {
                            if (in_array($priceCondition->attr_operator, ['less', 'greater'])) {
                                $fields[$priceCondition->attr_operator][$priceCondition->attr_key] = $priceCondition->attr_value;
                            }
                        }
                    }

                    // Process for greater first
                    if (isset($fields['greater']) || !empty($fields['greater'])) {
                        foreach ($fields['greater'] as $key => $value) {
                            // Check current condition values
                            if (isset($conditions[$key])) {
                                // Get max price
                                $tmpConditions = $conditions;
                                $tmpConditions[$key] = $value;
                                $priceConditions = self::geProductsPricesByConditions([$productId], $tmpConditions);
                                $maxPrice = 0;
                                if (!empty($priceConditions)) {
                                    $priceConditions = $priceConditions[$productId];
                                    // Get list price type
                                    $tmpData = [];
                                    // Get active condition only
                                    foreach ($priceTypes as $code => $item) {
                                        if ((empty($selectedPriceType) && isset($item['default_require']) && $item['default_require'] == 1) || in_array($code, $selectedPriceType)) {
                                            $tmpData[$code] = isset($priceConditions[$code]) ? $priceConditions[$code] : 0;
                                        } else {
                                            $tmpData[$code] = 0;
                                        }
                                    }

                                    $maxPrice = self::getPriceByFormula($tmpData, $formula, $additionParams);
                                }

                                // Get more price
                                $additionValue = $conditions[$key] - $value;
                                $plusPrice = 0;
                                switch ($productPrice['price_type']) {
                                    case 1:
                                        // Days
                                        $plusPrice = $additionValue * $currPrice;
                                        break;
                                    case 2:
                                        // Weeks
                                        $plusPrice = ceil($additionValue / 7) * $currPrice;
                                        break;
                                    case 3:
                                        // Months
                                        $plusPrice = ceil($additionValue / 30) * $currPrice;
                                        break;
                                    case 4:
                                        // Years
                                        $plusPrice = ceil($additionValue / 365) * $currPrice;
                                        break;
                                }

                                $price = $maxPrice + $plusPrice;
                            }
                        }
                    }
                }

                $prices['price'] = $price;
            }
        }

        // Get price with tax
        if ($insuranceType->vat > 0 && isset($prices['price'])) {
            $prices['tax'] = $insuranceType->vat;
            $prices['tax_amount'] = $prices['price'] * (int)$insuranceType->vat / 100;
            $prices['price_with_tax'] = (float)$prices['tax_amount'] + (float)$prices['price'];
        }
        return ['prices' => $prices, 'conditions' => $filterData['conditions']];
    }

    /**
     * @param $extraFeeId
     * @param $filterData
     * @param $extraFeeAttributes
     * @param int $productId
     * @return array
     */
    public static function getExtraFeePrice($extraFeeId, $filterData, $extraFeeAttributes, $productId = 0)
    {
        // Get price attribute by insurance type id
        // Get default formula
        if (is_numeric($extraFeeId)) {
            $extraFee = InsuranceExtraFee::getDetail($extraFeeId);
        } else {
            $extraFee = InsuranceExtraFee::getDetailByCode($extraFeeId);
        }
        if ($extraFee) {
            $filterData = self::getFilterData($filterData);
            $price = 0;
            if (isset($filterData['conditions'])) {
                $conditions = $filterData['conditions'];

                $extraFeeCnf = [];
                // Get config by product id
                if (!empty($productId)) {
                    // Get extra_fee config from product
                    $product = Product::getProduct($productId);
                    if (!empty($product->extra_fees)) {
                        $extraFeeCnf = json_decode($product->extra_fees, true);
                    }
                }
                // Replace with addition params
                if (!empty($extraFeeAttributes)) {
                    $formulaStr = str_replace(array_keys($extraFeeAttributes), array_values($extraFeeAttributes), $extraFee->formula);
                } else {
                    $formulaStr = $extraFee->formula;
                }

                // Replace extra_fee config to formula
                if (!empty($extraFeeCnf)) {
                    $formulaStr = str_replace(array_keys($extraFeeCnf), array_values($extraFeeCnf), $formulaStr);
                }

                // Replace params in formula by price_params
                $priceParams = array_keys($conditions);
                $paramValues = array_values($conditions);

                $formulaStr = str_replace($priceParams, $paramValues, $formulaStr);

                // Get price
                $parser = new FormulaParser($formulaStr);
                $result = $parser->getResult();

                if (isset($result[0]) && $result[0] == 'done') {
                    $price = $result[1];
                }
            }

            // Get price str
            if ($price < 100) {
                $priceStr = number_format($price, 1);
            } else if ($price == 0) {
                $priceStr = 0;
            } else {
                $priceStr = number_format($price, 0);
            }

            return ['price' => $price, 'price_str' => $priceStr, 'conditions' => $filterData['conditions']];
        } else {
            return false;
        }
    }

    /**
     * @param $productIds
     * @param $conditions
     * @return array|bool
     */
    protected static function geProductsPricesByConditions($productIds, $conditions)
    {
        if ($conditions) {
            $query = DB::table('mp_product_prices')->select('mp_product_prices.*', 'mp_products.code as p_code')
                ->leftJoin('mp_products', 'mp_products.id', '=', 'mp_product_prices.product_id')
                ->whereIn('mp_product_prices.product_id', $productIds);

            foreach ($conditions as $key => $value) {
                $query->leftJoin('mp_product_price_conditions as condition_' . $key, 'mp_product_prices.id', '=', 'condition_' . $key . '.price_id')
                    ->where('condition_' . $key . '.attr_key', $key)
                    ->where(function ($query) use ($key, $value) {
                        $query->where(function ($query) use ($key, $value) {
                            $query->where('condition_' . $key . '.attr_value', $value)
                                ->where('condition_' . $key . '.attr_operator', 'equal');
                            })
                            ->orWhere(function ($query) use ($key, $value) {
                                $query->where('condition_' . $key . '.attr_max_value', '>=', $value)
                                    ->where('condition_' . $key . '.attr_min_value', '<=', $value)
                                    ->where('condition_' . $key . '.attr_operator', 'between');
                            })
                            ->orWhere(function ($query) use ($key, $value) {
                                $query->where('condition_' . $key . '.attr_value', '>', $value)
                                    ->where('condition_' . $key . '.attr_operator', 'less');
                            })
                            ->orWhere(function ($query) use ($key, $value) {
                                $query->where('condition_' . $key . '.attr_value', '<', $value)
                                    ->where('condition_' . $key . '.attr_operator', 'greater');
                            });
                    });
            }

            $listPrice = $query->get();
            if (!empty($listPrice)) {
                // Group list price by product
                $tmpData = [];
                foreach ($listPrice as $item) {
                    if (!isset($tmpData[$item->product_id])) {
                        $tmpData[$item->product_id] = [];
                    }
                    $arrPrice = json_decode($item->price_detail, true);
                    foreach ($arrPrice as $typeCode => $value) {
                        $tmpData[$item->product_id][$typeCode] = $value;
                    }

                    $tmpData[$item->product_id]['product_code'] = isset($item->product_code) && !empty($item->product_code) ? $item->product_code : $item->p_code;
                    $tmpData[$item->product_id]['price_id'] = isset($item->id) ? $item->id : 0;
                    $tmpData[$item->product_id]['price_type'] = isset($item->price_type) ? $item->price_type : 0;
                }

                return $tmpData;
            }
        }

        return false;
    }

    /**
     * @param $productId
     * @param $conditions
     * @return array|bool
     */
    protected static function getProductPriceByConditions($productId, $conditions)
    {
        if ($conditions) {
            // Get list config attribute keys for this product
            $attributeKeys = ProductPriceCondition::getListAttributeKeyByProduct($productId);
            $query = DB::table('mp_product_prices')->select('mp_product_prices.*', 'mp_products.code as p_code')
                ->leftJoin('mp_products', 'mp_products.id', '=', 'mp_product_prices.product_id')
                ->where('mp_product_prices.product_id', $productId);
            foreach ($conditions as $key => $value) {
                if($value==null){
                    return false;
                }
                if (in_array($key, $attributeKeys)) {
                    $query->leftJoin('mp_product_price_conditions as condition_' . $key, 'mp_product_prices.id', '=', 'condition_' . $key . '.price_id')
                        ->where('condition_' . $key . '.attr_key', $key)
                        ->where(function ($query) use ($key, $value) {
                            $query->where(function ($query) use ($key, $value) {
                                $query->where('condition_' . $key . '.attr_value', '=', $value)
                                    ->where('condition_' . $key . '.attr_operator', 'equal');
                            })
                                ->orWhere(function ($query) use ($key, $value) {
                                    $query->where('condition_' . $key . '.attr_max_value', '>=', $value)
                                        ->where('condition_' . $key . '.attr_min_value', '<=', $value)
                                        ->where('condition_' . $key . '.attr_operator', 'between');
                                })
                                ->orWhere(function ($query) use ($key, $value) {
                                    $query->where('condition_' . $key . '.attr_value', '>', $value)
                                        ->where('condition_' . $key . '.attr_operator', 'less');
                                })
                                ->orWhere(function ($query) use ($key, $value) {
                                    $query->where('condition_' . $key . '.attr_value', '<', $value)
                                        ->where('condition_' . $key . '.attr_operator', 'greater');
                                });
                        });
                }
            }
            $listPrice = $query->get();
            // dd($listPrice);
            if (!empty($listPrice)) {
                // Group list price by product
                $tmpData = [];
                foreach ($listPrice as $item) {
                    $arrPrice = json_decode($item->price_detail, true);
                    foreach ($arrPrice as $typeCode => $value) {
                        $tmpData[$typeCode] = $value;
                    }
                    $tmpData['product_code'] = isset($item->product_code) && !empty($item->product_code) ? $item->product_code : $item->p_code;
                    $tmpData['price_id'] = isset($item->id) ? $item->id : 0;
                    $tmpData['price_type'] = isset($item->price_type) ? $item->price_type : 0;

                }
                return $tmpData;
            }
        }
        return false;
    }

    /**
     * @param $listPriceType
     * @param InsuranceFormula $formula
     * @param $additionParams
     * @return int
     */
    protected static function getPriceByFormula($listPriceType, InsuranceFormula $formula, $additionParams)
    {
        // Replace with addition params
        if (!empty($additionParams)) {
            $formulaStr = str_replace(array_keys($additionParams), array_values($additionParams), $formula->formula);
        } else {
            $formulaStr = $formula->formula;
        }

        // Replace params in formula by price_params
        $priceParams = array_keys($listPriceType);
        $paramValues = array_values($listPriceType);

        $formulaStr = str_replace($priceParams, $paramValues, $formulaStr);

        // Get price
        $parser = new FormulaParser($formulaStr);
        $result = $parser->getResult();

        if (isset($result[0]) && $result[0] == 'done') {
            return $result[1];
        }

        return 0;
    }

    /**
     * Get price with list options and range value. For price_type
     *
     * @param $value
     * @param $options
     * @return int
     */
    protected static function _getPriceWithListOptions($value, $options)
    {
        $price = 0;
        $matchValue = 0;
        foreach ($options as $option) {
            if (isset($option['compare_value'])) {
                if ($option['compare_value'] == $value) {
                    $price = $option['price'];
                    $matchValue = $option['compare_value'];
                    break;
                } else if ($option['compare_value'] >= $value && ($matchValue == 0 || $matchValue > $option['compare_value'])) {
                    $price = $option['price'];
                    $matchValue = $option['compare_value'];
                }
            }
        }

        return $price;
    }

    protected static function getProductPriceByUnitCustomPriceType($product, $productPrice, $customPriceTypeHealthInsurance)
    {
        if (!empty($product->unit_price_type_health_insurance)) {
            $unit_price_type_health_insurance = \GuzzleHttp\json_decode($product->unit_price_type_health_insurance, true);
            foreach ($unit_price_type_health_insurance as $code=>$value) {
                if (!empty($productPrice[$code]) && isset($customPriceTypeHealthInsurance[$code]) && !empty($value)) {
                    $productPrice[$code] = ceil($productPrice[$code] * $customPriceTypeHealthInsurance[$code]/100);
                }
            }
        }
        return $productPrice;


    }
}
