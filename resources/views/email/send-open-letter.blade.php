<p style="font-size: 24px;"><b>THƯ NGỎ</b></p>
<p>Kính chào Quý khách <b>{{ $customer->name }} - HĐBH số {{ $contract->contract_number}}</b></p>
<p>Lời đầu tiên, Công ty cổ phần tập đoàn Medici (Medici) xin gửi đến Quý khách lời chào trân trọng, lời kính chúc sức khỏe và lời cảm ơn chân thành nhất! </p>
<p>Medici là doanh nghiệp cung cấp dịch vụ tư vấn các sản phẩm bảo hiểm của Công ty TNHH Bảo hiểm nhân thọ FWD Việt Nam (FWD) theo Hợp đồng đại lý bảo hiểm ký kết ngày 18/07/2021.Với phương châm lấy khách hàng làm trọng tâm, Medici sẽ là người đồng hành tin cậy trong hành trình kết nối giữa Quý khách với FWD để bảo đảm kế hoạch tài chính cũng như thực hiện thành công những mục tiêu trong tương lai của Quý khách cùng Gia đình.</p>
<p>Medici vui mừng thông báo Hợp đồng bảo hiểm nhân thọ của Quý khách được:</p>
<p><b>Tư vấn và phục vụ bởi: </b>Tư vấn tài chính FWD <b>{{ $agencyInfoFwd->name_agency_official }}</b>, mã số <b>{{ $agencyInfoFwd->code_agency_official }}</b>, số điện thoại: <b>{{ $agencyInfoFwd->phone }}</b></p>
<p><b>Đồng phục vụ bởi: </b>Thành viên Medici <b>{{ $insurance_agency->name }}</b>, mã số <b>{{ $insurance_agency->code_agency }}</b>, số điện thoại: <b>{{ $insurance_agency->phone }}</b></p>
<p>Trong trường hợp Quý khách cần trao đổi thêm thông tin hoặc có yêu cầu liên quan đến nội dung, quyền lợi trong Hợp đồng bảo hiểm, Quý khách vui lòng liên hệ <b>{{ $insurance_agency->name }}</b> hoặc liên hệ đến số điện thoại tổng đài Chăm sóc khách hàng của FWD <b>1800969690</b>.</p>
<p>Để có thêm thông tin về sản phẩm và dịch vụ của FWD, Quý khách vui lòng đăng nhập <b>www.fwd.com.vn</b>.</p>
<p>Được phục vụ và mang lại sự hài lòng cho Quý khách là niềm vinh hạnh của Medici chúng tôi!</p>
<p>Trân trọng !</p>
<p><a href="{{ env('LINK_HOMEPAGE').$consultation->pdf_open_letter }}" target="_blank">File đính kèm (Thư ngỏ)</a></p>
