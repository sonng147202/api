@extends('layouts.admin_default')
@section('title', 'Danh mục sản phẩm')
@section('content')
    <section class="content-header">
        <h1>
            Quản lý danh mục sản phẩm
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin_home') }}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Danh mục sản phẩm</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                @if ($errors->any())
                    <h4 style="color:red">{{$errors->first()}}</h4>
                @endif
                <div class="box box-info">
                    <div class="box-header">
                        <h3 class="box-title">Danh mục sản phẩm</h3>
                        <div class="pull-right">
                            <a class="btn btn-primary" href="{{ route('product.categories.create') }}"><i class="fa fa-plus"></i> Thêm danh mục mới</a>
                        </div>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        <table id="example2" class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tên</th>
                                    <th>Loại hình bảo hiểm</th>
                                    <th>Logo</th>
                                    <th>Trạng thái</th>
                                    <th class="text-center">Thuộc tính</th>
                                    <th class="text-center">Sửa</th>
                                    <th class="text-center">Xóa</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($productCategories as $productCategory)
                                    <tr>
                                        <td>{{ $productCategory->id }}</td>
                                        <td>{{ $productCategory->name }}</td>
                                        <td>{{ $productCategory->insurance_type? $productCategory->insurance_type->name : null }}</td>
                                        <td><img src="{{ $productCategory->avatar }}" alt="logo" width="30" height="30"/></td>
                                        <td>{{ $productCategory->getStatusName() }}</td>
                                        <td class="text-center">
                                            <a href="{{ route('product.category_attributes.index', $productCategory->id) }}" class="btn btn-warning btn-xs"><i class="fa fa-pencil"></i></a>
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('product.categories.edit', $productCategory->id) }}" class="btn btn-warning btn-xs"><i class="fa fa-pencil"></i></a>
                                        </td>
                                        <td class="text-center">
                                            @if ($productCategory->status != constant('Modules\Product\Models\Category::STATUS_DELETED'))
                                                {!! Form::open(['method' => 'DELETE', 'route' => ['product.categories.destroy', $productCategory->id]]) !!}
                                                    <a href="#" class="btn btn-danger btn-xs" onclick="$(this).closest('form').submit();"><i class="fa fa-trash"></i></a>
                                                {!! Form::close() !!}
                                            @endif
                                        </td>
                                    </tr>
                                    @foreach ($productCategory->children as $childProductCategory)
                                        <tr>
                                            <td>-- {{ $childProductCategory->id }}</td>
                                            <td>-- {{ $childProductCategory->name }}</td>
                                            <td>{{ $childProductCategory->insurance_type? $childProductCategory->insurance_type->name : null }}</td>
                                            <td><img src="{{ $childProductCategory->avatar }}" alt="logo" width="30" height="30"/></td>
                                            <td>{{ $childProductCategory->getStatusName() }}</td>
                                            <td class="text-center">
                                                <a href="{{ route('product.category_attributes.index', $childProductCategory->id) }}" class="btn btn-warning btn-xs"><i class="fa fa-pencil"></i></a>
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('product.categories.edit', $childProductCategory->id) }}" class="btn btn-warning btn-xs"><i class="fa fa-pencil"></i></a>
                                            </td>
                                            <td class="text-center">
                                                @if ($childProductCategory->status != constant('Modules\Product\Models\Category::STATUS_DELETED'))
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['product.categories.destroy', $childProductCategory->id]]) !!}
                                                        <button type="submit" class="btn btn-danger btn-xs" onclick="return confirm('Bạn có chắc chắn xóa bản ghi này?')"><i class="fa fa-trash"></i></button>
                                                    {!! Form::close() !!}
                                                    {{--<a href="{{route('product.categories.destroy',$childProductCategory->id)}}" onclick="return confirm('Bạn có chắc chắn xóa bản ghi này?')" class="btn btn-danger btn-xs"><i class="fa fa-trash"></i></a>--}}
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                            <tfoot>
                            </tfoot>
                        </table>
                        {{ $productCategories->links() }}
                    </div>
                    <!-- /.box-body -->
                </div>
                <!-- /.box -->
            </div>
            <!-- /.col -->
        </div>
    </section>
@endsection
