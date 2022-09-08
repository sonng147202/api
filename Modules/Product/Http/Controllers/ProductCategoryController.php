<?php

namespace Modules\Product\Http\Controllers;

use Validator;
use Modules\Product\Models\Category;
use Modules\Product\Models\InsuranceType;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Redirect;

class ProductCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        $productCategories = Category::whereNull('parent_id')
            ->with(['children' => function($q) {
                $q->with('insurance_type')
                    ->with('parent_category');
            }])
            ->with('insurance_type')
            ->with('parent_category')
            ->paginate(10);
        return view('product::categories/index', [
            "productCategories" => $productCategories
        ]);
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('product::categories/create', [
            "insuranceTypes" => InsuranceType::getListType(),
            "listCategory" => Category::getNestedListCategory()
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
            'insurance_type_id' => 'required',
            'name' => 'required',
            'status' => 'required'
        ];

        $validator = Validator::make($request->all(), $validatorArray);
        if ($validator->fails()) {
            $message = $validator->errors();
            return Redirect::route('product.categories.create')->withErrors([$message->first()]);
        }

        $result = Category::create([
            "insurance_type_id" => $params["insurance_type_id"],
            "name" => $params["name"],
            "parent_id" => isset($params["parent_id"]) ? $params["parent_id"] : null,
            "description" => isset($params["description"]) ? $params["description"] : null,
            "avatar" => isset($params["avatar"]) ? $params["avatar"] : null,
            "status" => $params["status"]
        ]);

        return Redirect::route('product.categories.index')->with('msg_success','Thêm danh mục sản phẩm thành công');
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit($id)
    {
        $obj = Category::where("id", $id)->first();
        return view('product::categories/edit', [
            "productCategory" => $obj,
            "insuranceTypes" => InsuranceType::getListType(),
            "listCategory" => Category::getNestedListCategory()
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
            'insurance_type_id' => 'required',
            'name' => 'required',
            'status' => 'required'
        ];

        $validator = Validator::make($request->all(), $validatorArray);
        if ($validator->fails()) {
            $message = $validator->errors();
            return Redirect::route('product.categories.edit', $id)->withErrors([$message->first()]);
        }

        $obj = Category::where("id", $id)->first();
        if ($obj) {
            $obj->insurance_type_id = $params["insurance_type_id"];
            $obj->name = $params["name"];
            $obj->parent_id = isset($params["parent_id"]) ? $params["parent_id"] : null;
            $obj->description = isset($params["description"]) ? $params["description"] : null;
            $obj->avatar = isset($params["avatar"]) ? $params["avatar"] : null;
            $obj->status = $params["status"];
            $obj->save();

            return Redirect::route('product.categories.index')->with('msg_success','Sửa danh mục sản phẩm thành công');
        } else {
            return Redirect::route('product.categories.index')->withErrors(["Bản ghi không tồn tại!"]);
        }
    }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy($id)
    {
        $obj = Category::where("id", $id)->first();
        if ($obj) {
            $obj->delete();
//            $obj->status = Category::STATUS_DELETED;
//            $obj->save();

            return Redirect::route('product.categories.index')->with('msg_success','Xóa danh mục sản phẩm thành công');
        } else {
            return Redirect::route('product.categories.index')->withErrors(["Bản ghi không tồn tại!"]);
        }
    }
}
