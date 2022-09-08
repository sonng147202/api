<table cellpadding="0" cellspacing="0" border="0">
    <tr>
        <td width="20%" align="left">
            <img src="/img/logo-medici.png" width="80px">
        </td>
        <td width="80%" align="center">
            <span class="center bold font-20">Thư Xác Nhận</span><br><span class="center font-italic">(V/v: Cung cấp thông tin ứng viên và chữ ký mẫu)</span>
        </td>
    </tr>
</table>
<br>
<br>
<table cellpadding="0" cellspacing="0" border="0">
    <tr>
        <td width="100%" align="center">
            <span class="center bold font-12">Kính gửi: CÔNG TY CỔ PHẦN TẬP ĐOÀN MEDICI </span>
        </td>
    </tr>
</table>
<br>
<br>
<table cellpadding="1" cellspacing="0" border="0">
    <tr >
        <td width="60%">Tôi là (họ tên đầy đủ): {{ !empty($data['name']) ? $data['name'] :'' }}</td>
        <td width="40%">Sinh ngày: {{ !empty($data['birthday']) ? date('d/m/Y',strtotime($data['birthday'])) :'' }}</td>
    </tr>
    <tr>
        <td width="60%">Số CCCD/CMT: {{ !empty($data['id_card_number']) ? $data['id_card_number'] :'' }}</td>
        <td width="40%">Ngày cấp: {{ !empty($data['date_card_number']) ? date('d/m/Y',strtotime($data['date_card_number'])) :'' }}</td>
    </tr>
    <tr>
        <td width="100%">Nơi cấp: {{ !empty($data['place_card_number']) ? $data['place_card_number'] :'' }}</td>
    </tr>
    <tr>
        <td width="50%">Điện thoại: {{ !empty($data['phone']) ? $data['phone'] :'' }}</td>
        <td width="50%">Địa chỉ Email: {{ !empty($email['email']) ? $email['email'] :'' }}</td>
    </tr>
</table>
<br>
<br>
<table cellpadding="0" cellspacing="0" border="0">
    <tr class="bold font-italic">
        <td width="100%" align="left">Bằng việc ký tên dưới đây, tôi đồng ý và xác nhận rằng:</td>
    </tr>
    <tr >
        <td width="100%" align="center">
            <span class="left font-11">1. Dưới sự hướng dẫn và hỗ trợ của nhà tuyển dụng, tôi đã tự mình kê khai và cung cấp tất cả các thông tin, hồ sơ theo yêu cầu của Công ty MEDICI. Thông tin đã khai báo thể hiện tại Bộ hồ sơ tuyển dụng bao gồm: Đơn đăng ký thành viên, Bản chụp bản chính giấy tờ tùy thân (CCCD/CMT), Bằng cấp liên quan và các giấy tờ khác được nộp kèm.</span>
        </td>
    </tr>
    <br>
    <tr >
        <td width="100%" align="center">
            <span class="left font-11">2. Tôi cam kết các thông tin được tôi kê khai và cung cấp cho Công ty MEDICI trên Bộ hồ sơ tuyển
                                    dụng là hoàn toàn đầy đủ, chính xác, trung thực. Đồng thời, bằng văn bản này, tôi chấp nhận để
                                    công ty MEDICI sử dụng các thông tin đó dùng làm căn cứ cho việc kiểm tra, thẩm định, lưu trữ, đào
                                    tạo và giao kết Hợp đồng hợp tác tư vấn và phân phối sản phẩm sau này với tôi.
            </span>
        </td>
    </tr>
    <br>
    <tr >
        <td width="100%" align="center">
            <span class="left font-11 ">3. Tôi xác nhận địa chỉ thư điện tử (email) trên được Tôi cung cấp cho Công ty MEDICI là email cá nhân
                                            duy nhất của tôi dùng để giao dịch với Công ty MEDICI và chỉ tôi có quyền sử dụng, truy cập địa chỉ
                                            email này. Tôi đăng ký và đồng ý nhận mọi thông tin, trao đổi từ Công ty MEDICI gửi đến tôi thông
                                            qua email trên và xác nhận mọi thông tin gửi tới địa chỉ email đó sẽ được coi là đã được gửi thành
                                            công cho tôi. Đồng thời, mọi thông tin, trao đổi gửi đến Công ty MEDICI từ địa chỉ email đó được xem
                                            là do tôi gửi, thể hiện đúng ý chí của tôi và do đó Công ty MEDICI có quyền căn cứ theo thông tin trong
                                            email để xử lý, giải quyết theo quy định pháp luật. Tôi hiểu và đồng ý rằng các giao dịch điện tử giữa
                                            tôi và Công ty MEDICI sẽ được xác nhận qua địa chỉ email trên. 
            </span>
        </td>
    </tr>
    <br>
    <tr >
        <td width="100%" align="center">
            <span class="left font-11 ">4. Tôi đồng ý rằng Bộ hồ sơ tuyển dụng và Thư xác nhận này sẽ có hiệu lực giữa tôi và Công ty MEDICI
                                            ngay khi được Công ty MEDICI chấp thuận và không cần thêm xác nhận, chấp thuận hay chữ ký nào
                                            khác của tôi.
            </span>
        </td>
    </tr>
    <br>
    <tr >
        <td width="100%" align="center">
            <span class="left font-11">5. Tôi đăng ký sử dụng chữ ký dưới đây làm <span class="bold">chữ ký mẫu</span> của mình và đồng ý cho Công ty MEDICI sử
                                                dụng chữ ký mẫu này cho việc xác minh chữ ký của tôi trong tất cả các văn bản, tài liệu khác có
                                                liên quan đến tôi. Tôi cam kết hoàn toàn chịu trách nhiệm trước pháp luật về tính chính xác, trung
                                                thực của các nội dung nêu trên.
            </span>
        </td>
    </tr>
</table>
<br>
<br>
<table cellpadding="5" cellspacing="0" border="0">
    <tr width="100%">
        <td width="60%"></td>
        <td width="40%" align="center">
            <span class="center bold font-italic ">Ngày: {{ date('d/m/Y') }}</span>
            <br>
            <span class="center bold" >Ứng viên ký và ghi rõ họ tên</span>
            <br>
            <span class="center"><img src="{{ !empty($signature) ? $signature : '' }}" class="signature"></span>
        </td>
    </tr>
</table>
<style>
    .signature {
        width: 150px;
        height: 105px;
    }
    .clear-p {
        margin-bottom: 5px !important;
        margin-top: 0px !important;
    }
    .font-11 {
        font-size: 11px;
    }
    .font-12 {
        font-size: 12px;
    }
    .font-16 {
        font-size: 16px;
    }
    .font-20 {
        font-size: 20px;
    }
    .bold {
        font-weight: bold;
    }

    .left {
        text-align: left;
    }

    .right {
        text-align: right;
    }

    .center {
        text-align: center;
    }
    .left {
        text-align: left !important;
    }
    .border-td-bottom {
        border-bottom: 1px solid #333;
    }
    .font-italic {
        font-style: italic;
    }
</style>