@extends('layouts.admin_default')
@section('title', 'Tạo sản phẩm')
@section('content')
    <section class="content-header">
        <h1>Hoa hồng theo sản phẩm</h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin_home') }}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="{{ route('product.index') }}"> Danh sách sản phẩm</a></li>
        </ol>
    </section>
    <section class="content">
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">Thêm hoa hồng theo sản phẩm bảo hiểm</h3>
            </div>
            @if ($errors->any())
                <h4 style="color:red">{{$errors->first()}}</h4>
            @endif
            <!-- /.box-header -->
            <div class="box-body">
            {!! Form::open(['method' => 'POST', 'route' => ['store.product.level', $product->id], 'class' => 'validate', 'files'=>true]) !!}
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
            </div>
            <div class="box-footer">
                <a href="/product" class="btn btn-default pull-right">Hủy</a>
                {!! Form::button('Thêm mới', ['class' => 'btn btn-primary pull-left', 'type' => "submit"]) !!}
            </div>
            {!! Form::close() !!}
        </div>
    </section>

@endsection
@section('scripts')
<script>
    CKEDITOR.replace( 'content');
</script>
<script type="text/javascript" src="{{ asset('/modules/product/js/product.js?v=1.1') }}"></script>
    <script type="text/javascript">
        $(function() {

        });
    </script>
@endsection
