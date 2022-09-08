@extends('layouts.admin_default')

@section('content')
    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title">Thêm mới thuộc tính danh mục bảo hiểm</h3>
        </div>
        @if ($errors->any())
            <h4 style="color:red">{{$errors->first()}}</h4>
        @endif
        <!-- /.box-header -->
        <div class="box-body">
        {!! Form::open(['method' => 'POST', 'route' => ['product.category_attributes.store', $categoryId]]) !!}
            <div class="row">
                <div class="col-md-6">
{{--
                    <div class="form-group">
                        <label>Danh mục</label>
                        <select name="category_id" class="form-control select2" style="width: 100%;">
                            <option value="" selected="selected">---Hãy chọn---</option>
                            @foreach($listCategory as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
--}}
                    <div class="form-group">
                        <label for="name">Mã thuộc tính</label>
                        <input name="name" type="textbox" class="form-control" placeholder="Nhập vào mã thuộc tính" required>
                    </div>

                    <div class="form-group">
                        <label for="title">Tên thuộc tính</label>
                        <input name="title" type="textbox" class="form-control" placeholder="Nhập vào tên thuộc tính" required>
                    </div>

                    <div class="form-group">
                        <label>Kiểu dữ liệu</label>
                        <select name="data_type" class="form-control select2" style="width: 100%;" required>
                            <option value="" selected="selected">---Hãy chọn---</option>
                            <option value="textbox" selected="selected">Textbox</option>
                            <option value="select">Select</option>
                            <option value="checkbox">Checkbox</option>
                            <option value="textarea">Textarea</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="is_required"> Required
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="default_value">Dữ liệu mặc định</label>
                        <input name="default_value" type="textbox" class="form-control" placeholder="Nhập vào tên thuộc tính">
                    </div>

                    <div class="form-group">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="compare_flg"> Compare flag
                            </label>
                        </div>
                    </div>

                </div>
            </div>
            <!-- /.row -->
        </div>
        <div class="box-footer">
            <a href="/product/{{$categoryId}}/category_attributes" class="btn btn-default pull-right">Hủy</a>
            {!! Form::button('Thêm mới', ['class' => 'btn btn-primary pull-left', 'type' => "submit"]) !!}
        </div>
        {!! Form::close() !!}
    </div>
@endsection
