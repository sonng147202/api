@extends('layouts.admin_default')
@section('title', 'Tạo sản phẩm')
@section('content')
    <section class="content-header">
        <h1>Quản lý sản phẩm</h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin_home') }}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="{{ route('product.index') }}"> Danh sách sản phẩm</a></li>
            <li class="active">Thêm mới</li>
        </ol>
    </section>
    <section class="content">
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">Thêm mới sản phẩm bảo hiểm</h3>
            </div>
            @if ($errors->any())
                <h4 style="color:red">{{$errors->first()}}</h4>
            @endif
            <!-- /.box-header -->
            <div class="box-body">
            {!! Form::open(['method' => 'POST', 'route' => ['product.sp.store'], 'class' => 'validate', 'files'=>true]) !!}
                <div class="row">
                    <div class="col-md-6">
                        <!-- /.form-group -->
                        <div class="form-group">
                            <label>Công ty bảo hiểm(*)</label>
                            <select name="company_id" class="form-control select2" style="width: 100%;" required>
                                <option value="">---Hãy chọn---</option>
                                @foreach ($companies as $company)
                                    <option value="{{ $company->id }}">{{ $company->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Tên sản phẩm(*)</label>
                            <input name="name" type="textbox" class="form-control" placeholder="Nhập vào tên sản phẩm bảo hiểm" required>
                        </div>
                        <div class="form-group">
                            <label>Miêu tả</label>
                            {{ Form::textarea('description', null, ['class' => 'form-control', 'id'=>'description']) }}
                        </div>
                        <div class="form-group">
                            <label>Nội dung chi tiết</label>
                            {{ Form::textarea('content', null, ['class' => 'form-control', 'id'=>'content']) }}
                        </div>
                        <div class="form-group" style="float: left; margin-right: 30px;">
                            <label id="avatar_label">Avatar</label> <br/>
                            <div id="contain-type-product-avatar"></div>
                            <img src="" id="image_edit" class="thumb-image" height="100" width="150"/>
                            <input type="file" id="product_avatar" name="avatar"/>
                        </div>
                        <div class="form-group">
                            <label id="sponsor_image_label">Sponsor image</label> <br/>
                            <div id="contain-type-sponsor-image"></div>
                            <img src="" id="sponsor_image_edit" class="thumb-image-sponsor" height="100" width="150"/>
                            <input type="file" id="sponsor_image" name="sponsor_image"/>
                        </div>
                        <div class="form-group">
                            <label>Trạng thái</label>
                            <select name="status" class="form-control select2" style="width: 100%;">
                                <option value=1 selected="selected">Kích hoạt</option>
                                <option value=0>Không sử dụng</option>
                            </select>
                        </div>
                        <!-- /.form-group -->
                        <div class="form-group">
                            <label>Loại hình bảo hiểm(*)</label>
                            <select name="insurance_type_id" class="form-control select2" required>
                                @foreach ($insuranceTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Danh mục sản phẩm(*)</label>
                            <select id="selectCategory" name="category_ids[]" multiple class="form-control select2" required>
                                @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->prefix }} {{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Mã sản phẩm</label>
                            <input name="code" type="text" class="form-control" placeholder="Nhập vào mã sản phẩm">
                        </div>
                        <div class="input-group">
                            <label>Số tiền bảo hiểm</label>
                            <input name="Insurance_money" type="number" class="form-control" placeholder="Số tiền bảo hiểm">
                            <div class="input-group-btn" style="width: 70px; font-size: 15px; padding-left: 10px; padding-top: 23px;"><span>VND</span></div>
                        </div>
                    </div>
                    <div class="col-md-5 col-md-offset-1">
                        <div class="form-group">
                            <label>Hạng sản phẩm(*)</label>
                            <select id="selectClass" name="category_class_id" class="form-control select2" required>
                                <option value="">---Hãy chọn danh mục sản phẩm---</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Sản phẩm nổi bật</label>
                            <input type="checkbox" name="is_feature" class="minimal" value="1"/>
                        </div>

                        <div class="form-group">
                            <label>Sản phẩm được tài trợ</label>
                            <input type="checkbox" name="is_sponsor" class="minimal" value="1"/>
                        </div>
                        <div class="form-group">
                            <label>Sản phẩm dành cho khách hàng</label>
                            <input type="checkbox" name="for_customer" class="minimal" value="1"/>
                        </div>
                        <div class="form-group">
                            <label>Sản phẩm trực tuyến</label>
                            <input type="checkbox" name="product_type_online" class="minimal" />
                        </div>
                        <div class="form-group">
                            <label>Sản phẩm hệ thống</label>
                            <input type="checkbox" name="is_agency" class="minimal" value="1"/>
                        </div>
                        <div class="form-group">
                            <label>Hoa hồng của đơn vị cung cấp(*)</label>
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label>- Số lượng</label>
                                        <input name="commission_amount" type="number" class="form-control" placeholder="Nhập vào số lượng">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label>- Loại hình hoa hồng</label>
                                    <select name="commission_type" class="form-control">
                                        <option value=0 selected="selected">Giá trị %</option>
                                        <option value=1>Tiền</option>
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
                                @foreach ($levels as $level)
                                    <tr>
                                        <td>{{ $level->name }}</td>
                                        <td><input required name="commission_rate[{{ $level->id }}]" type="number" class="form-control" placeholder="Nhập vào hoa hồng cá nhân" value=""/></td>
                                        <td><input required name="counterpart_commission_rate[{{ $level->id }}]" type="number" class="form-control" placeholder="Nhập vào phần trăm đồng cấp" value=""/></td>
                                    </tr>
                                @endforeach
                                </tbody>
                                <tfoot>
                                </tfoot>
                            </table>
                        </div>
                        <div class="form-group">
                            <label>Mức điểm thưởng cho khách hàng (%):</label>
                            <input name="customer_commission" type="number" class="form-control" placeholder="Nhập vào tỉ lệ %" value="{{ old('customer_commission') }}">
                        </div>
                        <div class="form-group">
                            <label>Công thức tính phí</label>
                            <select name="insurance_formula_id" class="form-control select2">
                                <option value="0">--- Mặc định ---</option>
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
                                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 col-xs-12">
                                    <div class="form-group">
                                        <label>Sản phẩm</label>
                                        <select name="extra_for_product" class="form-control select2">
                                            <option value="0">Không áp dụng</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.row -->
            </div>
            <div class="box-footer">
                <a href="/product" class="btn btn-default pull-right">Hủy</a>
                {!! Form::button('Thêm mới', ['class' => 'btn btn-primary pull-left', 'type' => "submit"]) !!}
            </div>
            {!! Form::close() !!}
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
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
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
                                    <textarea name="default_price_attribute_value[{{ $priceAttribute['code'] }}]" class="form-control">{{ !isset($defaultPriceAttributeValues[$priceAttribute['code']]) ? $defaultPriceAttributeValues[$priceAttribute['code']] : $priceAttribute['default_value'] }}</textarea>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </section>
@include('product::products.modalUploadImage')
@endsection
@section('scripts')
<script>
    CKEDITOR.replace( 'content');
    CKEDITOR.replace( 'description');
</script>
<script type="text/javascript" src="{{ asset('/modules/product/js/product.js?v=1.1') }}"></script>
    <script type="text/javascript">
        $(function() {

        });
    </script>
@endsection
