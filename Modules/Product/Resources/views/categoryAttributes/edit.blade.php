@extends('layouts.admin_default')

@section('content')
    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title">Chỉnh sửa thuộc tính danh mục bảo hiểm</h3>
        </div>
        @if ($errors->any())
            <h4 style="color:red">{{$errors->first()}}</h4>
        @endif
        <!-- /.box-header -->
        <div class="box-body">
        {!! Form::open(['method' => 'PUT', 'route' => ['product.category_attributes.update', $categoryId, $productCategory->id]]) !!}
            <div class="row">
                <div class="col-md-6">
{{--
                    <div class="form-group">
                        <label>Danh mục</label>
                        <select name="category_id" class="form-control select2" style="width: 100%;">
                            <option value="" selected="selected">---Hãy chọn---</option>
                            @foreach($listCategory as $category)
                                <option value="{{ $category->id }}" {{ $category->id ==  $productCategory->category_id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
--}}
                    <div class="form-group">
                        <label for="name">Mã thuộc tính</label>
                        <input name="name" type="textbox" class="form-control" placeholder="Nhập vào mã thuộc tính" required value="{{ $productCategory->name }}">
                    </div>

                    <div class="form-group">
                        <label for="title">Tên thuộc tính</label>
                        <input name="title" type="textbox" class="form-control" placeholder="Nhập vào tên thuộc tính" required value="{{ $productCategory->title }}">
                    </div>

                    <div class="form-group">
                        <label>Kiểu dữ liệu</label>
                        <select name="data_type" class="form-control select2" style="width: 100%;" required>
                            <option value="" selected="selected">---Hãy chọn---</option>
                            <option value="textbox" {{ $productCategory->data_type == "textbox" ? "selected" : "" }}>Textbox</option>
                            <option value="select"{{ $productCategory->data_type == "select" ? "selected" : "" }}>Select</option>
                            <option value="checkbox" {{ $productCategory->data_type == "checkbox" ? "selected" : "" }}>Checkbox</option>
                            <option value="textarea" {{ $productCategory->data_type == "textarea" ? "selected" : "" }}>Textarea</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="is_required" {{ $productCategory->is_required ? "checked" : "" }}> Required
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="default_value">Dữ liệu mặc định</label>
                        <input name="default_value" type="textbox" class="form-control" placeholder="Nhập vào tên thuộc tính" value="{{ $productCategory->default_value }}">
                    </div>

                    <div class="form-group">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="compare_flg" {{ $productCategory->compare_flg ? "checked" : "" }}> Compare flag
                            </label>
                        </div>
                    </div>

                </div>
            </div>
            <!-- /.row -->
        </div>
        <div class="box-footer">
            <a href="/product/{{$categoryId}}/category_attributes" class="btn btn-default pull-right">Hủy</a>
            {!! Form::button('Sửa', ['class' => 'btn btn-primary pull-left', 'type' => "submit"]) !!}
        </div>
        {!! Form::close() !!}
    </div>
@endsection
