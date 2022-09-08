<div class="col-md-3">
    <div style="border: #e6e6e6 1px solid; height: 100%">
        <p class="text-center" style="background: #0775c9; height: 40px; line-height: 40px; color: #fff; margin-bottom: 0px">
            <strong>KHÁCH HÀNG </strong>
        </p>
        <p style="background: #7fabdc; height: 20px; padding: 1px 5px; color: #fff" class="text-center">{{$total_customer}}</p>
        <div class="clearfix"></div>
        <div class="progress-group" style="padding: 1px 8px">
            <p>Khách hàng mới : {{number_format($kh_moi, 0)}}</p>
            {{-- <p>Khách hàng CƠ HỘI : {{number_format($kh_co_hoi, 0)}}</p> --}}
            <p>Khách hàng mua hàng : {{number_format($kh_mua_hang, 0)}}</p>
            <p>Khách hàng tái tục : {{number_format($kh_tai_tuc, 0)}}</p>
            <p>&nbsp;</p>
            <p>&nbsp;</p>
            <p class="text-center">
                <a href="{{route('insurance.customer.index')}}?type=moi&start={{$start}}&end={{$end}}" class="small-box-footer">
                    Xem chi tiết <i class="fa fa-arrow-circle-right"></i>
                </a>
            </p>
        </div>
    </div>
</div>
<!-- /.col -->
{{-- <div class="col-md-3">
    <div style="border: #e6e6e6 1px solid; height: 100%">
        <p class="text-center" style="background: #0775c9; height: 40px; line-height: 40px; color: #fff; margin-bottom: 0px">
            <strong>Tương tác</strong>
        </p>
        <p style="background: #7fabdc; height: 20px; padding: 1px 5px; color: #fff"><b class="pull-right">{{$kh_moi}}</b></p>
        <div class="clearfix"></div>
        <div class="progress-group" style="padding: 1px 8px">
            <p>Gửi báo giá : {{number_format($total_quotations, 0)}}</p>
            <p>Gửi Email Marketing : 0</p>
            <p>SMS : 0</p>
            <p>&nbsp;</p>
            <p>&nbsp;</p>
            <p class="text-center">
                <a href="{{route('insurance.quotation.index')}}?type=tiem_nang&start={{$start}}&end={{$end}}" class="small-box-footer">
                    Xem chi tiết <i class="fa fa-arrow-circle-right"></i>
                </a>
            </p>
        </div>
    </div>
</div> --}}
<!-- /.col -->
<div class="col-md-3">
    <div style="border: #e6e6e6 1px solid; height: 100%">
        <p class="text-center" style="background: #0775c9; height: 40px; line-height: 40px; color: #fff; margin-bottom: 0px">
            <strong>ĐỐI TÁC </strong>
        </p>
        <p style="background: #7fabdc; height: 20px; padding: 1px 5px; color: #fff" class="text-center">{{$kh_moi}}</p>
        <div class="clearfix"></div>
        <div class="progress-group" style="padding: 1px 8px">
            <p>Đối tác mới : {{number_format($partner_total)}}</p>
            <p>Đối tác có hợp đồng : {{number_format($partner_contract_total)}}</p>
            <p>&nbsp;</p>
            <p>&nbsp;</p>
            <p>&nbsp;</p>
            <p class="text-center">
                <a href="{{route('agency.newindex')}}?type=tiem_nang&start={{$start}}&end={{$end}}" class="small-box-footer">
                    Xem chi tiết <i class="fa fa-arrow-circle-right"></i>
                </a>
            </p>
        </div>
    </div>
</div>
<!-- /.col -->
<div class="col-md-3">
    <div style="border: #e6e6e6 1px solid; height: 100%">
        <p class="text-center" style="background: #0775c9; height: 40px; line-height: 40px; color: #fff; margin-bottom: 0px">
            <strong>DOANH THU</strong>
        </p>
        <p style="background: #7fabdc; height: 20px; padding: 1px 5px; color: #fff" class="text-center">0</p>
        <div class="clearfix"></div>
        <div class="progress-group" style="padding: 1px 8px">
            <p>Doanh thu sales : {{number_format($contract_sale)}} đ</p>
            {{-- <p>DT đại lý : {{number_format($contract_agence)}} đ</p> --}}
            {{-- <p>DT đơn BH có hiệu lực : {{number_format($tong_doanh_thu_hieuluc)}} đ</p> --}}
            {{-- <p>DT đơn BH chưa hiệu lực : {{number_format($tong_doanh_thu_khonghieuluc)}} đ</p> --}}
            {{-- <p>Hoa hồng nhận từ nhà BH : {{number_format($hoa_hong_nhan_tu_nha_BH)}} đ</p> --}}
            {{-- <p>Hoa hồng thực nhận : {{number_format($hoa_hong_thuc_nhan)}} đ</p> --}}
            <p>Doanh thu online : {{number_format($revenue_online)}} đ</p> 
            <p>Doanh thu đối tác : {{number_format($revenue_partner)}} đ</p> 
            <p>&nbsp;</p>
            <p>&nbsp;</p>
            <p class="text-center">
                <a href="{{route('insurance.statistic.revenue')}}?start={{$start}}&end={{$end}}" class="small-box-footer">
                    Xem chi tiết <i class="fa fa-arrow-circle-right"></i>
                </a>
            </p>
        </div>
    </div>
</div>
<!-- /.col -->
<div class="col-md-3">
    <div style="border: #e6e6e6 1px solid; height: 100%">
        <p class="text-center" style="background: #0775c9; height: 40px; line-height: 40px; color: #fff; margin-bottom: 0px">
            <strong>TÀI KHOẢN</strong>
        </p>
        <p style="background: #7fabdc; height: 20px; padding: 1px 5px; color: #fff" class="text-center">0</p>
        <div class="clearfix"></div>
        <div class="progress-group" style="padding: 1px 8px">
            <p>Số dư : 0đ</p>
            <p>Phát sinh tăng (+) :  0đ</p>
            {{-- <p>Công nợ CTBH : {{number_format($congno_baohiem, 0)}} đ</p> --}}
            <p>Phát sinh giảm (-) :  0đ</p>
            <p>Công nợ :  0đ</p>
            <p>&nbsp;</p>
            <p class="text-center">
                <a href="{{route('insurance.statistic.debt_kh')}}?start={{$start}}&end={{$end}}" class="small-box-footer">
                    Xem chi tiết <i class="fa fa-arrow-circle-right"></i>
                </a>
            </p>
        </div>
    </div>
</div>
<!-- /.col -->