@extends('layouts.admin_default')
@section('title', 'Quản lý hoa hồng')
@section('content')
    <section class="content-header">
        <h1>Quản lý hoa hồng</h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin_home') }}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="{{ route('product.commissions.index') }}"> Danh sách hoa hồng</a></li>
            <li class="active">Thêm mới</li>
        </ol>
    </section>
    <section class="content">
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">Thêm mới hoa hồng</h3>
            </div>
            @if ($errors->any())
                <h4 style="color:red">{{$errors->first()}}</h4>
            @endif
            <!-- /.box-header -->
            <div class="box-body">
            {!! Form::open(['method' => 'POST', 'route' => ['product.commissions.store'], 'class' => 'validate']) !!}
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Tên hoa hồng(*)</label>
                            <input name="name" type="textbox" class="form-control" placeholder="Nhập vào tên hoa hồng" required>
                        </div>

                        <!-- /.form-group -->
                        <div class="form-group">
                            <label>Loại hình hoa hồng(*)</label>
                            <select name="commission_type" class="form-control select2" required>
                                <option value=0 selected="selected">Giá trị %</option>
                                <option value=1>Tiền</option>
                            </select>
                        </div>
                        <!-- /.form-group -->

                        <div class="form-group">
                            <label>Giá trị(*)</label>
                            <input name="commission_amount" type="number" class="form-control" placeholder="Nhập vào số lượng" required>
                        </div>
                    </div>

                </div>
                <!-- /.row -->
            </div>
            <div class="box-footer">
                <a href="/product/commissions" class="btn btn-default pull-right">Hủy</a>
                {!! Form::button('Thêm mới', ['class' => 'btn btn-primary pull-left', 'type' => "submit"]) !!}
            </div>
            {!! Form::close() !!}
        </div>
    </section>
@endsection
