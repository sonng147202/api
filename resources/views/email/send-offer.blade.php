<p><b>Kính gửi {{ $sex.' '.$name }},</b></p>
<p>Chúng tôi trân trọng cảm ơn sự quan tâm của {{ $sex }} đến cơ hội trở thành thành viên,làm việc tại CÔNG TY CỔ PHẦN TẬP ĐOÀN MEDICI (gọi tắt là MEDICI)</p>
<p>Căn cứ vào kết quả phỏng vấn và đánh giá năng lực, chúng tôi kính mời {{ $sex }} tiếp nhận công việc tại Medici với các thông tin cơ bản liên quan đến công việc như Thư mời hợp tác kinh doanh, Phụ lục Chính sách Thù lao dành cho Hệ thống Kinh doanh và Quy định bổ nhiệm quản lý trực tiếp đính kèm.</p>
<p><a href="{{ $pdf_offer }}" target="_blank">File đính kèm (Thư mời Hợp tác Kinh doanh kèm Phụ lục)</a><p>
<p><a href="{{ env('LINK_HOMEPAGE').'/files/Memo-211110_BonhiemQuanlyTructiep.pdf' }}" target="_blank">File đính kèm (Quy định bổ nhiệm quản lý trực tiếp)</a></p>

@if($level >= 3 && $level <= 5)
    <p><a href="{{ env('LINK_HOMEPAGE').'/files/Memo_30.22_Chinh_sach_thu_lao_danh_cho_HTKD.pdf' }}" target="_blank">File đính kèm (Chính sách thù lao cho HTKD)</a></p>
@elseif ($level == 6)
    <p><a href="{{ env('LINK_HOMEPAGE').'/files/Memo_30.22_Chinh_sach_thu_lao_danh_cho_HTKD.pdf' }}" target="_blank">File đính kèm (Chính sách thù lao cho HTKD)</a></p>
@elseif ($level == 7)
    <p><a href="{{ env('LINK_HOMEPAGE').'/files/Memo_30.22_Chinh_sach_thu_lao_danh_cho_HTKD.pdf' }}" target="_blank">File đính kèm (Chính sách thù lao cho HTKD)</a></p>
@elseif ($level == 8)
<p><a href="{{ env('LINK_HOMEPAGE').'/files/Memo_30.22_Chinh_sach_thu_lao_danh_cho_HTKD.pdf' }}" target="_blank">File đính kèm (Chính sách thù lao cho HTKD)</a></p>
@endif  
<p><a href="{{ env('LINK_HOMEPAGE').'/files/Memo_32.22_Chinh_sach_thu_lao_Phi_nhan_tho.pdf' }}" target="_blank">File đính kèm (Chính sách thù lao phi nhân thọ)</a></p>
<p><a href="{{ env('LINK_HOMEPAGE').'/files/Memo-211110_GoihotrokhoinghiepMedici.pdf' }}" target="_blank">File đính kèm (Gói hỗ trợ khởi nghiệp)</a></p>

<p>{{ $sex }} vui lòng xác nhận lại về việc đồng ý hợp tác với Công ty Medici & ngày dự kiến {{ $sex }} có thể bắt đầu tuyển dụng xây dựng hệ thống kinh doanh ngay khi nhận được email Thư mời hợp tác kinh doanh này.</p>
<p>Trong trường hợp chúng tôi không nhận được email phản hồi, nhưng nhận được hồ sơ của {{ $sex }} thông qua email chuyên trách của Bộ phận Tuyển dụng Medici sẽ được hiểu là {{ $sex }} đã đồng ý với các điều kiện về Vị trí và Cấp bậc đã được đề xuất trong Thư mời hợp tác kinh doanh này.</p>
<p style="color:Red">Thư mời làm việc có giá trị trong vòng 15 ngày kể từ ngày Bộ phận Tuyển dụng của Công ty Medici gửi đến {{ $sex }}.</p>
<p>Ghi chú: Vui lòng liên hệ quản lý trực tiếp của {{ $sex }} hoặc liên hệ Bộ phận tuyển dụng để được hướng dẫn thủ tục nộp hồ sơ bổ nhiệm:</p>
<ul>
    <li>Hotline: {{env('HOTLINE')}}</li>
    <li>Email: hethong@medici.vn</li>
</ul>