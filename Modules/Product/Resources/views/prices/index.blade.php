@extends('layouts.admin_default')
@section('title', 'Quản lý giá sản phẩm')
@section('content')
    <section class="content-header">
        <h1>Quản lý giá cho sản phẩm bảo hiểm {{ $product->name }}</h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin_home') }}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="{{ route('product.index') }}"><i class="fa fa-dashboard"></i> Danh sách sản phẩm</a></li>
            <li class="active">Giá cho sản phẩm bảo hiểm</li>
        </ol>
    </section>
    <section class="content">
    <div class="row">
        <div class="col-xs-12">
            @if ($errors->any())
                <h4 style="color:red">{{$errors->first()}}</h4>
            @endif
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">Giá theo sản phẩm:</h3>
                        <div class="pull-right">
                            <a class="btn btn-primary btn-xs" href="/product/{{ $productId }}/update-price-type">Chỉnh sửa</a>
                        </div>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        <table id="example2" class="table table-bordered table-striped">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>Loại phí</th>
                                <th>Chi tiết</th>
                            </tr>
                            </thead>
                            <tbody>
                            @if (isset($productPriceTypes) && !empty($productPriceTypes))
                                <?php $i = 1;?>
                                @foreach($productPriceTypes as $productPriceType)
                                    <tr>
                                        <td>{{ $i }}</td>
                                        <td>{{ $productPriceType->name }}</td>
                                        <td></td>
                                    </tr>
                                    <?php $i++; ?>
                                @endforeach
                            @endif
                            </tbody>
                            <tfoot>
                            </tfoot>
                        </table>
                    </div>
                </div>
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">Giá theo điều kiện</h3>
                    <div class="pull-right">
                        <a class="btn btn-primary btn-xs" href="/product/{{ $productId }}/prices/create">Thêm giá cho sản phẩm</a>
                    </div>
                </div>
                <!-- /.box-header -->
                <div class="box-body">
                    <table id="example2" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Điều kiện</th>
                                @foreach($priceTypes as $priceType)
                                    <th>{{ $priceType->name }}</th>
                                @endforeach
                                <th>Lựa chọn</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($productPrices as $productPrice)
                                <?php $priceDetail = !empty($productPrice->price_detail) ? json_decode($productPrice->price_detail, true) : [];?>
                                <tr>
                                    <td>{{ $productPrice->id }}</td>
                                    <td>
                                        @if($productPrice->productPriceCondition)
                                            @foreach ($productPrice->productPriceCondition as $condition)
                                                @if (isset($priceAttributes[$condition->attr_key]))
                                                <div>. {{ $priceAttributes[$condition->attr_key]['title'] }}
                                                    {{ \Modules\Product\Libraries\ProductPriceHelper::getPriceConditionText($condition, $priceAttributes[$condition->attr_key]['data_type'], $priceAttributes[$condition->attr_key]['values']) }}</div>
                                                @endif
                                            @endforeach
                                        @endif
                                    </td>
                                    @foreach($priceTypes as $priceType)
                                        <td>{{ isset($priceDetail[$priceType->code]) ? @number_format($priceDetail[$priceType->code], 5) : 0 }}</td>
                                    @endforeach
                                    <td class="row-options">
                                        {!! Form::open(['method' => 'GET', 'class' => 'form-delete-btn', 'route' => ['product.prices.edit', $productId, $productPrice->id] ]) !!}
                                            <a href="#" class="btn btn-warning btn-xs" onclick="$(this).closest('form').submit();"><i class="fa fa-pencil"></i></a>
                                        {!! Form::close() !!}
                                        {!! Form::open(['method' => 'DELETE', 'class' => 'form-delete-btn', 'route' => ['product.prices.destroy', $productId, $productPrice->id] ]) !!}
                                            <a href="#" class="btn btn-danger btn-xs"  onclick="if(confirm('Bạn có chắc muốn xóa bản ghi này không?')) $(this).closest('form').submit();"><i class="fa fa-trash"></i></a>
                                        {!! Form::close() !!}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                        </tfoot>
                    </table>
                    {{ $productPrices->links() }}
                </div>
                <!-- /.box-body -->
                <div class="overlay hide">
                    <i class="fa fa-refresh fa-spin"></i>
                </div>
            </div>
            <!-- /.box -->
        </div>
        <!-- /.col -->
    </div>
    </section>
@endsection
