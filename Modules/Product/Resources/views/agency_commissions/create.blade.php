@extends('layouts.admin_default')

@section('content')
    <section class="content-header">
        <h1>Quản lý hoa hồng của Ebaohiem dành cho đại lý</h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin_home') }}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="{{ route('product.agency_commissions.index') }}"> Danh sách hoa hồng cho đại lý</a></li>
            <li class="active">Thêm mới</li>
        </ol>
    </section>
    <section class="content">
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">Thêm mới hoa hồng của Ebaohiem dành cho đại lý</h3>
            </div>
            @if ($errors->any())
                <h4 style="color:red">{{$errors->first()}}</h4>
            @endif
            <!-- /.box-header -->
            <div class="box-body">
            {!! Form::open(['method' => 'POST', 'route' => ['product.agency_commissions.store'], 'class' => 'validate']) !!}
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Tên sản phẩm(*)</label>
                            <select name="product_id" class="form-control select2" required>
                                <option value="">---Hãy chọn---</option>
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Tên đại lý(*)</label>
                            <select name="agency_id" class="form-control select2" required>
                                <option value="">---Hãy chọn---</option>
                                @foreach ($insuranceAgencies as $insuranceAgencie)
                                    <option value="{{ $insuranceAgencie->id }}">{{ $insuranceAgencie->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- /.form-group -->
                        <div class="form-group">
                            <label>Loại hình hoa hồng</label>
                            <select name="commission_type" class="form-control select2" style="width: 100%;">
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
                <a href="/product/agency_commissions" class="btn btn-default pull-right">Hủy</a>
                {!! Form::button('Thêm mới', ['class' => 'btn btn-primary pull-left', 'type' => "submit"]) !!}
            </div>
            {!! Form::close() !!}
        </div>
    </section>
@endsection
