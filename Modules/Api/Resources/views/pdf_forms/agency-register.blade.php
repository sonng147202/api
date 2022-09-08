<table cellpadding="0" cellspacing="0" border="0">
    <tr>
        <td width="15%" align="left">
            <img src="/img/logo-medici.png" width="60px">
        </td>
        <td width="85%" align="center" class="border-td-bottom bold ">
            <span style="width:100%;" class="font-italic">Chúng tôi nồng nhiệt chào đón bạn đến với Medici để <br></span><span>THẮP SÁNG ĐAM MÊ, LAN TỎA GIÁ TRỊ và KHẲNG ĐỊNH THÀNH CÔNG!</span>
        </td>
    </tr>
</table>
<br>
<br>
<table cellpadding="0" cellspacing="0" border="0">
    <tr>
        <td width="100%" align="center">
            <span class="center bold font-16">ĐƠN ĐĂNG KÝ THÀNH VIÊN</span><br><span class="font-11">CÔNG TY CỔ PHẦN TẬP ĐOÀN MEDICI</span>
        </td>
    </tr>
</table>
<br>
<br>
<table cellpadding="3" cellspacing="0" border="0">
     <tr width="100%">
        <td class="bold">THÔNG TIN ỨNG VIÊN</td>
    </tr>
</table>
<table cellpadding="1" cellspacing="0" border="0">
    <tr >
        <td width="42%">Họ tên: {{ !empty($data['name']) ? $data['name'] :'' }}</td>
        <td width="33%">Ngày sinh: {{ !empty($data['birthday']) ? $data['birthday'] :'' }}</td>
        <td width="25%">Giới tính: {{ (isset($data['sex']) &&  $data['sex'] == 1 ) ? 'Nam' : 'Nữ' }}</td>
    </tr>
    <tr>
        <td width="42%">Số CCCD/CMT: {{ !empty($data['id_card_number']) ? $data['id_card_number'] :'' }}</td>
        <td width="33%">Ngày cấp: {{ !empty($data['date_card_number']) ? $data['date_card_number'] :'' }}</td>
        <td width="25%">Nơi cấp: {{ !empty($data['place_card_number']) ? $data['place_card_number'] :'' }}</td>
    </tr>
    <tr>
        <td width="42%">Điện thoại: {{ !empty($data['phone']) ? $data['phone'] :'' }}</td>
        <td width="33%">Email: {{ !empty($data['email']) ? $data['email'] :'' }}</td>
        <td width="25%">Vị trí đề xuất: {{ (!empty($agency->level)) ? $agency->level->code : ''}}</td>
    </tr>
    <tr>
        <td width="42%">Người giới thiệu: {{ !empty($agency_invite) ? $agency_invite->name : '' }}</td>
        <td width="33%">Vị trí: {{ !empty($agency_invite->level) ? $agency_invite->level->code : '' }}</td>
        <td width="25%">Mã số: {{ !empty($agency_invite) ? $agency_invite->code_agency : '' }}</td>
    </tr>
    <tr>
        <td width="42%">Điện thoại: {{ !empty($agency_invite) ? $agency_invite->phone: '' }}</td>
        <td width="58%">Email: {{ !empty($agency_invite) ? $agency_invite->user->email : ''}}</td>
    </tr>
    <tr>
        <td width="42%">Người quản lý trực tiếp: {{ !empty($agency_parent) ? $agency_parent->name : '' }}</td>
        <td width="33%">Vị trí: {{ !empty($agency_parent->level) ? $agency_parent->level->code : '' }}</td>
        <td width="25%">Mã số: {{ !empty($agency_parent) ? $agency_parent->code_agency : '' }}</td>
    </tr>
    <tr>
        <td width="42%">Điện thoại: {{ !empty($agency_parent) ? $agency_parent->phone : '' }}</td>
        <td width="58%">Email: {{ !empty($agency_parent->user) ? $agency_parent->user->email : '' }}</td>
    </tr>
</table>
<br>
<br>
<table cellpadding="3" cellspacing="0" border="0">
     <tr width="100%">
        <td class="bold">THÔNG TIN THỪA KẾ</td>
    </tr>
</table>
<table cellpadding="2" cellspacing="0" border="1">
    <tr class="bold">
        <td width="20%" align="center">Thứ tự ưu tiên</td>
        <td width="40%" align="center">Họ và tên người thừa kế</td>
        <td width="20%" align="center">Mối quan hệ</td>
        <td width="20%" align="center">Số điện thoại</td>
    </tr>
    <tr >
        <td width="20%" align="center">Ưu tiên 1</td>
        <td width="40%" align="center">{{ !empty($data['name_inherit_1']) ? $data['name_inherit_1'] :'' }}</td>
        <td width="20%" align="center">{{ !empty($data['relationship_inherit_1']) ? $data['relationship_inherit_1'] :'' }}</td>
        <td width="20%" align="center">{{ !empty($data['phone_inherit_1']) ? $data['phone_inherit_1'] :'' }}</td>
    </tr>
    <tr >
        <td width="20%" align="center">Ưu tiên 2</td>
        <td width="40%" align="center">{{ !empty($data['name_inherit_2']) ? $data['name_inherit_2'] :'' }}</td>
        <td width="20%" align="center">{{ !empty($data['relationship_inherit_2']) ? $data['relationship_inherit_2'] :'' }}</td>
        <td width="20%" align="center">{{ !empty($data['phone_inherit_2']) ? $data['phone_inherit_2'] :'' }}</td>
    </tr>
</table>
<br>
<br>
<table cellpadding="0" cellspacing="0" border="0">
    <tr class="font-italic">
        <td width="100%" align="left">Tôi cam kết rằng mọi thông tin được cung cấp tại Đơn đăng ký này là đúng sự thật. Tôi xin chịu trách
        nhiệm trước pháp luật về tính chính xác và trung thực của các thông tin này. Tôi hiểu rằng và không
        có khiếu nại gì về việc tôi cung cấp thông tin không trung thực sẽ dẫn đến việc Medici có toàn quyền
        chấm dứt ngay lập tức Hợp đồng hợp tác đã ký với tôi, mà Medici không cần báo trước.
        </td>
    </tr>
</table>
<br>
<br>
<table cellpadding="5" cellspacing="0" border="1">
    <tr >
        <td width="100%" align="center">
            <span class="font-12 bold center">NGƯỜI ĐĂNG KÝ</span><br><span class="left font-italic">Tôi đã đọc, hiểu rõ về hình thức thành viên là “Quản lý viên” của Medici.
Tôi đề nghị Medici chấp thuận cho tôi được tham gia vào Medici với tư cách là (Ứng viên ký
tên
xác nhận chọn vào ô thích hợp bên dưới):</span>
        </td>
    </tr>
    <tr width="100%">
        <td width="100%" align="left"><span class="bold">Quản lý viên (FC-TD).</span><br><span class="font-italic">Phải tham dự đầy đủ các khóa huấn luyện, hoạt động kinh doanh (Họp định kỳ, hội thảo…) và được tham gia thăng cấp, thi đua, thừa kế theo Quy định của Công ty. </span><br><span class="font-12 bold">Chữ Ký: </span><br>
        @if(!empty($agency->level) && $agency->level->level != 1)
        <span class="left"><img src="{{ !empty($signature) ? $signature : '' }}" class="signature"></span><br>
        <span>Ngày: {{ date('d/m/Y') }}</span>
        @endif
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
    .font-10 {
        font-size: 10px;
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