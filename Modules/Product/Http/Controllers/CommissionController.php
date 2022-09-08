<?php

namespace Modules\Product\Http\Controllers;

use Validator;
use Modules\Product\Models\Commission;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Redirect;

class CommissionController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        $commissions = Commission::paginate(15);
        return view('product::commissions/index', [
            "commissions" => $commissions
        ]);
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('product::commissions/create');
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
            'name' => 'required',
            'commission_type' => 'required',
            'commission_amount' => 'required'
        ];

        $validator = Validator::make($request->all(), $validatorArray);
        if ($validator->fails()) {
            $message = $validator->errors();
            return Redirect::route('product.commissions.create')->withErrors([$message->first()]);
        }

        $obj = Commission::where("name", $params["name"])->first();
        if ($obj) {
            $message = "Dữ liệu đã tồn tại trong hệ thống rồi";
            return Redirect::route('product.commissions.create')->withErrors($message);
        }

        $result = Commission::create([
            "name" => $params["name"],
            "commission_type" => $params["commission_type"],
            "commission_amount" => $params["commission_amount"]
        ]);

        return Redirect::route('product.commissions.index');
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit($id)
    {
        $obj = Commission::where("id", $id)->first();
        return view('product::commissions/edit', [
            "commission" => $obj
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
            'name' => 'required',
            'commission_type' => 'required',
            'commission_amount' => 'required'
        ];

        $validator = Validator::make($request->all(), $validatorArray);
        if ($validator->fails()) {
            $message = $validator->errors();
            return Redirect::route('product.commissions.edit', $id)->withErrors([$message->first()]);
        }

        $obj = Commission::where("id", $id)->first();
        if ($obj) {
            $obj->name = $params["name"];
            $obj->commission_type = $params["commission_type"];
            $obj->commission_amount = $params["commission_amount"];
            $obj->save();

            return Redirect::route('product.commissions.index');
        } else {
            return Redirect::route('product.commissions.index')->withErrors(["Bản ghi không tồn tại!"]);
        }
    }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy($id)
    {
        $obj = Commission::where("id", $id)->first();
        if ($obj) {
            $obj->delete();

            return Redirect::route('product.commissions.index');
        } else {
            return Redirect::route('product.commissions.index')->withErrors(["Bản ghi không tồn tại!"]);
        }
    }
}
