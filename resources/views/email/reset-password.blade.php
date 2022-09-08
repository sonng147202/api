<table style="max-width:800px" rel="max-width:800px;" width="100%" cellspacing="0" cellpadding="0" border="0" align="center" height="100%">
    <tbody>
        <tr>
            <td colspan="2" style="background:#3498db;color:#fff;font-size:11px;padding:5px"><strong></strong></td>
        </tr>
        <tr style="background:#f5f5f5;font-size:13px" valign="top">
            <td style="padding:10px" colspan="2">
                <h1 style="text-align:center">CẤP LẠI MẬT KHẨU TRUY CẬP</h1>
                <p>Xin chào quý KHÁCH HÀNG: <strong>{{ $name }}</strong>
                </p>
                <p>
                    Chúng tôi đã nhận được yêu cầu thay đổi mật khẩu truy cập từ bạn,<br>
                    Mật khẩu mới của ban sẽ là:  {{$passwordOrigin}}<br>
                    Bạn hãy đăng nhập bằng tài khoản của mình và mua sắm để được hưởng toàn bộ các tiện ích cũng như ưu đãi dành cho thành viên.<br>
                    Vui lòng truy cập website <a href="{{ env('LINK_HOMEPAGE') }}">{{ env('LINK_HOMEPAGE') }}</a>
                </p>
                <p>Nếu cần hỗ trợ, xin vui lòng liên hệ thông tin như sau.</p>
                <p style="text-transform: uppercase;color:Red">BỘ PHẬN TUYỂN DỤNG | MEDICI INSURANCE</p>
                <p>Tel: {{env('HOTLINE')}}</p>
                <p>Email: {{env('EMAIL_SUPPORT')}}</p>
                <p>Website: {{env('APP_URL')}}</p>
            </td>
        </tr>
    </tbody>
</table>
