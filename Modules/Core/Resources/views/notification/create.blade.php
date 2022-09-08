<div class="box no-border">
    <form method="POST" action="{{route('core.notification.save')}}" id="form-input">
        {{csrf_field()}}
        <!-- /.box-header -->
        <div class="box-body">

            <div class="col-md-12">
                <!-- /.form-group -->
                <div class="form-group">
                    <label>Tiêu đề (*)</label>
                    <input name="title" type="text" value="" class="form-control" required>
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <label>Nội dung(*)</label>
                    <textarea name="message" class="form-control" rows="4" required></textarea>
                </div>
                <!-- /.form-group -->
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Khách hàng</label>
                    <select name="object_type" class="form-control">
                        <option value="0" >Khách hàng</option>
                        <option value="1" >Đại lý</option>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Lịch (*)</label>
                    <input type="text" id="schedule" class="form-control" name="schedule" value="" required>
                </div>
            </div>

            <!-- /.row -->
        </div>
        <div class="box-footer text-center">
            <a href="javascript:void(0)" class="btn btn-default" onclick="return dialog.close();">
                Huỷ bỏ
            </a>
            <input type="submit" value="Lưu" id="button-create"  class="btn btn-primary buttonsubmit">
        </div>
    </form>
</div>

<script type="text/javascript">
    $(function () {
        $("#form-input").validate({
            onfocusout: false,
            onkeyup: false,
            onclick: false,
            rules: {
                "title": {
                    required: true
                },
                "message": {
                    required: true
                },
                "schedule": {
                    required: true,
                    date: true,
                }

            },

        });

        $('#schedule').datetimepicker({
            sideBySide: true,
            minDate: new Date(),
            format: 'YYYY-MM-DD HH:mm',
        })
    });

    $('#form-input').on('submit',function(e) {
        e.preventDefault();
        if ($('#form-input').valid()){
            if (new Date($('#schedule').val()).getTime() > new Date().getTime()){
                // $('#form-input').parent().waitMe();
                btn_loading.loading('form-input');
                $.post('/admin/notification/save',$('#form-input').serialize(), function(result) {
                    btn_loading.hide('form-input');
                    dialog.close();
                    if (result.status === 1) {
                        alert('Thêm thông báo thành công')
                        window.location.reload();
                    } else {
                        alert(result.message);
                    }
                })

            } else {
                alert("Thời gian phải lớn hơn hiện tại")
            }
        }
        return false;
    });


</script>