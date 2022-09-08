@extends('layouts.admin_default')

@section('title','Gửi email')

@section('content')
    <section class="content-header">
        <h1>Gửi email Khách hàng</h1>
        <ol class="breadcrumb">
            <li><a href="http://admin-eroscare.test/admin"><i class="fa fa-dashboard"></i> Trang chủ</a></li>
            <li class="active"><a href="#">Gửi email</a></li>
        </ol>
    </section>

    {!! Form::open(['class'=>'validate', 'id' => 'form-input', 'method'=>'POST', 'route'=> ['core.sendEmail.index']]) !!}
    <section class="content">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                    <div class="box-body">
                        <div class="form-group">
                            <label for="exampleInputEmail1">Khách hàng</label>
                            <select id="customer" name="customer" class="form-control select2" required style="width: 100%;">
                                <option value selected="selected">--Chọn khách hàng--</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Email to: </label>
                            <input class="form-control" name="mailTo" readonly required value="{{old('mailTo')}}">
                        </div>
                        <div class="form-group">
                            <label>Cc: </label>
                            <select id="Cc-email" class="form-control select2" multiple="multiple" name="ccEmail[]" style="width: 100%;">
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Tiêu đề</label>
                            <input name="title" class="form-control" type="text" placeholder="Nhập tiêu đề vào đây" required>
                        </div>
                        <div class="form-group">
                            <label>Nội dung</label>
                            <textarea row="5" class="form-control" id="post-data" name="content" type="text">
                                <?php $user = \Illuminate\Support\Facades\Auth::user() ?>
                                <p></p>
                                <hr />
                                <p><strong>Họ và tên: {{!empty($user->fullname) ? $user->fullname : $user->username }}</strong></p>
                                <p><strong>BẢO HIỂM TRỰC TUYẾN</strong>, eBaohiem</p>
                                <p>Cell : <strong>096675 8898</strong></p>
                                <p>Hotline: 1900 633 613</p>
                                <p>Điện thoại: {{$user->phone}}
                                    <br />
                                    Email: <a href="mailto:kienlh@ebaohiem.com">kienlh@ebaohiem.com</a>
                                    <br />
                                    Website: <a href="http://www.ebaohiem.com/">www.eBaohiem.com</a>
                                </p>
                                <p><strong>Hà Nội: </strong>Tầng 4, Tòa nhà 110 phố Nguyễn Ngọc Nại, Q.Thanh Xuân, TP Hà Nội</p>
                                <p><strong>Hồ Chí Minh:</strong> Lầu 2, Toà nhà MT, số 47 Hồ Bá Kiện, Phường 15, Quận 10, TP HCM</p>
                            </textarea>
                        </div>
                    </div>
                    <div class="box-footer">
                        <button type="submit" class="btn btn-primary center-block">Gửi email</button>
                    </div>
                </div>
            </div>
        </div>

    </section>
{!! Form::close() !!}


@endsection

@section('scripts')
    <script type="text/javascript" src="{{asset('/modules/insurance/js/company.js')}}"></script>
    <script src="{{asset('admin-lte/plugins/tinymce/tinymce.min.js')}}"></script>
    <script type="text/javascript" src="{{asset('js/tinymce.js')}}"></script>
    <script type="text/javascript">
        $(function(){
            $('#Cc-email').select2({
                tags: true,
                tokenSeparators: [','],
                createTag: function (params) {
                    var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
                    // Don't offset to create a tag if there is no @ symbol
                    if (!regex.test(params.term)) {
                        // Return null to disable tag creation
                        return null;
                    }

                    return {
                        id: params.term,
                        text: params.term
                    }
                }
            });


            $('#customer').on('select2:select', function (e) {
                var data = e.params.data;
                $('input[name="mailTo"]').val(data.email);
            });

            $('#form-input').on('submit',function(e){
                e.preventDefault();
                if ($(this).valid()) {
                    btn_loading.loading('form-input');
                    $.post('{{route('core.sendEmail.index')}}', $(this).serialize(), function(response) {
                        btn_loading.hide('form-input');
                        if (typeof response.result !== undefined) {
                            alert(response.message);
                        }
                    })
                }
                return false;
            })

            select2Ajax('#customer', 'Tìm kiếm khách hàng', '/customer/search');
        })
    </script>
    @endsection