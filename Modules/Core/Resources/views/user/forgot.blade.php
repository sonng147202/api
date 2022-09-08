@extends('layouts.admin_login')
@section('title', 'Login')
@section('content')
<div class="login-box">
    <div class="login-logo">
        <a href="javascript:void(0)"><b>{{env('APP_NAME')}}</b></a>
    </div>
    <!-- /.login-logo -->
    <div class="login-box-body">
        <p class="login-box-msg">Để đặt lại mật khẩu của bạn, hãy nhập địa chỉ email bạn sử dụng để đăng nhập</p>
        
        {{ Form::open(array('url' => '/admin/forgot-password', 'id'=>'form-login1', 'class'=>'form-login')) }}
            <input type="hidden" id="base_url1" value="{{URL::to('/')}}" />
            {{ csrf_field() }}
            <div class="form-group has-feedback">
                <input required type="email" class="form-control dynamic_email" name="email" placeholder="Vui lòng nhập tên Email"
                       data-msg-required="Vui lòng nhập thông tin" data-msg-email="Email phải đúng định dạng" id="email1">
                <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
            </div>
        @if (session('msg_error'))
            <div class="alert alert-error">
                <p>{{ session('msg_error') }}</p>
            </div>
        @endif
            <button class="btn btn-primary btn-block btn-flat" type="submit">Nhận liên kết đặt lại</button>
            </div>
        {{ Form::close() }}
    </div>
    <!-- /.login-box-body -->
</div>
@endsection