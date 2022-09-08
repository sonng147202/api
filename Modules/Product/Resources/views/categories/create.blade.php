@extends('layouts.admin_default')
@section('title', 'Danh mục sản phẩm')
@section('content')
    <section class="content-header">
        <h1>
            Quản lý danh mục sản phẩm
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin_home') }}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="{{ route('product.categories.index') }}"> Danh mục sản phẩm</a></li>
            <li class="active">Thêm mới</li>
        </ol>
    </section>
    <section class="content">
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">Thêm mới danh mục sản phẩm</h3>
            </div>
            @if ($errors->any())
                <h4 style="color:red">{{$errors->first()}}</h4>
            @endif
            <!-- /.box-header -->
            <div class="box-body">
            {!! Form::open(['method' => 'POST', 'route' => ['product.categories.store'], 'class' => 'validate']) !!}
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Loại hình bảo hiểm(*)</label>
                            <select name="insurance_type_id" class="form-control select2" style="width: 100%;" required>
                                <option value="">---Hãy chọn---</option>
                                @foreach ($insuranceTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="exampleInputEmail1">Tên danh mục(*)</label>
                            <input name="name" type="textbox" class="form-control" placeholder="Nhập vào tên danh mục bảo hiểm" required>
                        </div>

                        <div class="form-group">
                            <label>Danh mục cha</label>
                            <select name="parent_id" class="form-control select2" style="width: 100%;">
                                <option value="">---Hãy chọn---</option>
                                @foreach ($listCategory as $category)
                                    <option value="{{ $category->id }}">{{ $category->prefix }} {{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Miêu tả</label>
                            {{ Form::textarea('description', null, ['class' => 'form-control']) }}
                        </div>

                        <div class="form-group">
                            <label>Logo</label>
                            <input name="avatar" type="textbox" class="form-control" placeholder="Nhập vào logo url">
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
                <a href="/product/categories" class="btn btn-default pull-right">Hủy</a>
                {!! Form::button('Thêm mới', ['class' => 'btn btn-primary pull-left', 'type' => "submit"]) !!}
            </div>
            {!! Form::close() !!}
        </div>
    </section>
@endsection
