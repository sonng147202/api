<?php
/**
 * Created by PhpStorm.
 * User: phongbd
 * Date: 7/18/17
 * Time: 08:57
 */

namespace Modules\Product\Libraries;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Modules\Car\Models\CarVehicle;
use Modules\Car\Models\CarVehiclePriceHistory;
use Modules\Insurance\Models\InsuranceExtraFee;
use Modules\Insurance\Models\InsurancePriceAttribute;
use Modules\Insurance\Models\InsurancePriceType;
use Modules\Insurance\Models\InsuranceType;
use Modules\Product\Models\Category;
use Modules\Product\Models\CategoryAttribute;
use Modules\Product\Models\CategoryClass;
use Modules\Product\Models\Product;

class ProductHelper
{
    /**
     * @param $insuranceTypeId
     * @param $filterData
     * @return array
     */
    public static function getListProduct($insuranceTypeId, $filterData)
    {
        if (!isset($filterData['category']) || empty($filterData['category'])) {
            $category = Category::getByInsurance($insuranceTypeId);
            $filterData['category'] = isset($category->id) ? $category->id : 0;
        }

        // Check child categories
        $childCategories = Category::getListChild($filterData['category']);

        // Get category multi level
        $categoryIds = [(int)$filterData['category']];
        if ($childCategories) {
            foreach ($childCategories as $cate) {
                $categoryIds[] = $cate->id;
            }
        }

        // Get list classes
        $classes = CategoryClass::getListClassByCategory($filterData['category']);

        // Get compare attribute by category
        $compareAttributes = CategoryAttribute::getListCompareAttributes($filterData['category']);

        if ($compareAttributes) {
            $tmpData = [];

            // Set attribute id as array key
            foreach ($compareAttributes as $attribute) {
                $tmpData[$attribute->id] = $attribute;
            }

            $compareAttributes = $tmpData;
        }

        // Get product with current filter info
        $paramsProduct = ['category_ids' => $categoryIds];
        if(isset($filterData['status'])){
            $paramsProduct['status'] = $filterData['status'];
        }
        $products = Product::getProducts($paramsProduct, true);

        // Reformat list product attribute, group product by category_class
        $tmpData = [];
        $productIds = [];

        foreach ($products as $product) {
            $product['product_code'] = $product['code'];
            if (isset($product['attributes']) && !empty($product['attributes'])) {
                $tmp = [];
                foreach ($product['attributes'] as $attribute) {
                    if (isset($compareAttributes[$attribute['attribute_id']])) {
                        $tmp[$compareAttributes[$attribute['attribute_id']]->name] = $attribute['attribute_data'];
                    }
                }

                $product['attributes'] = $tmp;
            }

            if (!isset($tmpData[$product['category_class_id']])) {
                $tmpData[$product['category_class_id']] = [];
            }

            $tmpData[$product['category_class_id']][] = $product;
            $productIds[] = $product['id'];
        }

        $products = $tmpData;

        $selectedPriceTypes = isset($filterData['price_type']) ? $filterData['price_type'] : [];
        // Re-format list price types
        if (!empty($selectedPriceTypes)) {
            $tmpData = [];
            foreach ($selectedPriceTypes as $key => $value) {
                if ($value != 1 && $value !== true) {
                    // Process for options
                    $tmpData[] = $value;
                } else {
                    // Process for checkbox
                    $tmpData[] = $key;
                }
            }
            $selectedPriceTypes = $tmpData;
        }

        $conditionPriceType = isset($filterData['condition_price_type']) ? $filterData['condition_price_type'] : [];

        // Get extra fee
        $extraFees = InsuranceExtraFee::getListWithKeyCodeByTypeId($insuranceTypeId);

        // Get extra products by product
        $extraProducts = Product::getExtraProductsByProduct($productIds);

        // Get prices
        $result = ProductPriceHelper::getProductsPrices($productIds, $insuranceTypeId, isset($filterData['filter_data']) ? $filterData['filter_data'] : [], $selectedPriceTypes, $conditionPriceType);

        // Order products by price asc
        if (isset($result['prices']) && !empty($result['prices'])) {
            $prices = $result['prices'];
            foreach ($products as $classId => $listProducts) {
                foreach ($listProducts as $key => $product) {
                    if (isset($prices[$product['id']])) {
                        $products[$classId][$key]['price_id'] = isset($prices[$product['id']]['price_id']) ? $prices[$product['id']]['price_id'] : 0;
                        $products[$classId][$key]['price'] = isset($prices[$product['id']]['price']) ? $prices[$product['id']]['price'] : 0;
                        $products[$classId][$key]['product_code'] = isset($prices[$product['id']]['product_code']) ? $prices[$product['id']]['product_code'] : $product['code'];
                    }

                    $products[$classId][$key]['extra_fee_price'] = [];

                    // Get extra fee
                    if (!empty($extraFees)) {
                        foreach ($extraFees as $extraFee) {
                            // Get price
                            $price = ProductPriceHelper::getExtraFeePrice($extraFee['id'], $filterData['filter_data'], isset($filterData['extra_fee_attributes']) ? $filterData['extra_fee_attributes'] : [], $product['id']);
                            $products[$classId][$key]['extra_fee_price'][] = [
                                'code' => $extraFee['code'],
                                'name' => $extraFee['name'],
                                'price' => $price
                            ];
                        }
                    }

                    // Get extra product
                    $products[$classId][$key]['extra_products'] = [];
                    if (isset($extraProducts[$product['id']])) {
                        foreach ($extraProducts[$product['id']] as $extraProduct) {
                            $priceAttributes = InsurancePriceAttribute::getListWithKeyCodeByTypeId($extraProduct->insurance_type->insurance_type_id);
                            // Get default value from product setting
                            if (isset($extraProduct->default_price_attribute_values) && !empty($extraProduct->default_price_attribute_values)) {
                                $priceAttributes['default_price_attribute_values'] = json_decode($extraProduct->default_price_attribute_values, true);
                            }
                            $filterParams = isset($filterData['extra_product_filter_data']) ? $filterData['extra_product_filter_data'] : [];
                            $price = ProductPriceHelper::getProductsPrices([$extraProduct->id], $extraProduct->insurance_type->insurance_type_id, $filterParams, $selectedPriceTypes, []);

                            // todo: cannot get extra-product price because missing extra-product's price attributes
                            $products[$classId][$key]['extra_products'][] = [
                                'id' => $extraProduct->id,
                                'name' => $extraProduct->name,
                                'code' => $extraProduct->code,
                                'price' => isset($price['prices'][$extraProduct->id]) ? $price['prices'][$extraProduct->id]['price'] : 0,
                                'insurance_type_id' => $extraProduct->insurance_type->insurance_type_id,
                                'price_attributes' => $priceAttributes
                            ];
                        }
                    }
                }
            }
        } else {
            $products = [];
        }

        return [
            'products'          => $products,
            'classes'           => $classes,
            'compareAttributes' => $compareAttributes,
            'insurance_type_id' => $insuranceTypeId,
            'filter_condition'  => isset($result['conditions']) ? $result['conditions'] : []
        ];
    }

    /**
     * @param $insuranceTypeId
     * @param int $productId
     * @return array
     */
    public static function getExtraProduct($insuranceTypeId, $productId = 0)
    {
        $cacheKey = 'list_extra_products_by_type_' . $insuranceTypeId . '_product_' . $productId;

        $result = Cache::tags('product')->remember($cacheKey, config('product.default_cache_time', 60), function () use ($insuranceTypeId, $productId) {
            // Get extra products for insurance type only
            $products = Product::getListProduct([
                'extra_for_insurance_type' => $insuranceTypeId,
                'extra_for_product'        => 0
            ], false);

            $insuranceIds = [];
            $extraFilterData = [];
            $extraInsuranceTypes = [];
            $extraPriceTypes = [];
            $productPriceAttributes = [];

            if (!empty($productId)) {
                // Get extra product for product
                $extraProducts = Product::getListProduct([
                    'extra_for_product' => $productId
                ], false);

                // Merge two list product
                if ($extraProducts->count()) {
                    $products = $products->toBase()->merge($extraProducts);
                }
            } else {
                // Get extra product for product filter form
                $extraProducts = Product::getListProduct([
                    'extra_for_insurance_type' => $insuranceTypeId,
                    'extra_for_product' => true
                ], false);
            }

            if ($extraProducts->count()) {
                // Get insurance type ids for filter form
                foreach ($extraProducts as $product) {
                    if ($product->insurance_type->insurance_type_id != $insuranceTypeId) {
                        if (!in_array($product->insurance_type->insurance_type_id, $insuranceIds)) {
                            $insuranceIds[] = $product->insurance_type->insurance_type_id;
                        }
                    }
                }
            }

            if (!empty($products)) {
                // Check product price attribute
                foreach ($products as $product) {
                    if ($product->insurance_type->insurance_type_id != $insuranceTypeId) {
                        // Get product price attribute
                        $priceAttributes = InsurancePriceAttribute::getListWithKeyCodeByTypeId($product->insurance_type->insurance_type_id);
                        if ($priceAttributes) {
                            $productPriceAttributes[$product->id] = $priceAttributes;
                        }
                    }
                }

                // Get list price type
                if (!empty($insuranceIds)) {
                    $priceTypes = InsurancePriceType::getListPriceTypesByTypeIds($insuranceIds);
                    // Group by type
                    if ($priceTypes) {
                        foreach ($priceTypes as $priceType) {
                            if (!isset($extraPriceTypes[$priceType->insurance_type_id])) {
                                $extraPriceTypes[$priceType->insurance_type_id] = [];
                            }

                            $extraPriceTypes[$priceType->insurance_type_id][] = $priceType;
                        }
                    }

                    // Get filter data
                    $extraFilterData = InsurancePriceAttribute::getListWithKeyCode($insuranceIds);
                    // Group by insurance type
                    $tmpData = [];
                    foreach ($extraFilterData as $item) {
                        $tmpData[$item['type_id']][] = $item;
                    }
                    $extraFilterData = $tmpData;

                    // Get extra insurance types. Only load if missing product id
                    if (!empty($productId)) {
                        $extraInsuranceTypes = InsuranceType::getByListId($insuranceIds);
                    }
                }

                $products = $products->toArray();

                return [
                    'success' => true,
                    'message' => '',
                    'products' => $products,
                    'extra_insurance_types' => $extraInsuranceTypes,
                    'extra_price_types' => $extraPriceTypes,
                    'extra_filter_data' => $extraFilterData,
                    'price_attributes'  => isset($priceAttributes) ? $priceAttributes : []
                ];
            } else {
                return ['success' => false, 'message' => ''];
            }
        });

        return $result;
    }

    public static function getExtraFee($insuranceTypeId)
    {
        $extraFees = InsuranceExtraFee::getListWithKeyCodeByTypeId($insuranceTypeId);
        if (!empty($extraFees)) {
            return [
                'success' => true,
                'message' => '',
                'extra_fees' => $extraFees,
            ];
        } else {
            return ['success' => true, 'message' => 'Không tìm thấy sản phẩm phụ'];
        }
    }
}