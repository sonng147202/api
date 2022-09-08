<p style="text-align:center">
    <img src="{{asset('/img/header_email.gif')}}" style="text-align: center;max-width: 800px;">
</p>
<table style="max-width:800px" rel="max-width:800px;" width="100%" cellspacing="0" cellpadding="0" border="0" align="center" height="100%">
    <tbody>
    <tr>
        <td colspan="2" style="background:#3498db;color:#fff;font-size:11px;padding:5px"><strong></strong></td>
    </tr>
    <tr style="background:#f5f5f5;font-size:13px" valign="top">
        <td style="padding:10px" colspan="2">
            <h1 style="text-align:center">YÊU CẦU RÚT TIỀN GHI NHẬN TRÊN APP MONFIN</h1>
            <p>Xin chào quý KHÁCH HÀNG : <strong>{{ $agency_name }}</strong>
            </p>
            <p>Cảm ơn quý đối tác đã đồng hành cùng MonFin Việt Nam.</p>
            <p>Chúng tôi vừa nhận được yêu cầu rút ví của quý đối tác với thông tin như sau.</p>
            <table style="border-collapse: collapse; border: 1px solid black;">
                <thead>
                <tr>
                    <th style="border: 1px solid black;">ID_Giao dịch</th>
                    <th style="border: 1px solid black;">Nội dung</th>
                    <th style="border: 1px solid black;">Số tiền</th>
                    <th style="border: 1px solid black;">Ghi chú</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td style="border: 1px solid black; text-align: center;">{{ $id }}</td>
                    <td style="border: 1px solid black; text-align: center;">{{ $content }}</td>
                    <td style="border: 1px solid black; text-align: center;">{{ number_format($money, 0).' VNĐ' }}</td>
                    <td style="border: 1px solid black; text-align: center;">{{ $note }}</td>
                </tr>
                </tbody>
            </table>
            <p>Sau khi kế toán kiểm tra và xác nhận số tiền này, bạn sẽ nhận được thông báo xác thực giao dịch thành công và tiền sẽ được chuyển khoản. Lưu ý số tiền này sẽ tự động ghi nhận vào giao dịch chờ thực hiện và số dư ví sẽ giảm số tiền tương ứng.</p>
            <p><a href="https://monfin.vn/huong-dan-yeu-cau-thanh-toan-tien-ghi-nhan-tu-vi-monfin.html">>> Hướng dẫn yêu cầu thanh toán tiền ghi nhận từ ví Monfin</a></p>
            <p>Nếu có thắc mắc cần hỗ trợ, xin vui lòng liên hệ thông tin như sau:</p>
            <p>PHÒNG DỊCH VỤ KHÁCH HÀNG</p>
            <p>Chat: <a href="m.me/monfinvn">m.me/monfinvn</a></p></p>
            <p>Email: <a href="mailto:contact@monfin.vn">contact@monfin.vn</a></p>
            <p>Chúc anh/chị thành công với MonFin Việt Nam.</p>
            <p>Trân trọng.</p>
        </td>
    </tr>
    </tbody>
</table>
<p style="text-align:center">
    <img src="{{asset('/img/footer_email.gif')}}" style="text-align: center;max-width: 800px;">
</p>