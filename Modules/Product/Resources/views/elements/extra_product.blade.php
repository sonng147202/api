@if (isset($products) && !empty($products))
    @foreach($products as $product)
        <div class="panel box box-primary fee-item-wrapper extra-product-item">
            <div class="box-header with-border extra-product-{{ $product->id }}">
                <h4><input type="checkbox" @if (isset($selectedProducts) && in_array($product->id, $selectedProducts)) checked @endif name="use_fees[extra_product][{{ $product->id }}]" class="minimal extra-product-input use-fees-checkbox" value=""/>&nbsp;<label>{{ $product->name }}</label></h4>
                <div class="text-center">
                    @if (isset($productPriceAttributes[$product->id]) && !empty($productPriceAttributes[$product->id]))
                        <?php
                        $priceAttributes = $productPriceAttributes[$product->id];
                        $customClass = 'extra-product-filter';
                        if (!isset($filterData)) {
                            $filterData = [];
                        }
                        if (isset($listFilterData[$product->id])) {
                            $filterData = $listFilterData[$product->id];
                        }
                        ?>
                        @include('insurance::quotation.get_filter_form')
                    @endif
                </div>
                <div>
                    <div>
                        <?php
                        if (isset($extraPriceTypes) && isset($extraPriceTypes[$product->insurance_type->insurance_type_id])) {
                            $priceTypes = $extraPriceTypes[$product->insurance_type->insurance_type_id];
                        } else {
                            $priceTypes = false;
                        }
                        $inputClass = 'extra-product-price-type'
                        ?>
                        @include('insurance::elements.price_types_select')
                    </div>
                </div>
                <div class="text-center">
                    <h4 class="hidden">Phí bảo hiểm: <span class="item-price">0 VND</span></h4>
                    <h4 class="hidden">Thuế: <span class="item-tax">0</span>%</h4>
                    <h4 class="hidden">Phí bảo hiểm sau thuế: <span class="item-price-tax">0 VND</span></h4>
                    <input type="hidden" class="extra-product-id" value="{{ $product->id }}"/>
                    <input type="hidden" class="extra-product-insurance-type" value="{{ $product->insurance_type->insurance_type_id }}"/>
                </div>
            </div>
        </div>
    @endforeach
@endif
@if (isset($extraInsuranceTypes) && !empty($extraInsuranceTypes))
    @foreach($extraInsuranceTypes as $extraInsuranceType)
        @if (isset($extraFilterData[$extraInsuranceType->id]) && !empty($extraFilterData[$extraInsuranceType->id]))
            <div class="panel box box-primary fee-item-wrapper extra-product-item">
                <div class="box-header with-border extra-insurance-type-{{ $extraInsuranceType->id }}">
                    <h4><input type="checkbox" @if (isset($selectedProducts) && in_array($extraInsuranceType->id, $selectedProducts)) checked @endif name="use_fees[extra_insurance_type][{{ $extraInsuranceType->id }}]" class="minimal extra-product-input use-fees-checkbox" value=""/>&nbsp;<label>{{ $extraInsuranceType->name }}</label></h4>
                    <div class="text-center">
                        <?php
                        $priceAttributes = $extraFilterData[$extraInsuranceType->id];
                        $customClass = 'extra-product-filter';
                        $prefixInput = 'extra_product_filter_data';
                        if (!isset($filterData)) {
                            $filterData = [];
                        }
                        ?>
                        @include('insurance::quotation.get_filter_form')
                    </div>
                    <div>
                        <?php
                        if (isset($extraPriceTypes[$extraInsuranceType->id])) {
                            $priceTypes = $extraPriceTypes[$extraInsuranceType->id];
                        } else {
                            $priceTypes = false;
                        }
                        ?>
                        @include('insurance::elements.price_types_select')
                    </div>
                    <input type="hidden" class="extra-product-insurance-type" value="{{ $extraInsuranceType->id }}"/>
                </div>
            </div>
        @endif
    @endforeach
@endif