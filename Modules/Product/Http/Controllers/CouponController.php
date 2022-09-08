<?php

namespace Modules\Product\Http\Controllers;

use Validator;
use Modules\Product\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Redirect;
use Carbon\Carbon;

class CouponController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        $coupons = Coupon::where('status','>','-1')->orderBy('created_at', 'desc')->paginate(15);
        return view('product::coupons/index', [
            "coupons" => $coupons
        ]);
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('product::coupons/create');
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $params = $request->all();
        $validatorArray = [
            'start_time' => 'required',
            'end_time' => 'required',
            'sale_off_type' => 'required',
            'sale_off_amount' => 'required'
        ];

        $validator = Validator::make($request->all(), $validatorArray);
        if ($validator->fails()) {
            $message = $validator->errors();
            return Redirect::route('product.coupons.create')->withErrors([$message->first()]);
        }

        if (Carbon::parse($params["start_time"]) > Carbon::parse($params["end_time"])) {
            $message = 'Ngày bắt đầu phải nhỏ hơn ngày kết thúc';
            return Redirect::route('product.coupons.create')->withErrors([$message]);
        }

        do
        {
            $code = 'EBH'.str_random(5);
            $coupon = Coupon::where('coupon_code', $code)->first();
        }
        while(!empty($coupon));

        $result = Coupon::create([
            "coupon_code" => $code,
            "start_time" => Carbon::parse($params["start_time"]),
            "end_time" => Carbon::parse($params["end_time"]),
            "sale_off_type" => $params["sale_off_type"],
            "sale_off_amount" => $params["sale_off_amount"]
        ]);

        return Redirect::route('product.coupons.index');
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit($id)
    {
        $obj = Coupon::where("id", $id)->first();
        return view('product::coupons/edit', [
            "coupon" => $obj
        ]);
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request, $id)
    {
        $params = $request->all();

        $validatorArray = [
            'start_time' => 'required',
            'end_time' => 'required',
            'status' => 'required',
            'sale_off_type' => 'required',
            'sale_off_amount' => 'required'
        ];

        $validator = Validator::make($request->all(), $validatorArray);
        if ($validator->fails()) {
            $message = $validator->errors();
            return Redirect::route('product.coupons.edit', $id)->withErrors([$message->first()]);
        }

        if (Carbon::parse($params["start_time"]) > Carbon::parse($params["end_time"])) {
            $message = 'Ngày bắt đầu phải nhỏ hơn ngày kết thúc';
            return Redirect::route('product.coupons.create')->withErrors([$message]);
        }

        $obj = Coupon::where("id", $id)->first();
        if ($obj) {
            $obj->start_time = Carbon::parse($params["start_time"]);
            $obj->end_time = Carbon::parse($params["end_time"]);
            $obj->status = $params["status"];
            $obj->sale_off_type = $params["sale_off_type"];
            $obj->sale_off_amount = $params["sale_off_amount"];
            $obj->save();

            return Redirect::route('product.coupons.index');
        } else {
            return Redirect::route('product.coupons.index')->withErrors(["Bản ghi không tồn tại!"]);
        }
    }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy($id)
    {
        $obj = Coupon::where("id", $id)->first();
        if ($obj) {
            $obj->status = Coupon::STATUS_DELETED;
            $obj->save();

            return Redirect::route('product.coupons.index');
        } else {
            return Redirect::route('product.coupons.index')->withErrors(["Bản ghi không tồn tại!"]);
        }
    }
}
