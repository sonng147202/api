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
            <span class="center bold font-16">ĐƠN ĐĂNG KÝ THÀNH VIÊN <br><span class="font-11">CÔNG TY CỔ PHẦN TẬP ĐOÀN MEDICI</span></span>
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
        <td width="42%">Họ tên: {{ !empty($agency) ? $agency->name : '' }}</td>
        <td width="33%">Ngày sinh: {{ !empty($agency->birthday) ? date('d/m/Y', strtotime($agency->birthday)) :'' }}</td>
        <td width="25%">Giới tính: {{ (isset($agency->sex) &&  $agency->sex == 1 ) ? 'Nam' : 'Nữ' }}</td>
    </tr>
    <tr>
        <td width="42%">Số CCCD/CMT: {{ !empty($agency->id_card_number) ? $agency->id_card_number :'' }}</td>
        <td width="33%">Ngày cấp: {{ !empty($agency->date_card_number) ? date('d/m/Y', strtotime($agency->date_card_number)) :'' }}</td>
        <td width="25%">Nơi cấp: {{ !empty($agency->place_card_number) ? $agency->place_card_number :'' }}</td>
    </tr>
    <tr>
        <td width="42%">Điện thoại: {{ !empty($agency->phone) ? $agency->phone :'' }}</td>
        <td width="33%">Email: {{ !empty($agency->user->email) ? $agency->user->email :'' }}</td>
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
    @php
        $count = $insuranceAgencyInherit->count();
    @endphp
    @foreach ($insuranceAgencyInherit as $key=>$inherit)
        <tr >
            <td width="20%" align="center">Ưu tiên {{ $key + 1}}</td>
            <td width="40%" align="center">{{ $inherit->name }}</td>
            <td width="20%" align="center">{{ $inherit->relationship }}</td>
            <td width="20%" align="center">{{ $inherit->phone }}</td>
        </tr>
    @endforeach
    @if($count >= 0 && $count < 2)
        @for($i = $count; $i < 2;$i++)
        <tr >
            <td width="20%" align="center">Ưu tiên {{ $i + 1}}</td>
            <td width="40%" align="center"></td>
            <td width="20%" align="center"></td>
            <td width="20%" align="center"></td>
        </tr>
        
        @endfor
    @endif
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
            <span class="font-12 bold center">NGƯỜI ĐĂNG KÝ</span><br><span class="left font-italic">Tôi đã đọc, hiểu rõ về Hai hình thức thành viên là “Cộng tác viên” và “Quản lý viên” của Medici.Tôi đề nghị Medici chấp thuận cho tôi được tham gia vào Medici với tư cách là (Ứng viên ký tênxác nhận chọn vào ô thích hợp bên dưới):</span>
        </td>
    </tr>
    <tr width="100%">
        <td width="50%" align="left"><span class="bold">Cộng tác viên (RE).</span><br><span class="font-italic">Chỉ hưởng duy nhất quyền lợi là phí giới thiệu khách hàng, không tham gia bất kỳ quyền lợi nào khác của Medici (như thăng cấp, thi đua, thừa kế, huấn luyện). </span><br><span class="font-12 bold">Chữ Ký: </span><br>
        @if(!empty($agency->level) && $agency->level->level == 1)
        <span class="center"><img src="{{ !empty($signature) ? $signature : '' }}" class="signature"></span><br>
        <span>Ngày: {{ date('d/m/Y', strtotime($agency->created_at)) }}</span>
        @endif
        </td>
        <td width="50%" align="left"><span class="bold">Quản lý viên (FC-ED).</span><br><span class="font-italic">Phải tham dự đầy đủ các khóa huấn luyện, hoạt động kinh doanh (Họp định kỳ, hội thảo…) và được tham gia thăng cấp, thi đua, thừa kế theo Quy định của Công ty. </span><br><span class="font-12 bold">Chữ Ký: </span><br>
        @if(!empty($agency->level) && $agency->level->level != 1)
        <span class="center"><img src="{{ $signature }}" class="signature"></span><br>
        <span>Ngày: {{ date('d/m/Y', strtotime($agency->created_at)) }}</span>
        @endif
        </td>
    </tr>
</table>

<style>
    .signature {
        width: 120px;
        height: 85px;
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