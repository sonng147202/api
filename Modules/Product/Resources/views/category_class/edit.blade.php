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
            <li class="active">Cập nhật</li>
        </ol>
    </section>
    <section class="content">
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">Chỉnh sửa hạng sản phẩm</h3>
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
            {!! Form::open(['method' => 'PUT', 'class' => 'validate', 'route' => ['product.category_class.update', $category->id, $class->id]]) !!}
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Tên hạng <span class="require">*</span></label>
                            <input name="name" type="text" class="form-control" placeholder="Nhập vào tên hạng" required value="{{ $class->name }}">
                        </div>
                        <div class="form-group">
                            <label>Thứ tự</label>
                            <select name="order_number" class="form-control select2" style="width: 100%;">
                                @for($i = 1; $i <= $total; $i++)
                                    <option @if($i == $class->order_number) selected @endif value="{{ $i }}">{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        <!-- /.form-group -->
                        <div class="form-group">
                            <label>Trạng thái</label>
                            <select name="status" class="form-control select2" style="width: 100%;">
                                <option value=1 {{ $class->status == 1 ? "selected" : "" }}>Kích hoạt</option>
                                <option value=0 {{ $class->status == 0 ? "selected" : "" }}>Không sử dụng</option>
                                <option value=-1 {{ $class->status == -1 ? "selected" : "" }}>Đã xóa</option>
                            </select>
                        </div>
                        <!-- /.form-group -->
                    </div>
                </div>
                <!-- /.row -->
            </div>
            <div class="box-footer">
                <a href="{{ route('product.category_class.index', $category->id) }}" class="btn btn-default pull-right">Hủy</a>
                {!! Form::button('Cập nhật', ['class' => 'btn btn-primary pull-left', 'type' => "submit"]) !!}
            </div>
            {!! Form::close() !!}
            <div class="overlay hide">
                <i class="fa fa-refresh fa-spin"></i>
            </div>
        </div>
    </section>
@endsection
