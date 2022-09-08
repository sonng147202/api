<p>Kính gửi {{ $sex.' '.$name }},</p>
<p>Công ty cổ phần Tập Đoàn Medici (gọi tắt là Medici) xin trân trọng chúc mừng </p>
<p>{{ $sex.' '.$name }} -  Mã số :{{$code_agency}} chính thức được bổ nhiệm vị trí : {{$level}}</p>
<p> hiệu lực kể từ ngày {{$appoint_date}} kèm theo file đính kèm :</p>
<p><a href="{{ env('LINK_HOMEPAGE').$pdf_appoint_letter }}" target="_blank">file PDF bổ nhiệm đính kèm</a></p>
<p>Với những nỗ lực phấn đấu của {{$sex}}, Medici tin tưởng rằng việc bổ nhiệm chính thức</p>
<p>này, {{$sex}} sẽ phát huy hơn nữa khả năng quản lý và phát triển hệ thống kinh doanh, </p>
<p>đóng góp vào mục tiêu phát triển chung của Medici và mang lại thành công riêng cho </p>
<p>đội ngũ cũng như cá nhân {{$sex}}.</p>
<p>Chúc {{$sex}} cùng gia đình Sức khỏe – Hạnh phúc – Thịnh vượng!</p>
<p>Trân trọng,</p>
<p style="text-transform: uppercase;color:Red">MEDICI INSURANCE</p>
<p>Tel: {{env('HOTLINE')}}</p>
<p>Email: {{env('EMAIL_SUPPORT')}}</p>
<p>Website: {{env('APP_URL')}}</p>