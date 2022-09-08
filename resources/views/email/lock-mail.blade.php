<p>Kính gửi {{ $sex.' '.$name }},</p>
<p>Mã số :{{$code_agency}} </p>
<p>Chức vụ : {{$level}}</p>
<p>Quản lý trực tiếp: {{$level_parent}} - {{$name_parent}} - Mã số: {{$code_agency_parent}}</p>
<p>Căn cứ Bộ luật Dân sự, các quy định pháp luật hiện hành và quy định của Công ty Cổ phần Tập đoàn Medici.</p>
<p>Căn cứ Hợp đồng hợp tác tư vấn và phân phối sản phẩn được ký kết giữa Công ty Cổ phần Tập đoàn Medici và {{ $sex.' '.$name }} ngày {{$date_approve}}</p>
<h2 style="font-weight: bold; text-align:center;">QUYẾT ĐỊNH</h2>
<p>Điều 1: Chấm dứt Hợp đồng hợp tác tư vấn và phân phối sản phẩm của {{ $sex.' '.$name }}</p>
<p>với Công ty Cổ phần Tập đoàn Medici và khóa Mã số kể từ ngày {{$date_now}}</p>
<p>Điều 2: {{ $sex.' '.$name }} phải chấm dứt việc hoạt động kinh doanh với vai trò là </p>
<p>{{$level}} của Công ty Cổ phần Tập đoàn Medici kể từ ngày Quyết định này có hiệu lực. </p>
<p>Điều 3: {{ $sex.' '.$name }} có trách nhiệm hoàn trả đầy đủ cho Công ty Cổ phần Tập đoàn Medici</p>
<p>các giấy tờ, vật dụng, tài sản của công ty trong thời gian 15 ngày kể từ ngày nhận được thông báo này.</p>
<p>Điều 4: Các Phòng ban liên quan cùng {{ $sex.' '.$name }} tiếp nhận thông tin chịu trách nhiệm thi hành Quyết định này.</p>
<p>Điều 5: Quyết định này có hiệu lực kể từ ngày Thông báo được gửi đi.</p>
<p>Trân trọng Thông báo!</p>
<p style="text-transform: uppercase;color:Red">MEDICI INSURANCE</p>
<p>Tel: {{env('HOTLINE')}}</p>
<p>Email: {{env('EMAIL_SUPPORT')}}</p>