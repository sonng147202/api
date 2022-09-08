@extends('layouts.admin_login')
@section('title', 'Login')
@section('content')
<div class="login-box">

    <div class="login-box-body">
        <div class="text-center">
            <img  src="/img/logo-medici.png" width="120px" style="margin-bottom: 15px;">
        </div>
        @if (session('msg_error'))
            <div class="alert alert-error">
                  <p>{{ session('msg_error') }}</p>
            </div>
        @endif
        @if (session('msg_success'))
            <div class="alert alert-error">
                  <p>{{ session('msg_success') }}</p>
            </div>
        @endif
        <form method="post" action="{{ route('login') }}">
            <div class="form-group has-feedback">
                <input type="text" name="username" class="form-control" placeholder="Tài khoản">
                <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
            </div>
            <div class="form-group has-feedback">
                <input type="password" name="password" class="form-control" placeholder="Mật khẩu">
                <span class="glyphicon glyphicon-lock form-control-feedback"></span>
            </div>
            <a href="{{route('forgot')}}">Quên mật khẩu</a>
            <div class="row">
                <div class="col-xs-8">
                    <div class="checkbox icheck">
                        <label>
                            <input type="checkbox" name="remember_me"> Ghi nhớ đăng nhập
                        </label>
                    </div>
                </div>
                <!-- /.col -->
                <div class="col-xs-4">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <button type="submit" class="btn btn-warning btn-block btn-flat">{{ trans('core::general.login') }}</button>
                </div>
                <!-- /.col -->
            </div>
        </form>

        {{-- <a href="{{ URL::to('/admin/forgot') }}">@lang('core::general.forgot_password')</a><br> --}}
    </div>
</div>
@endsection
