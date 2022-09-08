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
            <h1 style="text-align:center">THÔNG BÁO NẠP TIỀN VÀO VÍ MPOINT</h1>
            <p>Xin chào quý KHÁCH HÀNG : <strong>{{ $agency['name'] }}</strong>
            </p>
            <p>
                Bạn vừa nạp tiền vào ví điểm thưởng Mpoint với thông tin như sau.
            </p>
            <table style="border-collapse: collapse; border: 1px solid black;">
                <thead>
                <tr>
                    <th style="border: 1px solid black;">ID_Giao dịch</th>
                    <th style="border: 1px solid black;">Nội dung</th>
                    <th style="border: 1px solid black;">Số tiền</th>
                    <th style="border: 1px solid black;">Hình thức</th>
                    <th style="border: 1px solid black;">Ghi chú</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td style="border: 1px solid black; text-align: center;">{{ $id_data_wallet }}</td>
                    <td style="border: 1px solid black; text-align: center;">Nạp tiền vào ví</td>
                    <td style="border: 1px solid black; text-align: center;">{{ $exchange['value'] }}</td>
                        <?php 
                        if(!empty($note)){
                            echo '<td style="border: 1px solid black; text-align: center;">'.$note.'</td>';
                        }else {
                            echo '<td style="border: 1px solid black; text-align: center;"></td>';
                        }
                    ?>
                    <?php 
                        if(empty($recharge_content)){
                            echo '<td style="border: 1px solid black; text-align: center;">'.$recharge_content.'</td>';
                        }else {
                            echo '<td style="border: 1px solid black; text-align: center;"></td>';
                        }
                    ?>
                    
                </tr>
                </tbody>
            </table>
            <p>Chúng tôi sẽ nhanh chóng xác nhận thông tin cho bạn</p>
            <p>Nếu cần hỗ trợ, xin vui lòng liên hệ thông tin như sau:</p>
            <p>PHÒNG DỊCH VỤ KHÁCH HÀNG</p>
            <p>Chat: <a href="m.me/monfinvn">m.me/monfinvn</a></p></p>
            <p>Email: <a href="mailto:contact@monfin.vn">contact@monfin.vn</a></p>
            <p>Trân trọng.</p>
        </td>
    </tr>
    </tbody>
</table>
<p style="text-align:center">
    <img src="{{asset('/img/footer_email.gif')}}" style="text-align: center;max-width: 800px;">
</p>