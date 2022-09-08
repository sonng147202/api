@extends('layouts.admin_default')
@section('title', 'Quản lý thông báo')
@section('content')
    <section class="content-header">
        <h1>Quản lý thông báo</h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('admin_home') }}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Danh sách thông báo</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">

                <div class="box box-info">

                    <!-- /.box-header -->
                    <div class="box-body">
                        {!! Form::open(['method' => 'GET', 'route' => ['core.notification.index']]) !!}
                        <div class="col-xs-3">
                            <label>Tiêu đề</label>
                            <input type="text" name="title" class="form-control" value="{{!empty($params["title"]) ? $params["title"] : '' }}" >
                        </div>
                        <div class="col-xs-3">
                            <label>Đối tượng</label>
                            <select name="object_type" class="form-control select2 pull-right">

                                    <option value {{ empty($params) || $params["object_type"] == -1 ? 'selected' : ''}}>Tất cả</option>
                                    <option value="0" {{ !empty($params) && $params["object_type"] == 0 ? 'selected' : ''}}>Khách hàng</option>
                                    <option value="1" {{ !empty($params) && $params["object_type"] == 1 ? 'selected' : ''}}>Đại lý</option>

                            </select>
                        </div>
                        <div class="col-xs-3">
                            <label>Lịch</label>
                            <input type="text" id="schedule_filter" name="schedule" class="form-control" value="{{!empty($params["schedule"]) ? $params["schedule"] : ""}}">
                        </div>
                        <div class="col-xs-3">
                            <label style="visibility: hidden">TK</label>
                            <button type="submit" class="btn btn-primary btn-block"><span class="glyphicon glyphicon-search" aria-hidden="true"></span> Tìm kiếm</button>
                        </div>

                        {!! Form::close() !!}
                    </div><!-- /.box-body -->
                </div>
                <!-- /.box -->
            </div>

            <div class="col-md-12">
                @if ($errors->any())
                    <h4 style="color:red">{{$errors->first()}}</h4>
                @endif
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">Danh sách thông báo</h3>
                        <div class="pull-right">
                            <button type="button" id="button_create" onclick="notificationHelper.create()" class="btn btn-primary" >Thêm thông báo</button>
                        </div>
                    </div>

                    <!-- /.box-header -->
                    <div class="box-body">
                        <table id="example2" class="table table-bordered table-hover">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>Đối tượng</th>
                                <th>Tiêu đề</th>
                                <th style="width: 50%">Nội dung</th>
                                <th >Trạng thái</th>
                                <th class="text-center">Lịch</th>
                                {{--<th class="text-center">Xem chi tiết</th>--}}
                                <th class="text-center">Xóa</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($notifications as $notification)
                                <tr>
                                    <td>{{ $notification->id }}</td>
                                    <td>{{ $notification->object_type == 1 ? "Đại lý" : "Khách hàng" }}</td>
                                    <td>{{ $notification->title }}</td>
                                    <td>{{ $notification->message }}</td>
                                    <td>{{ ($notification->is_admin_push == 1) ? 'Đã gửi' : 'Chờ gửi' }}</td>
                                    <td>{{ $notification->schedule }}</td>
                                    {{-- <td class="text-center">
                                        <button type="button" id="button-edit" onclick="return notificationHelper.edit('{{$noti->id}}')" class="btn btn-warning btn-xs"><i class="fa fa-pencil"></i></button>
                                    </td> --}}
                                    <td class="text-center">
                                            {!! Form::open(['method' => 'DELETE', 'route' => ['core.notification.destroy', $notification->id]]) !!}
                                            <a href="#" class="btn btn-warning"  onclick="if(confirm('Bạn có chắc muốn xóa bản ghi này không?')) $(this).closest('form').submit();"><i class="fa fa-trash"></i> Xoá</a>
                                            {!! Form::close() !!}
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                            <tfoot>
                            </tfoot>
                        </table>
                        {{ $notifications->links() }}
                    </div>
                    <!-- /.box-body -->
                    <div class="overlay hide">
                        <i class="fa fa-refresh fa-spin"></i>
                    </div>
                </div>
                <!-- /.box -->
            </div>
            <!-- /.col -->
        </div>
    </section>

@endsection
@section('scripts')
    <script type="text/javascript">
        $(function () {
            $('link[href*="datepicker3.css"]').prop('disabled', true);

            $('#schedule_filter').datetimepicker({
                format: 'YYYY-MM-DD'
            });
        });


        var notificationHelper = (function () {
            return {
                create: function() {
                    btn_loading.loading('button_create');
                    $.get('/admin/notification/create', function(result) {
                        btn_loading.hide('button_create');
                        dialog.show('Thêm nội dung thông báo', result);

                    })
                },

            }
        })();
    </script>
@endsection
