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
            <h1 style="text-align:center">THÔNG BÁO TIỀN THƯỞNG/PHẠT</h1>
            <p>Xin chào quý đối tác : <strong>{{ $agency['name'] }}</strong>
            </p>
            <p>
                Bạn vừa nhận được một giao dịch thưởng/ phạt với thông tin như sau.
            </p>
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
                    <td style="border: 1px solid black; text-align: center;">{{ $id_data_wallet }}</td>
                    <td style="border: 1px solid black; text-align: center;">{{ $note }}</td>
                    <td style="border: 1px solid black; text-align: center;">{{ number_format($exchange['value']).' VNĐ' }}</td>
                    <td style="border: 1px solid black; text-align: center;">{{ $recharge_content }}</td>
                </tr>
                </tbody>
            </table>
            <p>Vui lòng truy cập website https://doitac.monfin.vn/ | hoặc trên ứng dụng MonFin ( iOs và Android) để theo dõi các thông tin giao dịch ví.</p>
            <p>Nếu cần hỗ trợ, xin vui lòng liên hệ thông tin như sau:</p>
            <br>
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