<table cellpadding="3" cellspacing="0" border="0" class = "table-x">
    <tr>
        <td width="100%" align="left"><span class="left font-16 "></span></td>
    </tr>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <tr>
        <td width="50%" align="left"><span class="left font-16 ">Bổ nhiệm {{$sex}}</span></td>
    </tr>
    <tr>
        <td width="100%" align="left"><span class="left bold font-26 " style ="color: blue">{{$name}}</span></td>
    </tr> 
    <br>
    <br>   
    <tr >
        <td width="70%" align="left"><span class="left font-16 ">Mã số : {{$code}} - Chức vụ : </span><span class="left bold font-16" style ="color: #FFD700"> {{$level->code}}</span></td>
    </tr>
    <tr>
        <td width="100%" align="left"><span class="left bold font-16 " style ="color: blue ; text-transform: uppercase;">{{$level->name}}</span></td>
    </tr>
    <br>
    <br>
    <br>
    <br>
    <tr>    
        <td width="50%" align="left" class="font-italic">
            <span class="left font-12 font-italic">Hà Nội, ngày {{ substr($appoint_date,0,2) }} tháng {{ substr($appoint_date,3,2) }} năm {{ substr($appoint_date,6,4) }}</span>
        </td>
    </tr>
    <tr>    
        <td align="left" width="50%">
            <span><img src="/img/ceo-ngo2.png" width="200px" height="150px"></span>
        </td>
    </tr>    
    <tr>    
        <td align="center" width="40%">
            <span class="bold"> Ngô Đức Anh </span>  
            <br>
            <span>Chủ tịch HĐQT</span>
        </td>
    </tr>

</table>
<!-- <div style="position: relative;">
        <img src="/img/banner_appoint.png" style="width:100%;" />
</div> -->
<style>
    table.table-x{
        margin-top: 1000px;
    }
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
    .font-26 {
        font-size: 26px;
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
