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
            <h1 style="text-align:center">Yêu cầu trợ giúp</h1>
            <p> ********************************************************</p>
            <p>
                ĐÂY LÀ EMAIL TỰ ĐỘNG ĐƯỢC GỬI TỪ HỆ THỐNG CRM MonFin <br>
                KHÁCH HÀNG VUI LÒNG KHÔNG TRẢ LỜI EMAIL NÀY
            </p>
            <p> ********************************************************</p>
            <table style="border-collapse: collapse; border: 1px solid black;">
                <thead>
                <tr>
                    <th style="border: 1px solid black;">Thông tin</th>
                    <th style="border: 1px solid black;">Thời gian</th>
                    <th style="border: 1px solid black;">Từ</th>
                    <th style="border: 1px solid black;">Nội dung</th>
                </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="border: 1px solid black; text-align: center;">Gửi yêu cầu trợ giúp</td>
                        <td style="border: 1px solid black; text-align: center;">{{ date('d/m/Y H:i', strtotime($date)) }}</td>
                        <td style="border: 1px solid black; text-align: center;">{{ $customer_name }}</td>
                        <td style="border: 1px solid black; text-align: center;">{{ $content }}</td>
                    </tr>
                </tbody>
            </table>
        </td>
    </tr>
    </tbody>
</table>
<p style="text-align:center">
    <img src="{{asset('/img/footer_email.gif')}}" style="text-align: center;max-width: 800px;">
</p>