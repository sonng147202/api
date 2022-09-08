@extends('layouts.admin_default')

@section('content')
<section class="content-header">
    <h1>
        Quản lý sản phẩm
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="{{ route('product.index') }}"> Danh sách sản phẩm</a></li>
        <li class="active">Cập nhật</li>
    </ol>
</section>
{!! Form::open(['method' => 'PUT', 'route' => ['product.sp.update', $product->id], 'class' => 'validate', 'files'=>true]) !!}
<section class="content">
    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title">Chỉnh sửa sản phẩm bảo hiểm: {{ $product->name }}</h3>
        </div>
        @if ($errors->any())
            <h4 style="color:red">{{$errors->first()}}</h4>
        @endif
        <!-- /.box-header -->
        <div class="box-body">
            <div class="row">
                <div class="col-md-6">
                    <!-- /.form-group -->
                    <div class="form-group">
                        <label>Công ty bảo hiểm(*)</label>
                        <select name="company_id" class="form-control select2" style="width: 100%;" required>
                            <option value="">---Hãy chọn---</option>
                            @foreach ($companies as $company)
                                <option value="{{ $company->id }}" {{ $product->company_id == $company->id ? "selected" : "" }}>{{ $company->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Tên sản phẩm(*)</label>
                        <input name="name" type="textbox" class="form-control" placeholder="Nhập vào tên sản phẩm bảo hiểm" required value="{{ $product->name }}">
                    </div>
                    <div class="form-group">
                        <label>Miêu tả</label>
                        {{ Form::textarea('description', $product->description ? $product->description : '', ['class' => 'form-control', 'id'=>'description']) }}
                    </div>
                    <div class="form-group">
                        <label>Nội dung chi tiết</label>
                        {{ Form::textarea('content', $product->content ? $product->content : '', ['class' => 'form-control', 'id'=>'content']) }}
                    </div>
                    <div class="form-group">
                        <label>Thông báo  cho thứ  nhất cho app </label>
                        {{ Form::textarea('popup_app1', $product->popup_app1 ? $product->popup_app1 : '', ['class' => 'form-control', 'id'=>'popup_app1']) }}
                    </div>
                    <div class="form-group">
                        <label>Thông báo  cho thứ  hai cho app</label>
                        {{ Form::textarea('popup_app2', $product->popup_app2 ? $product->popup_app2 : '', ['class' => 'form-control', 'id'=>'popup_app2']) }}
                    </div>
                    <div class="form-group">
                        <label>Thông báo  cho thứ  ba cho app</label>
                        {{ Form::textarea('popup_app3', $product->popup_app3 ? $product->popup_app3 : '', ['class' => 'form-control', 'id'=>'popup_app3']) }}
                    </div>
                    <div class="form-group" style="float: left; margin-right: 30px;">
                        <label id="avatar_label">Avatar</label> <br/>
                        <div id="contain-type-product-avatar"></div>
                        <img src="{{asset($product->avatar )}}" id="image_edit" class="thumb-image" height="100" width="150"/>
                        <input type="file" id="product_avatar" name="avatar"/>
                    </div>
                    <div class="form-group">
                        <label id="sponsor_image_label">Sponsor image</label> <br/>
                        <div id="contain-type-sponsor-image"></div>
                        <img src="{{ asset($product->sponsor_image )}}" id="sponsor_image_edit" class="thumb-image-sponsor" height="100" width="150"/>
                        <input type="file" id="sponsor_image" name="sponsor_image"/>
                    </div>
                    <div class="form-group">
                        <label>Trạng thái</label>
                        <select name="status" class="form-control select2" style="width: 100%;">
                            <option value=1 {{ $product->status == 1 ? "selected" : "" }}>Kích hoạt</option>
                            <option value=0 {{ $product->status == 0 ? "selected" : "" }}>Không sử dụng</option>
                            <option value=-1 {{ $product->status == -1 ? "selected" : "" }}>Đã xóa</option>
                        </select>
                    </div>
                    <!-- /.form-group -->
                    <div class="form-group">
                        <label>Loại hình bảo hiểm(*)</label>
                        <select name="insurance_type_id" class="form-control select2" required>
                            <option value="0">Lựa chọn loại hình bảo hiểm</option>
                            @foreach ($insuranceTypes as $type)
                                <option value="{{ $type->id }}" @if(isset($product->insurance_type->id) && $product->insurance_type->insurance_type_id == $type->id) selected @endif>{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Danh mục sản phẩm(*)</label>
                        <select id="selectCategory" name="category_ids[]" multiple class="form-control select2" required>
                            @foreach ($categories as $category)
                            <option value="{{ $category->id }}" {{ in_array($category->id, $productCategoryIds) ? "selected" : "" }}>{{ $category->prefix }} {{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Mã sản phẩm</label>
                        <input name="code" type="text" class="form-control" placeholder="Nhập vào mã sản phẩm" value="{{ $product->code }}">
                    </div>
                    <div class="input-group">
                        <label>Số tiền bảo hiểm</label>
                        <input name="Insurance_money" type="number" class="form-control" placeholder="Số tiền bảo hiểm" value="{{ $product->Insurance_money }}">
                        <div class="input-group-btn" style="width: 70px; font-size: 15px; padding-left: 10px; padding-top: 23px;"><span>VND</span></div>
                    </div>
                </div>
                <div class="col-md-5 col-md-offset-1">
                    <div class="form-group">
                        <label>Hạng sản phẩm(*)</label>
                        <select id="selectClass" name="category_class_id" class="form-control select2" required>
                            <option value="">---Hãy chọn danh mục sản phẩm---</option>
                            @foreach ($classes as $class)
                                <option value="{{ $class->id }}" {{ $product->category_class_id == $class->id ? "selected" : "" }}>{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <input type="checkbox" name="is_feature" class="minimal" value="1" @if ($product->is_feature == 1) checked @endif/>
                        <label>Sản phẩm nổi bật</label>
                    </div>
                    <div class="form-group">
                        <input type="checkbox" name="is_sponsor" class="minimal" value="1" @if ($product->is_sponsor == 1) checked @endif/>
                        <label>Sản phẩm được tài trợ</label>
                    </div>
                    <div class="form-group">
                        <input type="checkbox" name="for_customer" class="minimal" value="1" @if ($product->for_customer == 1) checked @endif/>
                        <label>Sản phẩm dành cho khách hàng</label>
                    </div>
                    <div class="form-group">
                        <input type="checkbox" name="product_type_online" class="minimal" value="1" @if ($product->product_type_online == 1) checked @endif/>
                        <label>Sản phẩm trực tuyến</label>
                    </div>
                    <div class="form-group">
                        <input type="checkbox" name="is_agency" class="minimal" value="1" @if ($product->is_agency == 1) checked @endif/>
                        <label>Sản phẩm hệ thống</label>
                    </div>
                    <div class="form-group">
                        <label>Hoa hồng của đơn vị cung cấp(*)</label>
                        <div class="row">
                            <div class="col-md-8">
                                <input name="commission_amount" type="number" class="form-control" placeholder="Nhập vào số lượng" value="{{ $productCommission ? $productCommission->commission_amount : ""}}">
                            </div>
                            <div class="col-md-4">
                                <select name="commission_type" class="form-control select2" required>
                                    <option value=0 {{ $productCommission ? ($productCommission->commission_type == 0 ? "selected" : "") : "" }}>Giá trị %</option>
                                    <option value=1 {{ $productCommission ? ($productCommission->commission_type == 1 ? "selected" : "") : "" }}>Tiền</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group" id="subsidiaries">
                        <label>Hoa hồng của công ty con(*)</label>
                        

                    </div>
                    <div class="form-group">
                        <label>Mức hoa hồng dành cho đại lý</label>
                        <table id="commissionLevelTable" class="table table-bordered table-hover">
                            <thead>
                            <tr>
                                <th>Mức hoa hồng</th>
                                <th>Hoa hồng cá nhân (%)</th>
                                <th>Phần trăm đồng cấp (%)</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($product->levels as $level)
                                <tr>
                                    <td>{{ $level->name }}</td>
                                    <td><input required name="commission_rate[{{ $level->level }}]" type="number" class="form-control" placeholder="Nhập vào hoa hồng cá nhân" value="{{ $level->pivot->commission_rate }}"/></td>
                                    <td><input required name="counterpart_commission_rate[{{ $level->level }}]" type="number" class="form-control" placeholder="Nhập vào phần trăm đồng cấp" value="{{ $level->pivot->counterpart_commission_rate }}"/></td>
                                </tr>
                            @endforeach
                            </tbody>
                            <tfoot>
                            </tfoot>
                        </table>
                    </div>
                    <div class="form-group">
                        <label>Mức điểm thưởng cho khách hàng (%):</label>
                        <input name="customer_commission" type="number" class="form-control" placeholder="Nhập vào tỉ lệ %" value="{{ isset($productCustomerCommission) ? $productCustomerCommission->commission_amount : ""}}">
                    </div>
                    <div class="form-group">
                        <label>Công thức tính phí</label>
                        <select name="insurance_formula_id" class="form-control select2">
                            <option value="0">--- Mặc định ---</option>
                            @foreach($formulas as $formula)
                            <option value="{{ $formula->id }}" @if ($product->insurance_formula_id == $formula->id) selected @endif>{{ $formula->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Sản phẩm phụ cho:</label>
                        <div class="row">
                            <div class="col-md-6 col-xs-12">
                                <div class="form-group">
                                    <label>Loại hình bảo hiểm</label>
                                    <select name="extra_for_insurance_type" class="form-control select2">
                                        <option value="0">Không áp dụng</option>
                                        @foreach ($insuranceTypes as $type)
                                        <option value="{{ $type->id }}" @if ($product->extra_for_insurance_type == $type->id) selected @endif>{{ $type->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6 col-xs-12">
                                <div class="form-group">
                                    <label>Sản phẩm</label>
                                    <select name="extra_for_product" class="form-control select2">
                                        <option value="0">Không áp dụng</option>
                                        @if (isset($listExtraForProducts))
                                        @foreach($listExtraForProducts as $id => $name)
                                        <option value="{{ $id }}" @if ($product->extra_for_product == $id) selected @endif>{{ $name }}</option>
                                        @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <!-- /.row -->
        </div>
        <div class="overlay hide">
            <i class="fa fa-refresh fa-spin"></i>
        </div>
    </div>
</section>
<section class="content">
    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title">Cập nhật giá trị mặc định cho thuộc tính giá</h3>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="box-body">
            @if (isset($priceAttributes))
                <?php
                // Get default value from product
                $defaultPriceAttributeValues = json_decode($product->default_price_attribute_values, true);
                ?>
                <div class="row">
                    @foreach($priceAttributes as $priceAttribute)
                        <div class="col-md-4 col-sm-6 col-xs-12">
                            <div class="form-group">
                                <label>{{ $priceAttribute['title'] }}</label>
                                <textarea name="default_price_attribute_value[{{ $priceAttribute['code'] }}]" class="form-control">{{ isset($defaultPriceAttributeValues[$priceAttribute['code']]) ? $defaultPriceAttributeValues[$priceAttribute['code']] : $priceAttribute['default_value'] }}</textarea>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</section>
<section class="content">
    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title">Cấu hình phụ phí</h3>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="box-body">
            @if (isset($extraFees) && !empty($extraFees))
                <?php
                // Get default extra fee attribute values
                $defaultExtraFeeAttributeValues = json_decode($product->default_extra_fee_attribute_values, true);
                ?>
                <div class="row">
                <?php $productExtraFees = !empty($product->extra_fees) ? json_decode($product->extra_fees, true) : [];?>
                @foreach($extraFees as $key => $item)
                    <?php $defaultAttributeValues = isset($defaultExtraFeeAttributeValues[$item['code']]) ? $defaultExtraFeeAttributeValues[$item['code']] : []; ?>
                    <div class="col-lg-4 col-md-6 col-sm-12">
                        <div class="form-group">
                            <label>{{ $item['name'] }}</label>
                            @if (!empty($item['price_type']))
                                <input type="text" name="extra_fee[{{ $item['price_type'] }}]" value="{{ isset($productExtraFees[$item['price_type']]) ? $productExtraFees[$item['price_type']] : 0 }}" class="form-control"/>
                            @endif
                            <?php $priceAttributes = json_decode($item['price_attributes'], true);?>
                            @if (!empty($priceAttributes))
                                @foreach($priceAttributes as $attribute)
                                    <div class="form-group">
                                        <label>Giá trị mặc định cho: {{ $attribute['attr_name'] }}</label>
                                        <textarea name="default_extra_fee_attribute_values[{{ $item['code'] }}][{{ $attribute['attr_code'] }}]" class="form-control">{{ isset($defaultAttributeValues[$attribute['attr_code']]) ? $defaultAttributeValues[$attribute['attr_code']] : '' }}</textarea>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                @endforeach
                </div>
            @endif
        </div>
    </div>
</section>
<section class="content">
    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title">Thông tin thêm</h3>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="box-body">
                <div class="row">
                        <div class="col-md-4 col-sm-6 col-xs-12">
                            <div class="form-group">
                                <label>Chỉ số PEYP</label><br>
                                <input class= "form-control" type="number" name="PEYP" value="{{ $product->PEYP }}">

                                <label>Kiểu GCN đối tác trả về:</label><br>
                                <input class= "form-control" type="text" name="file_type" value="{{ $product->file_type }}">

                                <label>Là bảo hiểm nhân thọ?</label><br>

                                @if ($product->is_life == 1)
                                <input type="radio" id="yes" name="is_life" value="1" checked = {{ "checked" }}>
                                <label for="yes">Có</label><br>
                                <input type="radio" id="no" name="is_life" value="0">
                                <label for="no">Không</label><br>
                                @else
                                <input type="radio" id="yes" name="is_life" value="1">
                                <label for="yes">Có</label><br>
                                <input type="radio" id="no" name="is_life" value="0" checked = {{ "checked" }}>
                                <label for="no">Không</label><br>
                                @endif

                                <label>Đây là sản phẩm hot ?</label><br>
                                @if ($product->is_hot == 1)
                                    <input type="radio" id="yes" name="is_hot" value="1" checked = {{ "checked" }}>
                                    <label for="yes">Có</label><br>
                                    <input type="radio" id="no" name="is_hot" value="0">
                                    <label for="no">Không</label><br>
                                @else
                                    <input type="radio" id="yes" name="is_hot" value="1">
                                    <label for="yes">Có</label><br>
                                    <input type="radio" id="no" name="is_hot" value="0" checked = {{ "checked" }}>
                                    <label for="no">Không</label><br>
                                @endif


                            </div>
                        </div>
                </div>
        </div>
    </div>
</section>
<section>
    <div class="box-footer">
        <a href="/product" class="btn btn-default pull-right">Hủy</a>
        {!! Form::button('Cập nhật', ['class' => 'btn btn-primary', 'type' => "submit"]) !!}
    </div>
</section>
{!! Form::close() !!}

@include('product::products.modalUploadImage')
@endsection
@section('scripts')
    <script>
        CKEDITOR.replace( 'content');
        CKEDITOR.replace( 'description');
        CKEDITOR.replace( 'popup_app3');
        CKEDITOR.replace( 'popup_app2');
        CKEDITOR.replace( 'popup_app1');
    </script>
    <script type="text/javascript" src="{{ asset('/modules/product/js/product.js?v=1.1') }}"></script>
@endsection
