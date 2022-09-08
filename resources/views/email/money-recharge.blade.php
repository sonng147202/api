<p style="text-align:center">
    <img src="http://admin.eroscare.com/img/png-email.png" alt="{{env('APP_NAME')}}" style="height:60px" height="60" class="CToWUd">
</p>
<table style="max-width:800px" rel="max-width:800px;" width="100%" cellspacing="0" cellpadding="0" border="0" align="center" height="100%">
    <tbody>
        <tr>
            <td colspan="2" style="background:#3498db;color:#fff;font-size:11px;padding:5px"><strong></strong></td>
        </tr>
        <tr style="background:#f5f5f5;font-size:13px" valign="top">
            <td style="padding:10px" colspan="2">
                <h1 style="text-align:center">THÔNG BÁO NẠP TIỀN VÀO VÍ Mpoint</h1>
                <p>Xin chào quý KHÁCH HÀNG: <strong>{{$insuranceAgency['name']}}</strong>
                </p>
                <p>
                    Bạn vừa nap tiền vào  chính thức trở thành thành viên của Moncover Việt Nam với thông tin như sau.
                </p>
                <table style="border-collapse: collapse; border: 1px solid black; ">
                    <thead>
                        <tr>
                            <th style="border: 1px solid black;">ID_Giao dịch</th>
                            <th style="border: 1px solid black;">Nội dung</th>
                            <th style="border: 1px solid black;">Số tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="border: 1px solid black; text-align: center;">{{$id_data_exchange}}</td>
                            <td style="border: 1px solid black; text-align: center;">Nạp tiền vào ví</td>
                            <td style="border: 1px solid black; text-align: center;">{{$money_recharge}}</td>
                        </tr>
                    </tbody>
                </table>
                <p>Bạn hãy đăng nhập bằng tài khoản của mình và mua sắm theo lựa chọn phương thức thanh toán là ví hoàn thành giao dịch.</p>
                <p>
                    Vui lòng truy cập website https://partners.moncover.vn/ | hoặc trên ứng dụng Moncover ( iOs và Android)
                </p>
                <br>
                <p>Nếu cần hỗ trợ, xin vui lòng liên hệ thông tin như sau:</p>
                <br>
                <p>
                   PHÒNG DỊCH VỤ KHÁCH HÀNG <br/>
                </p>
                <p> Mobile:     
                    Email: contact@moncover.vn
                </p>
                <br>
                <br>
                <p>
                    Trân trọng. 
                </p>
            </td>
        </tr>
    </tbody>
</table>