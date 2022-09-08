@extends('layouts.admin_default')

@section('content')
    <section class="content-header">
        <h1>Quản lý sản phẩm</h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin_home') }}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="{{ route('product.categories.index') }}"><i class="fa fa-dashboard"></i> Danh mục sản phẩm</a></li>
            <li class="active">Danh sách thuộc tính</li>
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
                    <h3 class="box-title">Danh sách thuộc tính của danh mục {{$category->name}}</h3>
                    <div class="pull-right">
                        <a class="btn btn-primary" href="/product/{{$categoryId}}/category_attributes/create">Thêm thuộc tính mới</a>
                    </div>
                </div>
                <!-- /.box-header -->
                <div class="box-body">
                    <table id="example2" class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Danh mục bảo hiểm</th>
                                <th>Mã thuộc tính</th>
                                <th>Tiêu đề</th>
                                <th>Loại dữ liệu</th>
                                <th>Bắt buộc</th>
                                <th>Sử dụng để so sánh</th>
                                <th>Sửa</th>
                                <th>Xóa</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($productCategoryAttributes as $categoryAttribute)
                            <tr>
                                <td>{{ $categoryAttribute->id }}</td>
                                <td>{{ $categoryAttribute->category->name }}</td>
                                <td>{{ $categoryAttribute->name }}</td>
                                <td>{{ $categoryAttribute->title }}</td>
                                <td>{{ $categoryAttribute->data_type }}</td>
                                <td>{{ $categoryAttribute->is_required == 1 ? 'true' : 'false' }}</td>
                                <td>{{ $categoryAttribute->compare_flg == 1 ? 'true' : 'false' }}</td>
                                <td>
                                    {!! Form::open(['method' => 'GET', 'route' => ['product.category_attributes.edit', $categoryId, $categoryAttribute->id]]) !!}
                                        <a href="#" class="btn btn-warning btn-xs" onclick="$(this).closest('form').submit();"><i class="fa fa-pencil"></i></a>
                                    {!! Form::close() !!}
                                </td>
                                <td>
                                    {!! Form::open(['method' => 'DELETE', 'route' => ['product.category_attributes.destroy', $categoryId, $categoryAttribute->id]]) !!}
                                        <a href="#" class="btn btn-danger btn-xs"  onclick="if(confirm('Bạn có chắc muốn xóa bản ghi này không?')) $(this).closest('form').submit();"><i class="fa fa-trash"></i></a>
                                    {!! Form::close() !!}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                        </tfoot>
                    </table>
                    {{ $productCategoryAttributes->links() }}
                </div>
                <!-- /.box-body -->
            </div>
            <!-- /.box -->
        </div>
        <!-- /.col -->
    </div>
    </section>
@endsection
