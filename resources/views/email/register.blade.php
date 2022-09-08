<p><b>Chào mừng thành viên mới</b></p>
<p>Ban lãnh đạo CÔNG TY CỔ PHẦN TẬP ĐOÀN MEDICI (gọi tắt là MEDICI) thân mến chào anh/chị {{ $name }}!</p>
<p>Chúc mừng anh/chị đã tham gia hệ thống của MEDICI bởi sự đề cử của thành viên {{ $agency_invite }}{{ !empty($agency_code) ? ', mã số MEDICI '.$agency_code : '' }}</p>
<p><b>Thông tin truy cập vào tài khoản của anh/chị như sau:</b></p>
<p>+ Địa chỉ: <a href="{{env('LINK_HOMEPAGE')}}">{{env('LINK_HOMEPAGE')}}</a></p>
<p>+ Tên người dùng: {{$email}}</p>
<p>+ Mật khẩu: {{$password}}</p>
<p>+ Thuộc Văn phòng: {{ $office }}</p>
<p>+ Cấp bậc: {{ $level }}</p>
<p>Mời anh/chị bắt đầu sử dụng tài khoản bằng cách đăng nhập vào hệ thống của công ty và thay đổi mật khẩu theo ý riêng của mình sớm nhất có thể bảo mật thông tin.</p>
<p>Nếu cần hỗ trợ, xin mời liên hệ:</p>
<p style="text-transform: uppercase;color:Red">BỘ PHẬN TUYỂN DỤNG | MEDICI INSURANCE</p>
<p>Tel: {{env('HOTLINE')}}</p>
<p>Email: {{env('EMAIL_SUPPORT')}}</p>
<p>Website: {{env('APP_URL')}}</p>