@extends('layouts.admin_default')

@section('content')
    <section class="content-header">
        <h1>Hoa hồng của Ebaohiem dành cho đại lý</h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin_home') }}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Hoa hồng cho đại lý</li>
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
                    <h3 class="box-title">Hoa hồng của Ebaohiem dành cho đại lý</h3>
                    <div class="pull-right">
                        <a class="btn btn-primary btn-sm" href="/product/agency_commissions/create"><i class="fa fa-plus"></i> Thêm mức hoa hồng cho đại lý</a>
                    </div>
                </div>
                <!-- /.box-header -->
                <div class="box-body">
                    <table id="example2" class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tên sản phẩm</th>
                                <th>Tên đại lý</th>
                                <th>Loại hình hoa hồng</th>
                                <th>Giá trị</th>
                                <th class="text-center">Sửa</th>
                                <th class="text-center">Xóa</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($items as $item)
                            <tr>
                                <td>{{ $item->id }}</td>
                                <td>{{ !empty($item->product) ? $item->product->name : ''}}</td>
                                <td>{{ !empty($item->insurance_agency) ? $item->insurance_agency->name : '' }}</td>
                                <td>{{ $item->getCommissionTypeName() }}</td>
                                <td>{{ $item->getCommissionAmountFormat() }}</td>
                                <td class="text-center">
                                    {!! Form::open(['method' => 'GET', 'route' => ['product.agency_commissions.edit', $item->id]]) !!}
                                        <a href="#" class="btn btn-warning btn-xs" onclick="$(this).closest('form').submit();"><i class="fa fa-pencil"></i></a>
                                    {!! Form::close() !!}
                                </td>
                                <td class="text-center">
                                    {!! Form::open(['method' => 'DELETE', 'route' => ['product.agency_commissions.destroy', $item->id]]) !!}
                                        <a href="#" class="btn btn-danger btn-xs"  onclick="if(confirm('Bạn có chắc muốn xóa bản ghi này không?')) $(this).closest('form').submit();"><i class="fa fa-trash"></i></a>
                                    {!! Form::close() !!}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                        </tfoot>
                    </table>
                    {{ $productAgencyCommissions->links() }}
                </div>
                <!-- /.box-body -->
            </div>
            <!-- /.box -->
        </div>
        <!-- /.col -->
    </div>
    </section>
@endsection
