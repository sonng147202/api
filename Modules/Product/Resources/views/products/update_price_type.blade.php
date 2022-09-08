@extends('layouts.admin_default')
@section('title', 'Cấu hình giá cho sản phẩm: ' . $product->name)
@section('content')
    <section class="content-header">
        <h1>Cấu hình giá cho sản phẩm: {{ $product->name }}</h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin_home') }}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="{{ route('product.index') }}"> Danh sách sản phẩm</a></li>
            <li class="active">Cấu hình giá</li>
        </ol>
    </section>
    <section class="content">
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">Cấu hình giá cho sản phẩm</h3>
            </div>
            @if ($errors->any())
                <h4 style="color:red">{{$errors->first()}}</h4>
            @endif
        <!-- /.box-header -->
            <div class="box-body">
                {!! Form::open(['method' => 'POST', 'route' => ['product.update_price_type', $product->id], 'class' => 'validate']) !!}
                @if (isset($priceTypes) && !empty($priceTypes))
                    <?php
                    $productPriceTypeValues = json_decode($product->config_price_types, true);
                    ?>
                    <div class="row">
                        @foreach($priceTypes as $priceType)
                            <div class="col-lg-4 col-md-6 col-sm-12">
                                <div class="form-group">
                                    <label>{{ $priceType->name }}</label>
                                    <?php
                                    if (!isset($productPriceTypeValues[$priceType->code])) {
                                        // Get from insurance type default value config
                                        $defaultValue = json_decode($priceType->default_value, true);
                                    } else {
                                        $defaultValue = $productPriceTypeValues[$priceType->code];
                                    }
                                    ?>
                                    <div class="row">
                                        <div class="col-md-3">Mức phí</div>
                                        <div class="col-md-4">Giá trị</div>
                                        <div class="col-md-5">Giá trị hiển thị</div>
                                    </div>
                                    <div class="list-option-wrapper">
                                        @if (!empty($defaultValue) && is_array($defaultValue))
                                            @foreach($defaultValue as $key => $item)
                                                <div class="row form-group">
                                                    <div class="col-md-3"><input class="form-control" name="config_value[{{ $priceType->code }}][{{ $key }}][price]" value="{{ $item['price'] }}"/></div>
                                                    <div class="col-md-4"><input class="form-control" name="config_value[{{ $priceType->code }}][{{ $key }}][compare_value]" value="{{ $item['compare_value'] }}"/></div>
                                                    <div class="col-md-4"><input class="form-control" name="config_value[{{ $priceType->code }}][{{ $key }}][compare_text]" value="{{ $item['compare_text'] }}"/></div>
                                                    <div class="col-md-1"><a href="javascript:" class="btn btn-sm btn-danger btn-remove-option"><i class="fa fa-trash-o"></i></a></div>
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="row form-group">
                                                <div class="col-md-3"><input class="form-control" name="config_value[{{ $priceType->code }}][0][price]" value=""/></div>
                                                <div class="col-md-4"><input class="form-control" name="config_value[{{ $priceType->code }}][0][compare_value]" value=""/></div>
                                                <div class="col-md-4"><input class="form-control" name="config_value[{{ $priceType->code }}][0][compare_text]" value=""/></div>
                                                <div class="col-md-1"><a href="javascript:" class="btn btn-sm btn-danger btn-remove-option"><i class="fa fa-trash-o"></i></a></div>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12 text-center">
                                            <a href="javascript:" class="btn btn-sm btn-primary btn-add-option"><i class="fa fa-plus-circle"></i></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="box-footer">
                <a href="{{ route('product.sp.index') }}" class="btn btn-default pull-right">Hủy</a>
                {!! Form::button('Cập nhật', ['class' => 'btn btn-primary pull-left', 'type' => "submit"]) !!}
            </div>
            {!! Form::close() !!}
            <div class="overlay hide">
                <i class="fa fa-refresh fa-spin"></i>
            </div>
        </div>
    </section>
@endsection
@section('scripts')
    <script type="text/javascript" src="{{ asset('/modules/product/js/update_price_type_setting.js') }}"></script>
@endsection
