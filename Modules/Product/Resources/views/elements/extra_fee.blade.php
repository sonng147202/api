@if (isset($extraFees) && !empty($extraFees))
    <?php
    $beforeInput = $afterInput = '';
    if (isset($prefixInput) && !empty($prefixInput)) {
        $beforeInput = $prefixInput . '[';
        $afterInput = ']';
    }
    ?>
    @foreach($extraFees as $extraFee)
        <div class="panel box box-primary fee-item-wrapper">
            <div class="box-header with-border extra-fee-item" data-id="{{ $extraFee['id'] }}">
                <h4><input class="item-price-input extra-fee-input use-fees-checkbox" type="checkbox" @if (isset($selectedFees) && in_array($extraFee['code'], array_keys($selectedFees))) checked @endif name="use_fees[extra_fee][{{ $extraFee['code'] }}]" value="{{ isset($selectedFees[$extraFee['code']]) ? $selectedFees[$extraFee['code']] : 0 }}"/>&nbsp;<label>{{ $extraFee['name'] }}</label></h4>
                <?php $attributes = !empty($extraFee['price_attributes']) ? json_decode($extraFee['price_attributes'], true) : [];?>
                @if (!empty($attributes))
                    @foreach($attributes as $attribute)
                        @php
                            $input = '';
                            switch ($attribute['type']) {
                                case 'select':
                                    // Get options
                                    $options = [];
                                    $arr1 = explode(';', $attribute['default_value']);
                                    foreach ($arr1 as $arr) {
                                        $arr2 = explode(':', $arr);
                                        $selected = '';
                                        if (isset($selectedFeeAttributes[$attribute['attr_code']]) && $arr2[0] == $selectedFeeAttributes[$attribute['attr_code']]) {
                                            $selected = 'selected';
                                        }
                                        $options[] = '<option value="' . $arr2[0] . '" '. $selected .'>' . $arr2[1] . '</option>';
                                    }
                                    $input = '<select data-name="' . $attribute['attr_code'] . '" name="' . $beforeInput . $attribute['attr_code'] . $afterInput . '" class="form-control extra-fee-attribute"> ' . implode('', $options) . ' </select>';
                                    break;
                                case 'number':
                                    $input = '<input data-name="' . $attribute['attr_code'] . '" type="text" name="' . $beforeInput . $attribute['attr_code'] . $afterInput . '" class="extra-fee-attribute form-control" value="'. (isset($selectedFeeAttributes[$attribute['attr_code']]) ? $selectedFeeAttributes[$attribute['attr_code']] : '') .'"/>';
                                    break;
                                default:
                                    $input = '<input data-name="' . $attribute['attr_code'] . '" type="text" name="' . $beforeInput . $attribute['attr_code'] . $afterInput . '" class="extra-fee-attribute form-control" value="'. (isset($selectedFeeAttributes[$attribute['attr_code']]) ? $selectedFeeAttributes[$attribute['attr_code']] : '') .'"/>';
                                    break;
                            }
                        @endphp
                        <label>{{ $attribute['attr_name'] }}</label>
                        {!! $input !!}
                    @endforeach
                @endif
                <div class="text-center">
                    <h4 class="@if (!isset($selectedFees[$extraFee['code']]) || empty($selectedFees[$extraFee['code']])) hidden @endif">Phí bảo hiểm: <span class="item-price">{{ isset($selectedFees[$extraFee['code']]) ? number_format($selectedFees[$extraFee['code']], 0) : 0 }}</span> VND</h4>
                </div>
            </div>
        </div>
    @endforeach
@endif