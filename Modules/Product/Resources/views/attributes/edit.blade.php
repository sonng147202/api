@extends('layouts.admin_default')

@section('content')
<section class="content-header">
    <h1>
        Quản lý thuộc tính sản phẩm
    </h1>
    <ol class="breadcrumb">
        <li><a href="/admin"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="/products"> Products</a></li>
        <li class="active">Attribute</li>
    </ol>
</section>
<section class="content">
    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title">Thiết lập thuộc tính sản phẩm bảo hiểm: {{ $product->name }}</h3>
        </div>
        @if ($errors->any())
            <h4 style="color:red">{{$errors->first()}}</h4>
        @endif
        <!-- /.box-header -->
        <div class="box-body">
        {!! Form::open(['method' => 'PUT', 'class' => 'validate', 'route' => ['product.attribute.update', $product->id]]) !!}
            <div class="row">
                <div class="col-md-6">
                    @foreach ($attributes as $attribute)
                    <?php
                        $a = $productAttributes->where('attribute_id', $attribute->id)->first();
                        $val = $a ? $a->attribute_data : null;
                    ?>
                    <div class="form-group">
                        <label>{{ $attribute->title }} @if ($attribute->is_required) <span class="require">(*)</span> @endif </label>
                        @if ($attribute->data_type == "textbox")
                            {!! Form::text($attribute->id, $val, ['class' => "form-control", 'placeholder' => $attribute->name, $attribute->is_required ? 'required' : '' ]) !!}
                        @elseif ($attribute->data_type == "select")
                            @if ($attribute->is_required)
                                {!! Form::select($attribute->id, explode("|", $attribute->default_value), $val, [ "class" => "form-control select2",  "style" => "width: 100%;", "required" ] ) !!}
                            @else
                                {!! Form::select($attribute->id, array_merge([""], explode("|", $attribute->default_value)), $val, ["class" => "form-control select2",  "style" => "width: 100%;"] ) !!}
                            @endif
                        @elseif ($attribute->data_type == "radio")
                            @if (!$attribute->is_required)
                                <div class="radio">
                                    <label>Không chọn</label>
                                    {!! Form::radio($attribute->id, "", null, [ "style" => "left: 20px" ] ) !!}
                                </div>
                            @endif
                            @foreach (explode("|", $attribute->default_value) as $value)
                                <div class="radio">
                                    <label>{{ $value }}</label>
                                    {!! Form::radio($attribute->id, $value, ($val == $value), [ "style" => "left: 20px;" ] ) !!}
                                </div>
                            @endforeach
                        @elseif ($attribute->data_type == "checkbox")
                        <div class="radio">
                            {!! Form::checkbox($attribute->id, $val, ($a && $a->attribute_data), ['class' => ""] ) !!}
                        </div>
                        @else
                            {!! Form::textarea($attribute->id, $val, ['class' => "form-control", 'placeholder' => $attribute->name, $attribute->is_required ? 'required' : '' ]) !!}
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            <!-- /.row -->
        </div>
        <div class="box-footer">
            <a href="/product" class="btn btn-default pull-right">Hủy</a>
            {!! Form::button('Cập nhật', ['class' => 'btn btn-primary', 'type' => "submit"]) !!}
        </div>
        {!! Form::close() !!}
        <div class="overlay hide">
            <i class="fa fa-refresh fa-spin"></i>
        </div>
    </div>
</section>
@endsection
