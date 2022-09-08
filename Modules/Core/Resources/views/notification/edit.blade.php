<div class="box no-border">
    <form method="POST" action="{{route('core.notification.update')}}" id="form-input">
        <!-- /.box-header -->
        <div class="box-body">

            <div class="col-md-12">
                <!-- /.form-group -->
                <div class="form-group">
                    <label>Tiêu đề (*)</label>
                    <input name="fullname" type="text" value="{{$noti->title}}" class="form-control" required>
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <label>Nội dung(*)</label>
                    <textarea name="content"  class="form-control" rows="4"
                              required>{{$noti->message}}</textarea>
                </div>
                <!-- /.form-group -->
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Khách hàng</label>
                    <select name="member_id" class="form-control">
                        <option value="0" {{$noti->member_type == 0 ? 'checked' : ''}}>Khách hàng</option>
                        <option value="1" {{$noti->member_type == 1 ? 'checked' : ''}}>Đại lý</option>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Lịch (*)</label>
                    <input type="text" id="schedule" class="form-control" name="schedule" value="{{$noti->schedule}}">
                </div>
            </div>

            <!-- /.row -->
        </div>
        <div class="box-footer text-center">
            <a href="javascript:void(0)" class="btn btn-default" onclick="return dialog.close();">
                Huỷ bỏ
            </a>
            <button type="button" id="button-edituser" onclick="return notificationHelper.save()" class="btn btn-primary buttonsubmit">
                Cập nhật
            </button>
        </div>
    </form>
</div>

<script type="text/javascript">
    $('#schedule').datetimepicker({
        format: 'DD/MM/YYYY'
    });
</script>