@extends('layouts.admin_default')
@section('title', 'Hạng sản phẩm')
@section('content')
    <section class="content-header">
        <h1>
            Quản lý hạng sản phẩm
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin_home') }}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="{{ route('product.category_class.list_category') }}"> Hạng sản phẩm</a></li>
            <li class="active">Thêm mới</li>
        </ol>
    </section>
    <section class="content">
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">Thêm mới hạng sản phẩm</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif
            {!! Form::open(['method' => 'POST', 'class' => 'validate', 'route' => ['product.category_class.store', $category->id]]) !!}
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Tên hạng <span class="require">*</span></label>
                            <input name="name" type="text" class="form-control" placeholder="Nhập vào tên hạng" required>
                        </div>
                        <div class="form-group">
                            <label>Thứ tự</label>
                            <select name="order_number" class="form-control select2" style="width: 100%;">
                                @for($i = 0; $i <= $total; $i++)
                                    <option @if($i == $total) selected @endif value="{{ ($i+1) }}">{{ ($i + 1) }}</option>
                                @endfor
                            </select>
                        </div>
                        <!-- /.form-group -->
                        <div class="form-group">
                            <label>Trạng thái</label>
                            <select name="status" class="form-control select2" style="width: 100%;">
                                <option value=1 selected="selected">Kích hoạt</option>
                                <option value=0>Không sử dụng</option>
                            </select>
                        </div>
                        <!-- /.form-group -->
                    </div>
                </div>
                <!-- /.row -->
            </div>
            <div class="box-footer">
                <a href="{{ route('product.category_class.index', $category->id) }}" class="btn btn-default pull-right">Hủy</a>
                {!! Form::button('Thêm mới', ['class' => 'btn btn-primary pull-left', 'type' => "submit"]) !!}
            </div>
            {!! Form::close() !!}
            <div class="overlay hide">
                <i class="fa fa-refresh fa-spin"></i>
            </div>
        </div>
    </section>
@endsection
