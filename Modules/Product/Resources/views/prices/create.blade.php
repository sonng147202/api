@extends('layouts.admin_default')
@section('title', 'Quản lý giá sản phẩm')
@section('content')
    <section class="content-header">
        <h1>Quản lý giá cho sản phẩm bảo hiểm</h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin_home') }}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="{{ route('product.index') }}"> Danh sách sản phẩm</a></li>
            <li class="active">Giá cho sản phẩm bảo hiểm</li>
        </ol>
    </section>
    <section class="content">
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">Thêm mới giá sản phẩm bảo hiểm: <b>{{ $product->name }}</b></h3>
            </div>
            @if ($errors->any())
                <h4 style="color:red">{{ $errors->first() }}</h4>
            @endif
            <!-- /.box-header -->
            <div class="box-body">
            {!! Form::open(['method' => 'POST', 'route' => ['product.prices.store', $productId] ]) !!}
                <div class="row margin-bottom">
                    <div class="col-md-6 col-sm-12 col-xs-12">
                        <div class="form-group">
                            <label>Mã sản phẩm</label> <input type="text" name="product_code" class="form-control"/>
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-12 col-xs-12">
                        <div class="form-group">
                            <label>Loại giá:</label>
                            <select name="price_type" class="form-control">
                                <option value="0">Mặc định</option>
                                <option value="1">Theo ngày</option>
                                <option value="2">Theo tuần</option>
                                <option value="3">Theo tháng</option>
                                <option value="4">Theo năm</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row margin-bottom">
                    @foreach($priceTypes as $priceType)
                        <div class="col-md-3 col-sm-3 col-xs-6 form-group">

                            <label>{{ $priceType->name }}</label>
                            @if (in_array($priceType->code,["in_fee","AA00123"]))

                                @if (array_key_exists($priceType->code,$unitPriceType))
                                    : <label data-toggle="tooltip" data-placement="top" title="Giá trị cố định">
                                        <input type="radio" name="unit_price_{{$priceType->code}}_health_insurance" value="0"
                                               {{$unitPriceType[$priceType->code] == 0 ? "checked" : ""}}> $
                                    </label>
                                    <label data-toggle="tooltip" data-placement="top" title="Tỷ lệ">
                                        <input type="radio" name="unit_price_{{$priceType->code}}_health_insurance" value="1"
                                               {{$unitPriceType[$priceType->code] == 1 ? "checked" : ""}}> %
                                    </label>


                                    @else

                                : <label data-toggle="tooltip" data-placement="top" title="Giá trị cố định">
                                        <input type="radio" name="unit_price_{{$priceType->code}}_health_insurance" value="0" checked> $
                                    </label>
                                <label data-toggle="tooltip" data-placement="top" title="Tỷ lệ">
                                    <input type="radio" name="unit_price_{{$priceType->code}}_health_insurance" value="1"> %
                                </label>


                                @endif
                                <input name="price_detail[{{ $priceType->code }}]" value="{{ isset($priceDetail[$priceType->code]) ? $priceDetail[$priceType->code] : 0 }}" class="form-control" />

                                {{--<div class="input-group">--}}
                                    {{--<input name="price_detail[{{ $priceType->code }}]" value="{{ isset($priceDetail[$priceType->code]) ? $priceDetail[$priceType->code] : 0 }}" class="form-control" onclick=""/>--}}
                                    {{--<span class="input-group-btn">--}}
                                {{--<button type="button" class="btn btn-info btn-flat" onclick="customPriceHtml('{{$priceType->name}}','{{$priceType->code}}')"><i class="fa fa-fw fa-pencil"></i></button>--}}
                                {{--</span>--}}
                                {{--</div>--}}

                            @else
                                <input name="price_detail[{{ $priceType->code }}]" value="{{ isset($priceDetail[$priceType->code]) ? $priceDetail[$priceType->code] : 0 }}" class="form-control" />

                            @endif
                        </div>
                    @endforeach
                </div>
                <table id="example2" class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th width="30%">Thuộc tính giá</th>
                            <th width="20%">Điều kiện so sánh</th>
                            <th width="50%">Giá trị so sánh</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-tb">
                        <tr>
                            <td>
                                <select name="attr_key[]" class="form-control price-condition-elm" data-type="{{ $insuranceTypeIds[0] }}">
                                    @foreach($priceConditions as $priceCondition)
                                        <option value="{{ $priceCondition['code'] }}">{{ $priceCondition['title'] }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <select name="attr_operator[]" class="form-control operator-options">
                                    @foreach($operators as $key => $title)
                                        <option value="{{ $key }}">{{ $title }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="value-inputs-wrap">
                                <div class="value-inputs">
                                    <input name="attr_value[]" type="text" class="form-control">
                                </div>
                                <div class="between-inputs">
                                    <div class="row">
                                        <div class="col-md-5"><input name="attr_min_value[]" type="text" class="form-control"></div>
                                        <div class="col-md-2">Tới</div>
                                        <div class="col-md-5"><input name="attr_max_value[]" type="text" class="form-control"></div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <i class="fa fa-fw fa-plus" id="add-price"></i>
                <!-- /.row -->
            </div>
            <div class="box-footer">
                <a href="{{ route('product.prices.index', $productId) }}" class="btn btn-default pull-right">Hủy</a>
                {!! Form::button('Thêm mới', ['class' => 'btn btn-primary pull-left', 'type' => "submit"]) !!}
                <input type="hidden" name="product_id" value="{{ $productId }}"/>
            </div>
            {!! Form::close() !!}
            <div class="overlay hide">
                <i class="fa fa-refresh fa-spin"></i>
            </div>

            <div class="modal fade" id="modal-default" style="display: none;">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">×</span></button>
                            <h4 class="modal-title"></h4>
                        </div>
                        <div class="modal-body">
                            <div class="row form-group">
                                <div class="col-xs-5">
                                    <input type="number" min="0" class="form-control rate-custom-price" placeholder="Tỷ lệ phí bảo hiểm: %" onkeyup="calculateCustomPrice(this)">
                                </div>
                                <div class="col-xs-1" style="padding: 0px">
                                    <button class="btn btn-default form-control disabled" >X</button>

                                </div>

                                <div class="col-xs-5">
                                    <input type="number" pattern="[0-9]+([,\.][0-9]+)?" min="0" class="form-control total-custom-price" placeholder="Tổng tiền bảo hiểm" onkeyup="calculateCustomPrice(this)">
                                </div>

                            </div>

                            <div class="form-group ">
                                <h3 class="label-custom-price">Kết quả: </h3>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary confirm-custom-price" data-custom-price="" data-input-name="" onclick="confirmCustomPrice(this)">Xác nhận</button>
                        </div>
                    </div>
                    <!-- /.modal-content -->
                </div>
                <!-- /.modal-dialog -->
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script type="text/javascript" src="{{ Module::asset('product:js/get_product_price.js') }}?v={{ env('FRONTEND_VERSION') }}"></script>
<script>
    var exampleElm;
    $(function(){
        // Get example element
        exampleElm = $('#tbody-tb > tr').eq(0).clone();
        $('[data-toggle="tooltip"]').tooltip();

        $('body').on('click', '#add-price', function() {
            $("#tbody-tb").append(exampleElm.clone());
            handleAttrValueInputs();
        });

        $('#tbody-tb').on('change', '.operator-options', function () {
            handleAttrValueInputs();
        });

        handleAttrValueInputs();
        handleLoadPriceAttributeInputs();
    });

    function handleAttrValueInputs()
    {
        var operators = $('.operator-options');

        $.each(operators, function () {
            var parent = $(this).parents('tr');

            if ($(this).val() == 'between') {
                parent.find('.between-inputs').show();
                parent.find('.value-inputs').hide();
            } else {
                parent.find('.between-inputs').hide();
                parent.find('.value-inputs').show();
            }
        });
    }

    function handleLoadPriceAttributeInputs()
    {
        $('body').on('change', '.price-condition-elm', function () {
            var currElm = $(this),
                productId = $('[name=product_id]').val();
            $.ajax({
                url: '/product/load-price-attribute-inputs',
                type: 'get',
                dataType: 'json',
                data: {price_attribute: currElm.val(), type: currElm.data('type'), product_id: productId},
                success: function (res) {
                    if (res.success) {
                        currElm.parents('tr').find('.value-inputs-wrap').html(res.html);
                        currElm.parents('tr').find('.operator-options').trigger('change');
                    } else {
                        console.log(res.message);
                    }
                },
                error: function (res) {
                    console.log(res);
                }
            });
        });

        $('.price-condition-elm').trigger('change');
    }
</script>
@endsection
