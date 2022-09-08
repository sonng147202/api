<table cellpadding="0" cellspacing="0" border="0">
    <tr>
        <td width="20%" align="left">
            <img src="/img/logo-medici.png" width="80px">
        </td>
        <td width="80%" align="center">
            <span class="center bold font-14">CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM</span>
            <br>
            <span class="center bold font-14">Độc lập – Tự do – Hạnh phúc</span>
            <br>
            <span class="center bold font-14">---------------------</span>
        </td>
    </tr>
</table>
<br>
<br>
<table cellpadding="0" cellspacing="0" border="0">
    <tr>
        <td width="100%" align="center">
            <span class="center bold font-18">HỢP ĐỒNG HỢP TÁC TƯ VẤN VÀ PHÂN PHỐI SẢN PHẨM</span>
        </td>
    </tr>
    <br>
    <tr>
        <td width="100%" align="center">
            <span class="center font-14">Số: {{$contract_code}}/ HĐHT/MEDICI (Số Hợp đồng sẽ do BP Hành chính điền vào)</span>
        </td>
    </tr>
</table>
<hr>
<br>
<br>
<table cellpadding="0" cellspacing="0" border="0">
    <tr>
        <td width="100%" align="left">
            <span class="left">Căn cứ Bộ Luật dân sự hiện hành, Chính sách kinh doanh của Công ty cổ phần tập đoàn</span>
            <span class="left">Medici, Nhu cầu và năng lực của mỗi bên, hôm nay, ngày: {{ date('d/m/Y') }}, chúng tôi gồm:</span>
        </td>
    </tr>
</table>
<table cellpadding="1" cellspacing="0" border="1">
    <tr style = "background-color:#777777">
        <td width="100%" align="left">
            <span class="left bold font-12">I - BÊN A: CÔNG TY CỔ PHẦN TẬP ĐOÀN MEDICI</span>
        </td>
    </tr>
    <tr>
        <td width="100%" align="left">
            <span class="left font-11">Do Ông/Bà: Ngô Đức Anh – Chức vụ: Chủ tịch HĐQT - Tel: 19003434</span>
            <br>
            <span class="left font-11">Website: www.baohiem.medici.vn Giấy chứng nhận Đăng ký kinh doanh số 0109616541 do Sở Kế hoạch</span>
            <br>
            <span class="left font-11">và Đầu tư TP. Hà Nội cấp lần đầu ngày 29/04/2021 (Sau đây gọi là “Công ty”)</span>
        </td>
    </tr>
</table>
<table cellpadding="0" cellspacing="0" border="1">
    <tr style = "background-color:#777777">
        <td width="100%" align="left">
            <span class="left bold font-12">II - BÊN B: THÀNH VIÊN TƯ VẤN VÀ PHÂN PHỐI SẢN PHẨM</span>
        </td>
    </tr>
</table> 
<table border="1" cellpadding="0" cellspacing="0">   
    <tr style ="border-style: none;">
        <td width="58%" style ="border-style: none;" class = "font-11">Họ và tên: {{ !empty($data['name']) ? $data['name'] :'' }}</td>
        <td width="27%" style ="border-style: none;" class = "font-11">Ngày sinh: {{ !empty($data['birthday']) ? date('d/m/Y',strtotime($data['birthday'])) :'' }}</td>
        <td width="15%" style ="border-style: none;" class = "font-11">Giới tính: {{ (isset($data['sex']) &&  $data['sex'] == 1 ) ? 'Nam' : 'Nữ' }}</td>
    </tr>
    <tr style ="border-style: none;">
        <td width="33%" style ="border-style: none;" class = "font-11">Số CCCD/CMT: {{ !empty($data['id_card_number']) ? $data['id_card_number'] :'' }}</td>
        <td width="25%" style ="border-style: none;" class = "font-11">Ngày cấp: {{ !empty($data['date_card_number']) ? date('d/m/Y',strtotime($data['date_card_number'])) :'' }}</td>
        <td width="42%" style ="border-style: none;" class = "font-11">Nơi cấp: {{ !empty($data['place_card_number']) ? $data['place_card_number'] :'' }}</td>
    </tr>
    <tr style ="border-style: none;">
        <td width="25%" style ="border-style: none;" class = "font-11">Dân tộc: </td>
        <td width="33%" style ="border-style: none;" class = "font-11">Điện thoại: {{ !empty($data['phone']) ? $data['phone'] :'' }}</td>
        <td width="42%" style ="border-style: none;" class = "font-11">Email: {{ !empty($email['email']) ? $email['email'] :'' }}</td>
    </tr>
    <tr style ="border-style: none;">
        <td width="100%" style ="border-style: none;" class = "font-11">Địa chỉ: {{ !empty($data['address']) ? $data['address'] :'' }}</td>
    </tr>
    <tr style ="border-style: none;">
        <td width="100%" style ="border-style: none;" class = "font-11">(Sau đây gọi là “Thành viên”)</td>
    </tr>
</table>
<table border="1" frame="border"> 
    <tr style = "background-color:#777777"> 
        <td width="100%" align="left">
            <span class="left bold font-12">III - NỘI DUNG HỢP ĐỒNG</span>
        </td>
    </tr>
</table>
<table border="1" frame="border"> 
    <tr> 
        <td width="100%" align="left" style ="border-style: none;">
            <span class="left bold font-11 font-italic">Xét rằng:</span>
        </td>
    </tr>
    <tr>
        <td width="100%" align="center" style ="border-style: none;">
            <span class="left font-11">1. Công ty Medici là doanh nghiệp tư vấn và phân phối các sản phẩm bảo hiểm hoạt động dưới hình thức 
                Đại lý tổ chức cho các đối tác là công ty, doanh nghiệp bảo hiểm sẽ hợp tác với Thành viên để tư vấn, 
                phân phối các sản phẩm bảo hiểm do Công ty Medici đang chính thức phân phối.
            </span>
        </td>
    </tr>
    <tr>
        <td width="100%" align="center" style ="border-style: none;">
            <span class="left font-11">2. Thành viên phải đảm bảo rằng mình có đầy đủ các điều kiện về năng lực pháp luật dân sự, năng lực 
                hành vi dân sự và không nằm trong diện bị hạn chế bởi bất kỳ quy định nào của Pháp luật khi tham gia 
                ký kết và thực hiện Hợp đồng này. Trên cơ sở đó, hai bên thỏa thuận ký kết những điều khoản, điều kiện 
                như sau:
            </span>
        </td>
    </tr>
    <tr>
        <td width="100%" align="center" style ="border-style: none;">
            <span class="left font-11">2.1. Công ty giao và Thành viên đồng ý nhận hợp tác để tư vấn, phân phối các sản phẩm bảo hiểm do Công 
                ty đang chính thức phân phối trong thời hạn 01 (một) năm kể từ ngày ký kết Hợp đồng này, trừ trường 
                        hợp Hợp đồng này bị chấm dứt trước thời hạn căn cứ theo Điều 7 của Bản các Điều khoản và điều kiện 
                        áp dụng. Hợp đồng này sẽ tự động gia hạn 01 (một) năm mỗi khi hết hạn, trừ trường hợp một trong hai 
                        bên thông báo bằng văn bản cho bên kia về việc không tiếp tục gia hạn thêm trước ít nhất 15 ngày so với 
                    ngày hết hạn Hợp đồng, hoặc Hợp đồng bị chấm dứt trước thời hạn
            </span>
        </td>
    </tr>
    <tr>
        <td width="100%" align="center" style ="border-style: none;">
            <span class="left font-11">2.2. Những tài liệu sau đây là một phần cấu thành và không tách rời của Hợp đồng (Xem tại BP Hành 
                chính của Công ty hoặc trên Website/App): a. Bản các điều khoản và điều kiện áp dụng; b. Nguyên tắc 
                tuân thủ và đạo đức của Thành viên Medici.
            </span>
        </td>
    </tr>
    <tr>
        <td width="100%" align="center" style ="border-style: none;">
            <span class="left font-11">2.3. Công ty và Thành viên cam kết thực hiện đúng các nội dung, điều khoản, điều kiện áp dụng của Hợp 
                đồng. Bên B đồng ý đã đọc kỹ, xác nhận hiểu rõ, đồng thời cam kết tuân thủ đầy đủ.
            </span>
        </td>
    </tr>
</table>
<br>
<br>
<table border="1"> 
    <tr> 
        <td width="50%" align="center">
            <span class="left bold font-12">BÊN A: CÔNG TY CỔ PHẦN TẬP ĐOÀN MEDICI</span>
        </td>
        <td width="50%" align="center">
            <span class="left bold font-12">BÊN B: THÀNH VIÊN</span>
        </td>
    </tr>
    <tr> 
        <td width="50%" align="center">
            <span class="center font-italic font-12">(Ký tên, đóng dấu)</span>
            <br>
            <span class="center"><img src="/img/ceo-ngo.png" width="120px" height="60px"></span>
            <br>
            <span class="center font-italic bold ">CHỦ TỊCH HĐQT: Ngô Đức Anh</span>
        </td>
        <td width="50%" align="center">
            <span class="center font-italic font-12">(Ký và ghi rõ họ tên)</span>
            <br>
            <span class="center"><img src="{{ !empty($signature) ? $signature : '' }}" class="signature"></span>
        </td>
    </tr>
</table>
<style>
    .signature {
        width: 120px;
        height: 80px;
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
    .font-14 {
        font-size: 14px;
    }
    .font-16 {
        font-size: 16px;
    }
    .font-18 {
        font-size: 18px;
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