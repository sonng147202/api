<table cellpadding="0" cellspacing="0" border="0">
    <tr>
        <td width="30%" align="center" class="border-right-gray" >
            <img src="/img/logo-pdf.png" width="90px">
        </td>
        <td width="2%">
        </td>
        <td width="68%" align="left">
            <span class="color-gray">CÔNG TY CỔ PHẦN TƯ VẤN VÀ PHÂN PHỐI BẢO HIỂM MEDICI</span><br>
            <span class="font-10 color-gray">Website: www.baohiem.medici.vn</span><br>
            <span class="font-10 color-gray">Email: contact@fadgroup.vn</span><br>
            <span class="font-10 color-gray">Tel: 1900 34 34</span>
        </td>
    </tr>
    <tr>
        <td class="border-bottom-gray" width="100%"></td>
    </tr>
    <tr>
        <td width="32%"></td>
        <td width="68%" align="center" class="font-italic">
            <br>
            <br>
            Hà Nội, ngày {{ substr($date_approve,0,2) }} tháng {{ substr($date_approve,3,2) }} năm {{ substr($date_approve,6,4) }}
        </td>
    </tr>
</table>
<br>
<br>
<br>
<table cellpadding="0" cellspacing="0" border="0">
    <tr>
        <td width="100%" align="center">
            <span class="center bold font-16">THƯ MỜI HỢP TÁC KINH DOANH</span>
        </td>
    </tr>
</table>
<br>
<br>
<br>
<table cellpadding="12" cellspacing="0" border="0">
    <tr>
        <td>
            <span>
                Kính gửi: {{ $sex }} {{ $name }} – CMND/CCCD: {{ $id_card_number }}
            </span>
        </td>
    </tr>
    <tr>
        <td>
            <span>
                Công ty Cổ phần Tư vấn và Phân phối Bảo hiểm MEDICI (gọi tắt là Công ty MEDICI) vui mừng thông báo {{ $sex }} đã xuất sắc vượt qua các vòng phỏng vấn của Công ty MEDICI. Chúng tôi đã rất ấn tượng với kinh nghiệm chuyên môn cùng những kỹ năng và uy tín của {{ $sex }}.
            </span>
        </td>
    </tr>
    <tr>
        <td>
            <span>
                Bằng thư này Công ty MEDICI chính thức xác nhận và gửi tới {{ $sex }} Thư mời hợp tác tư vấn, phân phối sản phẩm với những điều khoản, điều kiện cơ bản đã được thống nhất như sau:
            </span>
        </td>
    </tr>
    <tr>
        <td width="30%" align="left"><span>Chức danh bổ nhiệm:</span></td>
        <td  width="70%" align="left"><span>{{ $level }}</span></td>
    </tr>
    <tr>
        <td width="30%" align="left"><span>Báo cáo trực tiếp cho:</span></td>
        <td  width="70%" align="left"><span>{{ $parent }}</span></td>
    </tr>
    <tr>
        <td width="30%" align="left"><span>Phạm vi tuyển dụng:</span></td>
        <td  width="70%" align="left"><span>Được quyền tuyển dụng không giới hạn số lượng TD/RD/SD/AD/SM/FC/RE trên khắp các tỉnh thành toàn quốc.</span></td>
    </tr>
    <tr>
        <td width="30%" align="left"><span>Loại hợp đồng:</span></td>
        <td  width="70%" align="left"><span>Hợp đồng hợp tác tư vấn và phân phối sản phẩm, với thời hạn một năm và sẽ tự động gia hạn mỗi năm.</span></td>
    </tr>
    <tr>
        <td width="30%" align="left"><span>Ngày bắt đầu làm việc</span></td>
        <td  width="70%" align="left"><span>{{ $sex }} có thể bố trí bắt đầu tuyển dụng xây hệ thống kinh doanh ngay khi nhận được thư mời hợp tác này.</span></td>
    </tr>
    <tr>
        <td width="30%" align="left"><span>Thu nhập hàng tháng:</span></td>
        <td  width="70%" align="left"><span>Áp dụng theo cơ Chế thù lao dành cho {{ $level_2 }} quy định tại Phụ lục gửi kèm dưới đây.</span></td>
    </tr>
    <tr>
        <td width="30%" align="left"><span>Thu nhập thực lĩnh:</span></td>
        <td  width="70%" align="left"><span>Thu nhập trên kết quả kinh doanh trừ các khoản thuế thu nhập cá nhân theo quy định của Pháp luật.</span></td>
    </tr>
</table>
{{-- page 2 --}}
<table cellpadding="0" cellspacing="0" border="0">
    <tr>
        <td width="17%" align="center" >
            <img src="/img/logo-pdf.png" width="70px">
        </td>
        <td width="83%" align="left"></td>
    </tr>
</table>
<br>
<br>
<table cellpadding="5" cellspacing="0" border="0">
    <tr>
        <td class="font-12 bold" width="100%" align="left">ĐIỀU KIỆN ĐỂ ĐƯỢC BỔ NHIỆM CHÍNH THỨC</td>
    </tr>
    <tr>
        <td align="left">
            Trong 3 tháng đầu tiên {{ $sex }} cần hoàn thành các chỉ tiêu tối thiểu về tuyển dụng và bổ nhiệm hệ thống kinh doanh như sau:
        </td>
    </tr>
</table>
<table cellpadding="2" cellspacing="0" border="1">
    <tr>
        <td class="bold" width="50%" align="center"><span>CẤP QUẢN LÝ TRONG HỆ THỐNG</span></td>
        <td class="bold" width="50%" align="center" ><span>SỐ LƯỢNG NHÂN SỰ ĐƯỢC BỔ NHIỆM</span><br><span>(3 tháng đầu tiên)</span></td>
    </tr>
    <tr>
        <td width="50%" align="center">Giám đốc điều hành – ED</td>
        <td width="50%" align="center">Ưu tiên thăng tiến nội bộ</td>
    </tr>
    <tr>
        <td width="50%" align="center">Giám đốc miền – TD</td>
        <td width="50%" align="center">2 RD hoặc 4 SD</td>
    </tr>
    <tr>
        <td width="50%" align="center">Giám đốc vùng – RD</td>
        <td width="50%" align="center">2 SD hoặc 4 AD hoặc 8 SM</td>
    </tr>
    <tr>
        <td width="50%" align="center">Giám đốc cấp cao – SD</td>
        <td width="50%" align="center">2 AD hoặc 4 SM</td>
    </tr>
    <tr>
        <td width="50%" align="center">Giám đốc khu vực – AD</td>
        <td width="50%" align="center">2 SM</td>
    </tr>
    <tr>
        <td width="50%" align="center">Trưởng phòng – SM</td>
        <td width="50%" align="center">5 FC</td>
    </tr>
</table>
<table cellpadding="2" cellspacing="0" border="0">
    <tr>
        <td width="100%" align="left">
            <span>
                Ghi chú: Chi tiết vui lòng tham khảo Thông báo nội bộ số 01.21/MEDICI đính kèm.
            </span>
        </td>
    </tr>
    <tr>
        <td width="100%" align="left">
            <span class="bold">ĐIỀU KHOẢN CHUNG:</span>
        </td>
        
    </tr>
    <tr>
        <td width="100%" align="left">
            <span>
                1. {{ $sex }} đồng ý rằng ngay khi ký kết hợp đồng hợp tác tư vấn và phân phối sản phẩm với Công ty, {{ $sex }} sẽ tuân thủ những quy định hiện hành của Công ty được thông báo trên cổng thông tin dành cho thành viên (Web/Portal) và tuân thủ quy định của Pháp luật.
            </span>
        </td>
    </tr>
    <tr>
        <td width="100%" align="left">
            <span>
                2. Thư này được gửi theo thư điện tử do {{ $sex }} cung cấp trên Đơn đăng ký thành viên MEDICI. Nếu {{ $sex }} còn có bất kỳ câu hỏi nào về nội dung của thư mời vui lòng liên hệ ngay với Quản lý trực tiếp của mình hoặc gửi thư điện tử theo địa chỉ: hethong@medici.vn
            </span>
        </td>
    </tr>
    <tr>
        <td width="100%" align="left">
            <span>
                3. Thư mời ký hợp đồng hợp tác này chỉ có giá trị đến hết ngày {{ $expire }}. Trong trường hợp {{ $sex }} đã có mã thành viên MEDICI, thư mời này có giá trị kể từ ngày được cấp mã.<br><br>Chúng tôi rất chào đón {{ $sex }} sớm đứng trong đội ngũ kinh doanh của Công ty MEDICI và rất tin tưởng sự hợp tác này sẽ thành công tốt đẹp, lâu dài.<br><br>Chúc {{ $sex }} và gia đình bình an, hạnh phúc và thịnh vượng !
            </span>
        </td>
    </tr>
</table>
<table cellpadding="5" cellspacing="0" border="0">
    <tr>
        <td align="center"  width="50%">
            <span><img src="/img/signature-ceo2.png" width="264px" height="150px"></span>
        </td>
    </tr>
</table>
<br>
<br>
{{-- page 3 --}}
<table cellpadding="0" cellspacing="0" border="0">
    <tr>
        <td width="17%" align="center" >
            <img src="/img/logo-pdf.png" width="70px">
        </td>
        <td width="83%" align="left"></td>
    </tr>
</table>
<table cellpadding="3" cellspacing="0" border="0">
    <tr>
        <td width="100%" align="center" ><span class="font-12 bold">PHỤ LỤC</span></td>
    </tr>
    <tr>
        <td width="100%" align="center" ><span class="font-12 bold">CƠ CHẾ THÙ LAO DÀNH CHO GIÁM ĐỐC ĐIỀU HÀNH</span></td>
    </tr>
    <tr>
        <td width="100%" align="center" ><span>(Ban hành kèm theo Thông báo nội bộ số 01.21/MEDICI)</span></td>
    </tr>
</table>
<br>
<br>
<table cellpadding="2" cellspacing="0" border="0">
    <tr>
        <td width="100%" align="left" >
            <span>
                Trích theo Thông báo nội bộ số 01.21/MEDICI, gồm các Thu nhập sau:
            </span>
        </td>
    </tr>
    <tr>
        <td width="100%" align="left" >
            <span>
                <b>3.8. Thu nhập dành cho Giám đốc điều hành (ED)</b> gồm những khoản sau:
            </span>
        </td>
    </tr>
    <tr>
        <td width="100%" align="left" >
            <span>
                <b>3.8.1. Thu nhập 1: </b>Từ bán cá nhân là <b>76% P.FYP</b> cộng với hoa hồng tái tục từ phí bảo hiểm năm thứ hai của những hợp đồng cá nhân đã bán hoặc đang phục vụ. (Tham khảo khoản Thu nhập 3 của Tư vấn viên - FC).
            </span>
        </td>
    </tr>
    <tr>
        <td width="100%" align="left" >
            <span>
                <b>3.8.2. Thu nhập 2:</b> Từ quản lý trực tiếp toàn hệ thống của TD (Cấp 7)
            </span>
        </td>
    </tr>
</table>
<br>
<br>
<table cellpadding="2" cellspacing="0" border="1">
    <tr>
        <td width="15%" align="center" >Cấp</td>
        <td width="15%" align="center" >Viết tắt</td>
        <td width="25%" align="center" >Thu nhập hàng tháng</td>
        <td width="25%" align="center" >Chỉ tiêu P.FYP Cấp 7</td>
        <td width="20%" align="center" >Thưởng</td>
    </tr>
    <tr>
        <td width="15%" align="center" >8</td>
        <td width="15%" align="center" >ED</td>
        <td width="25%" align="center" >120,000,000 đồng</td>
        <td width="25%" align="center" >4.000.000.000 đ</td>
        <td width="20%" align="center" >4% x phần P.FYP vượt chỉ tiêu</td>
    </tr>
</table>
<table cellpadding="1" cellspacing="0" border="0">
    <tr>
        <td width="100%" align="left" >
            <span>
                + Nếu toàn bộ hệ thống của TD đạt 4.000.000.000 P.FYP thì thu nhập của ED là 120 triệu VNĐ.
            </span>
        </td>
    </tr>
    <tr>
        <td width="100%" align="left" >
            <span>
                + Nếu đạt trên 4.000.000.000 P.FYP thì thu nhập của ED = 120 triệu + thưởng (Phần P.FYP vượt chỉ tiêu x 4%)
            </span>
        </td>
    </tr>
    <tr>
        <td width="100%" align="left" >
            <span>
                + Nếu đạt dưới 4.000.000.000 P.FYP thì thu nhập của ED = thực đạt P.FYP x 3%
            </span>
        </td>
    </tr>
</table>
<table cellpadding="5" cellspacing="0" border="0">
    <tr>
        <td width="100%" align="left" >
            <span>
                <b>3.8.3. Thu nhập 3:</b> Từ quản lý trực tiếp toàn hệ thống của RD (Cấp 6)
            </span>
        </td>
    </tr>
</table>
<table cellpadding="5" cellspacing="0" border="1">
    <tr>
        <td width="100%" align="center" >
            <span>
                Thu nhập = 8% x Tổng P.FYP toàn hệ thống RD trực tiếp báo cáo ED
            </span>
        </td>
    </tr>
</table>
<table cellpadding="4" cellspacing="0" border="0">
    <tr>
        <td width="100%" align="left" >
            <span>
                <b>3.8.4. Thu nhập 4:</b> Từ quản lý trực tiếp toàn hệ thống của SD (Cấp 5)
            </span>
        </td>
    </tr>
</table>

<table cellpadding="5" cellspacing="0" border="1">
    <tr>
        <td width="100%" align="center" >
            <span>
                Thu nhập = 16% x Tổng P.FYP toàn hệ thống SD trực tiếp báo cáo ED
            </span>
        </td>
    </tr>
</table>
<table cellpadding="5" cellspacing="0" border="0">
    <tr>
        <td width="100%" align="left" >
            <span>
                <b>3.8.5. Thu nhập 5:</b> Từ quản lý trực tiếp toàn hệ thống của AD (Cấp 4)
            </span>
        </td>
    </tr>
</table>

<table cellpadding="5" cellspacing="0" border="1">
    <tr>
        <td width="100%" align="center" >
            <span>
                Thu nhập = 26% x Tổng P.FYP toàn hệ thống AD trực tiếp báo cáo ED
            </span>
        </td>
    </tr>
</table>
<table cellpadding="5" cellspacing="0" border="0">
    <tr>
        <td width="100%" align="left" >
            <span>
                <b>3.8.6. Thu nhập 6:</b> Từ quản lý trực tiếp toàn hệ thống của SM (Cấp 3)
            </span>
        </td>
    </tr>
</table>

<table cellpadding="5" cellspacing="0" border="1">
    <tr>
        <td width="100%" align="center" >
            <span>
                Thu nhập = 36% x Tổng P.FYP toàn hệ thống SM trực tiếp báo cáo ED
            </span>
        </td>
    </tr>
</table>
<table cellpadding="5" cellspacing="0" border="0">
    <tr>
        <td width="100%" align="left" >
            <span>
                <b>3.8.7. Thu nhập 7:</b> Từ quản lý trực tiếp toàn hệ thống của FC (Cấp 2)
            </span>
        </td>
    </tr>
</table>
<table cellpadding="5" cellspacing="0" border="1">
    <tr>
        <td width="100%" align="center" >
            <span>
                Thu nhập = 46% x Tổng P.FYP toàn hệ thống FC báo cáo trực tiếp ED
            </span>
        </td>
    </tr>
</table>
<table cellpadding="5" cellspacing="0" border="0">
    <tr>
        <td width="100%" align="left" >
            <span>
                <b>3.8.8. Thu nhập 8:</b> Từ quản lý trực tiếp toàn hệ thống của RE (Cấp 1)
            </span>
        </td>
    </tr>
</table>
<table cellpadding="5" cellspacing="0" border="1">
    <tr>
        <td width="100%" align="center" >
            <span>
                Thu nhập = 56% x Tổng P.FYP toàn hệ thống RE báo cáo trực tiếp ED
            </span>
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