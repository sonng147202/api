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
                <div class="box box-info">
                    <div class="box-header">
                        <h3 class="box-title">Lựa chọn danh mục</h3>
                        <div class="pull-right">
                        </div>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        <table id="example2" class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Tên</th>
                                    <th>Loại hình bảo hiểm</th>
                                    <th>Logo</th>
                                    <th>Trạng thái</th>
                                    <th class="text-center">Danh sách hạng</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($categories as $category)
                                    <tr>
                                        <td>{{ $category->id }}</td>
                                        <td>{{ $category->name }}</td>
                                        <td>{{ $category->insurance_type? $category->insurance_type->name : null }}</td>
                                        <td><img src="{{ $category->avatar }}" alt="logo" width="30" height="30"/></td>
                                        <td>{{ $category->getStatusName() }}</td>
                                        <td class="text-center">
                                            <a href="{{ route('product.category_class.index', $category->id) }}" class="btn btn-primary btn-xs"><i class="fa fa-bars"></i></a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                            </tfoot>
                        </table>
                        {{ $categories->links() }}
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
