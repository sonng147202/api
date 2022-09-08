@extends('layouts.admin_default')
@section('title', 'Quản lý hoa hồng')
@section('content')
    <section class="content-header">
        <h1>Quản lý hoa hồng</h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin_home') }}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Danh sách hoa hồng</li>
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
                    <h3 class="box-title">Quản lý hoa hồng</h3>
                    <div class="pull-right">
                        <a class="btn btn-primary btn-sm" href="/product/commissions/create"><i class="fa fa-plus"></i> Thêm mức hoa hồng</a>
                    </div>
                </div>
                <!-- /.box-header -->
                <div class="box-body">
                    <table id="example2" class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tên hoa hồng</th>
                                <th>Loại hình hoa hồng</th>
                                <th>Giá trị</th>
                                <th class="text-center">Sửa</th>
                                <th class="text-center">Xóa</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($commissions as $commission)
                            <tr>
                                <td>{{ $commission->id }}</td>
                                <td>{{ $commission->name }}</td>
                                <td>{{ $commission->getCommissionTypeName() }}</td>
                                <td>{{ $commission->getCommissionAmountFormat() }}</td>
                                <td class="text-center">
                                    {!! Form::open(['method' => 'GET', 'route' => ['product.commissions.edit', $commission->id]]) !!}
                                        <a href="#" class="btn btn-warning btn-xs" onclick="$(this).closest('form').submit();"><i class="fa fa-pencil"></i></a>
                                    {!! Form::close() !!}
                                </td>
                                <td class="text-center">
                                    {!! Form::open(['method' => 'DELETE', 'route' => ['product.commissions.destroy', $commission->id]]) !!}
                                        <a href="#" class="btn btn-danger btn-xs"  onclick="if(confirm('Bạn có chắc muốn xóa bản ghi này không?')) $(this).closest('form').submit();"><i class="fa fa-trash"></i></a>
                                    {!! Form::close() !!}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                        </tfoot>
                    </table>
                    {{ $commissions->links() }}
                </div>
                <!-- /.box-body -->
            </div>
            <!-- /.box -->
        </div>
        <!-- /.col -->
    </div>
    </section>
@endsection
