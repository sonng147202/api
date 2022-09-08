<table cellpadding="0" cellspacing="0" border="0">
    <tr>
        <td width="30%" align="center" class="border-right-gray" >
            <img src="/img/logo-medici.png" width="90px">
        </td>
        <td width="2%">
        </td>
        <td width="68%" align="left">
            <span class="font-10 bold color-gray">Hotline:</span><span class="font-10 color-gray">1900 34 34</span><br>
            <span class="font-10 bold color-gray">Website:</span><span class="font-10 color-gray">www.baohiem.medici.vn</span><br>
            <span class="font-10 bold color-gray">Địa chỉ:</span><br>
            <span class="font-10 color-gray">HN: Tầng 9, Tòa 169 Nguyễn Ngọc Vũ, Quận Cầu Giấy, TP Hà Nội</span><br>
            <span class="font-10 color-gray">HCM: Số 192 Nam Kỳ Khởi Nghĩa, Phường 6, Quận 3, TP Hồ Chí Minh</span>

        </td>
    </tr>
    <tr>
        <td class="border-bottom-gray" width="100%"></td>
    </tr>
    <tr>
        <td width="32%"></td>
        <td width="68%" align="right" class="font-italic">
            <br>
            <br>
            Hà Nội, ngày {{ date('d') }} tháng {{ date('m') }} năm {{ date('Y') }}
        </td>
    </tr>
</table>
<br>
<br>
<br>
<table cellpadding="3" cellspacing="0" border="0">
    <tr>
        <td width="100%" align="left"><span class="left bold font-16">THƯ NGỎ</span></td>
    </tr>
</table>
<table cellpadding="5" cellspacing="0" border="0">
    <tr>
        <td><span>Kính chào Quý khách <b>{{ $customer->name }} - HĐBH số {{ $contract->contract_number}}</b></span></td>
    </tr>
    <tr>
        <td><span>Lời đầu tiên, Công ty cổ phần tập đoàn Medici (Medici) xin gửi đến Quý khách lời chào trân trọng, lời kính chúc sức khỏe và lời cảm ơn chân thành nhất! </span></td>
    </tr>
    <tr>
        <td><span>Medici là doanh nghiệp cung cấp dịch vụ tư vấn các sản phẩm bảo hiểm của Công ty TNHH Bảo hiểm nhân thọ FWD Việt Nam (FWD) theo Hợp đồng đại lý bảo hiểm ký kết ngày 18/07/2021.Với phương châm lấy khách hàng làm trọng tâm, Medici sẽ là người đồng hành tin cậy trong hành trình kết nối giữa Quý khách với FWD để bảo đảm kế hoạch tài chính cũng như thực hiện thành công những mục tiêu trong tương lai của Quý khách cùng Gia đình.</span></td>
    </tr>
    <tr>
        <td><span>Medici vui mừng thông báo Hợp đồng bảo hiểm nhân thọ của Quý khách được:</span></td>
    </tr>
    <tr>
        <td><span><b>Tư vấn và phục vụ bởi: </b></span></td>
    </tr>
    <tr>
        <td><span>Tư vấn tài chính FWD <b>{{ $agencyInfoFwd->name_agency_official }}</b>, mã số <b>{{ $agencyInfoFwd->code_agency_official }}</b>, số điện thoại: <b>{{ $agencyInfoFwd->phone }}</b></span></td>
    </tr>
    <tr>
        <td><span><b>Đồng phục vụ bởi:</b></span></td>
    </tr>
    <tr>
        <td><span>Thành viên Medici <b>{{ $insurance_agency->name }}</b>, mã số <b>{{ $insurance_agency->code_agency }}</b>, số điện thoại: <b>{{ $insurance_agency->phone }}</b></span></td>
    </tr>
    <tr>
        <td><span>Trong trường hợp Quý khách cần trao đổi thêm thông tin hoặc có yêu cầu liên quan đến nội dung, quyền lợi trong Hợp đồng bảo hiểm, Quý khách vui lòng liên hệ <b>{{ $insurance_agency->name }}</b> hoặc liên hệ đến số điện thoại tổng đài Chăm sóc khách hàng của FWD <b>1800969690</b>.</span></td>
    </tr>
    <tr>
        <td><span>Để có thêm thông tin về sản phẩm và dịch vụ của FWD, Quý khách vui lòng đăng nhập <b>www.fwd.com.vn</b>.</span></td>
    </tr>
    <tr>
        <td><span>Được phục vụ và mang lại sự hài lòng cho Quý khách là niềm vinh hạnh của Medici chúng tôi!</span></td>
    </tr>
</table>
<table cellpadding="3" cellspacing="0" border="0">
    <tr>
        <td align="center"  width="50%"></td>
        <td align="right"  width="50%">
            <span><img src="/img/signature-ceo2.png" width="240px" height="120px"></span>
        </td>
    </tr>
</table>
<style>
    .border-bottom-gray {
        border-bottom:0.5px solid #a8a8a8
    }
    .border-right-gray {
        border-right:0.5px solid #a8a8a8
    }
    .color-gray{
        color : #a8a8a8;
    }
    .font-10{
        font-size: 11px;
    }
   
    .font-11 {
        font-size: 11px;
    }
    .font-12 {
        font-size: 12.5px;
    }
    .font-16 {
        font-size: 16px;
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