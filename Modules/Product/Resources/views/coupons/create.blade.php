@extends('layouts.admin_default')

@section('content')
    <section class="content-header">
        <h1>Quản lý mã giảm giá</h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin_home') }}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="{{ route('product.coupons.index') }}"> Danh sách mã giảm giá</a></li>
            <li class="active">Thêm mới</li>
        </ol>
    </section>
    <section class="content">
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">Thêm mới mã giảm giá</h3>
            </div>
            @if ($errors->any())
                <h4 style="color:red">{{$errors->first()}}</h4>
            @endif
            <!-- /.box-header -->
            <div class="box-body">
            {!! Form::open(['method' => 'POST', 'route' => ['product.coupons.store'], 'class' => 'validate']) !!}
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Ngày bắt đầu(*)</label>
                            <div class="input-group date">
                                <div class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </div>
                                <input name="start_time" type="text" class="form-control pull-right" id="datepickerStart" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Ngày kết thúc(*)</label>
                            <div class="input-group date">
                                <div class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </div>
                                <input name="end_time" type="text" class="form-control pull-right" id="datepickerEnd" required>
                            </div>
                        </div>

                        <!-- /.form-group -->
                        <div class="form-group">
                            <label>Loại hình giảm giá(*)</label>
                            <select name="sale_off_type" class="form-control select2" required>
                                <option value=0 selected="selected">Giá trị %</option>
                                <option value=1>Tiền</option>
                            </select>
                        </div>
                        <!-- /.form-group -->

                        <div class="form-group">
                            <label>Giá trị giảm giá(*)</label>
                            <input name="sale_off_amount" type="number" class="form-control" placeholder="Nhập vào số lượng" required>
                        </div>
                    </div>

                </div>
                <!-- /.row -->
            </div>
            <div class="box-footer">
                <a href="/product/coupons" class="btn btn-default pull-right">Hủy</a>
                {!! Form::button('Thêm mới', ['class' => 'btn btn-primary pull-left', 'type' => "submit"]) !!}
            </div>
            {!! Form::close() !!}
        </div>
    </section>
@endsection

@section('scripts')
<script>
    //Date picker
    $('#datepickerStart').datepicker({
       autoclose: true
    })
    $('#datepickerEnd').datepicker({
       autoclose: true
    })
</script>
@endsection
