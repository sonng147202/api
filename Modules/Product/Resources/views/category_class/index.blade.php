@extends('layouts.admin_default')
@section('title', 'Hạng sản phẩm')
@section('content')
    <section class="content-header">
        <h1>
            Quản lý hạng sản phẩm
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin_home') }}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Hạng sản phẩm</li>
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
                        <h3 class="box-title">Danh sách hạng sản phẩm danh mục: <b>{{ $category->name }}</b></h3>
                        <div class="pull-right">
                            <a class="btn btn-primary" href="{{ route('product.category_class.create', $category->id) }}">Thêm hạng sản phẩm</a>
                        </div>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        <table id="example2" class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th width="5%" class="text-center">ID</th>
                                    <th>Tên</th>
                                    <th>Thứ tự</th>
                                    <th width="15%">Trạng thái</th>
                                    <th width="10%" class="text-center">Lựa chọn</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($classes as $class)
                                    <tr>
                                        <td class="text-center">{{ $class->id }}</td>
                                        <td>{{ $class->name }}</td>
                                        <td>{{ $class->order_number }}</td>
                                        <td>{{ $class->getStatusName() }}</td>
                                        <td class="text-center">
                                            <a href="{{ route('product.category_class.edit', [$class->category_id, $class->id]) }}" class="btn btn-warning btn-xs"><i class="fa fa-pencil"></i></a>
                                            {!! Form::open(['method' => 'DELETE', 'class' => 'form-delete-btn', 'route' => ['product.category_class.destroy', $class->category_id, $class->id]]) !!}
                                                <a href="javascript:" onclick="if(confirm('Bạn có chắc muốn xóa bản ghi này không?')) $(this).closest('form').submit();" class="btn btn-danger btn-xs"><i class="fa fa-trash"></i></a>
                                            {!! Form::close() !!}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                            </tfoot>
                        </table>
                        {{ $classes->links() }}
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
