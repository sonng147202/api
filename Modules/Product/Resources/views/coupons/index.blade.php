@extends('layouts.admin_default')

@section('content')
    <section class="content-header">
        <h1>Quản lý mã giảm giá</h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin_home') }}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Danh sách mã giảm giá</li>
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
                        <h3 class="box-title">Quản lý coupon</h3>
                        <div class="pull-right">
                            <a class="btn btn-primary btn-sm" href="/product/coupons/create"><i class="fa fa-plus"></i> Thêm mã giảm giá</a>
                        </div>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        <table id="example2" class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Mã</th>
                                    <th>Ngày bắt đầu</th>
                                    <th>Ngày kết thúc</th>
                                    <th>Trạng thái</th>
                                    <th>Loại hình giảm giá</th>
                                    <th>Giá trị giảm giá</th>
                                    <th class="text-center">Sửa</th>
                                    <th class="text-center">Xóa</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($coupons as $coupon)
                                <tr>
                                    <td>{{ $coupon->id }}</td>
                                    <td>{{ $coupon->coupon_code }}</td>
                                    <td>{{ Carbon\Carbon::parse($coupon->start_time)->format('d/m/Y') }}</td>
                                    <td>{{ Carbon\Carbon::parse($coupon->end_time)->format('d/m/Y') }}</td>
                                    <td>{{ $coupon->getStatusName() }}</td>
                                    <td>{{ $coupon->getCommissionTypeName() }}</td>
                                    <td>{{ $coupon->getCouponAmountFormat() }}</td>
                                    <td class="text-center">
                                        {!! Form::open(['method' => 'GET', 'route' => ['product.coupons.edit', $coupon->id]]) !!}
                                            <a href="#" class="btn btn-warning btn-xs" onclick="$(this).closest('form').submit();"><i class="fa fa-pencil"></i></a>
                                        {!! Form::close() !!}
                                    </td>
                                    <td class="text-center">
                                        @if ($coupon->status != constant('Modules\Product\Models\Coupon::STATUS_DELETED'))
                                            {!! Form::open(['method' => 'DELETE', 'route' => ['product.coupons.destroy', $coupon->id]]) !!}
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
                        {{ $coupons->links() }}
                    </div>
                    <!-- /.box-body -->
                </div>
                <!-- /.box -->
            </div>
            <!-- /.col -->
        </div>
    </section>
@endsection
