@extends('layouts.admin_default')
@section('title', 'Danh sách sản phẩm')
@section('content')
    <section class="content-header">
        <h1>Quản lý sản phẩm</h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin_home') }}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Danh sách sản phẩm</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs">
                        <li class="active"><a href="#tab_1" data-toggle="tab" aria-expanded="true"><i class="fa fa-list-alt" aria-hidden="true"></i> Tất cả</a></li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active" id="tab_1">
                            {!! Form::open(['method' => 'GET', 'class' => 'filter', 'route' => ['product.sp.index']]) !!}
                            <div class="col-xs-3">
                                <label>Tên sản phẩm BH</label>
                                <input name="keyword" type="textbox" class= "form-control pull-right" placeholder="Nhập vào tên sản phẩm bảo hiểm" value={{isset($params["keyword"]) ? $params["keyword"] : "" }}>
                            </div>
                            <div class="col-xs-3">
                                <label>CTY Bảo hiểm</label>
                                <select name="company_id" class="form-control select2 pull-right">
                                    <option value="">Công ty bảo hiểm</option>
                                    @foreach ($companies as $company)
                                        <option value="{{ $company->id }}" {{isset($params["company_id"]) && $company->id==$params["company_id"] ? 'selected' : ''}}>{{ $company->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-xs-3">
                                <label>Danh mục SP</label>
                                <select name="category_id" class="form-control select2 pull-right">
                                    <option value="">Danh mục sản phẩm</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" {{ isset($params["category_id"]) && $params["category_id"] == $category->id ? "selected" : "" }}>{{ $category->prefix }} {{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-xs-3">
                                <label style="visibility: hidden">TK</label>
                                {{--{!! Form::button('Tìm kiếm', ['class' => 'btn btn-primary pull-right', 'type' => "submit"]) !!}--}}
                                <button type="submit" class="btn btn-primary btn-block"><span class="glyphicon glyphicon-search" aria-hidden="true"></span> Tìm kiếm</button>
                            </div>
                            {!! Form::close() !!}
                            <div class="clearfix"></div>
                            <br>
                        </div>
                    </div>
                    <!-- /.tab-content -->
                </div>
            </div>
            <div class="col-xs-12">
                @if ($errors->any())
                    <h4 style="color:red">{{$errors->first()}}</h4>
                @endif
                <div class="box box-info">
                    <div class="box-header">
                        <h3 class="box-title">Sản phẩm bảo hiểm</h3>
                        <div class="pull-right">
                            <a class="btn btn-primary btn-sm" href="/product/sp/create"><i class="fa fa-plus"></i> Thêm sản phẩm mới</a>
                        </div>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        <table id="example2" class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Công ty bảo hiểm</th>
                                    <th>Hạng sản phẩm</th>
                                    <th>Tên</th>
                                    <th>Hoa hồng</th>
                                    <th>Trạng thái</th>
                                    <th class="text-center">Thuộc tính</th>
                                    <th class="text-center">Giá</th>
                                    <th class="text-center">Sửa</th>
                                    <th class="text-center">Thêm hoa hồng</th>
                                    <th class="text-center">Xóa</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($products as $product)
                                <tr>
                                    <td>{{ $product->id }}</td>
                                    <td>{{ $product->company? $product->company->name : null }}</td>
                                    <td>{{ $product->category_class ? $product->category_class->name : null }}</td>
                                    <td>{{ $product->name }}</td>
                                    <td>{{ $product->commission() ? $product->commission()->getCommissionAmountFormat() : '' }}</td>
                                    <td>{{ $product->getStatusName() }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('product.attribute', $product->id) }}" class="btn btn-warning btn-xs" onclick="$(this).closest('form').submit();"><i class="fa fa-edit"></i></a>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('product.prices.index', $product->id) }}" class="btn btn-warning btn-xs" onclick="$(this).closest('form').submit();"><i class="fa fa-edit"></i></a>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('product.sp.edit', $product->id) }}" class="btn btn-warning btn-xs" onclick="$(this).closest('form').submit();"><i class="fa fa-pencil"></i></a>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('create.product.level', $product->id) }}" class="btn btn-warning btn-xs"><i class="fa fa-pencil"></i></a>
                                    </td>
                                    <td class="text-center">
                                        @if ($product->status != constant('Modules\Product\Models\Product::STATUS_DELETED'))
                                            {!! Form::open(['method' => 'DELETE', 'route' => ['product.sp.destroy', $product->id]]) !!}
                                                <a href="#" class="btn btn-danger btn-xs"  onclick="if(confirm('Bạn có chắc muốn xóa bản ghi này không?')) $(this).closest('form').submit();"><i class="fa fa-trash"></i></a>
                                            {!! Form::close() !!}
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                            </tfoot>
                        </table>
                        {{ $products->appends($params)->links() }}
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
