<?php

namespace Modules\Product\Http\Controllers;

use Validator;
use Modules\Product\Models\ProductAgencyCommission;
use Modules\Product\Models\Product;
use Modules\Insurance\Models\InsuranceAgency;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Redirect;

class ProductAgencyCommissionController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        $productAgencyCommissions = ProductAgencyCommission::with('product')->with('insurance_agency')->paginate(15);
        $items = $productAgencyCommissions->items();
        return view('product::agency_commissions.index', [
            "productAgencyCommissions" => $productAgencyCommissions,
            'items'=>$items
        ]);
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('product::agency_commissions.create', [
            "products" => Product::all(),
            "insuranceAgencies" => InsuranceAgency::all()
        ]);
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
            'product_id' => 'required',
            'agency_id' => 'required',
            'commission_type' => 'required',
            'commission_amount' => 'required'
        ];

        $validator = Validator::make($request->all(), $validatorArray);
        if ($validator->fails()) {
            $message = $validator->errors();
            return Redirect::route('product.agency_commissions.create')->withErrors([$message->first()]);
        }

        $result = ProductAgencyCommission::create([
            "product_id" => $params["product_id"],
            "agency_id" => $params["agency_id"],
            "commission_type" => $params["commission_type"],
            "commission_amount" => $params["commission_amount"]
        ]);

        return Redirect::route('product.agency_commissions.index');
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit($id)
    {
        $obj = ProductAgencyCommission::where("id", $id)->first();
        return view('product::agency_commissions/edit', [
            "productAgencyCommission" => $obj,
            "products" => Product::all(),
            "insuranceAgencies" => InsuranceAgency::all()
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
            'product_id' => 'required',
            'agency_id' => 'required',
            'commission_type' => 'required',
            'commission_amount' => 'required'
        ];

        $validator = Validator::make($request->all(), $validatorArray);
        if ($validator->fails()) {
            $message = $validator->errors();
            return Redirect::route('product.agency_commissions.edit', $id)->withErrors([$message->first()]);
        }

        $obj = ProductAgencyCommission::where("id", $id)->first();
        if ($obj) {
            $obj->product_id = $params["product_id"];
            $obj->agency_id = $params["agency_id"];
            $obj->commission_type = $params["commission_type"];
            $obj->commission_amount = $params["commission_amount"];
            $obj->save();

            return Redirect::route('product.agency_commissions.index');
        } else {
            return Redirect::route('product.agency_commissions.index')->withErrors(["Bản ghi không tồn tại!"]);
        }
    }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy($id)
    {
        $obj = ProductAgencyCommission::where("id", $id)->first();
        if ($obj) {
            $obj->delete();

            return Redirect::route('product.agency_commissions.index');
        } else {
            return Redirect::route('product.agency_commissions.index')->withErrors(["Bản ghi không tồn tại!"]);
        }
    }
}
