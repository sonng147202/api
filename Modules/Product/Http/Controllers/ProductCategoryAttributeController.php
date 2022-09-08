<?php

namespace Modules\Product\Http\Controllers;

use Validator;
use Modules\Product\Models\CategoryAttribute;
use Modules\Product\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Redirect;

class ProductCategoryAttributeController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index($categoryId)
    {
        $productCategoryAttributes = CategoryAttribute::where("category_id", $categoryId)
            ->with('category')->paginate(15);
        return view('product::categoryAttributes/index', [
            "category" => Category::find($categoryId),
            "categoryId" => $categoryId,
            "productCategoryAttributes" => $productCategoryAttributes
        ]);
    }

    /*categories*
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create($categoryId)
    {
        return view('product::categoryAttributes/create', [
            "categoryId" => $categoryId,
            //"listCategory" => Category::getListCategory()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request, $categoryId)
    {
        $params = $request->all();

        $validatorArray = [
            //'category_id' => 'required',
            'name' => 'required',
            'title' => 'required',
            'data_type' => 'required'
        ];

        $validator = Validator::make($request->all(), $validatorArray);
        if ($validator->fails()) {
            $message = $validator->errors();
            return Redirect::route('product.category_attributes.create', $categoryId)->withErrors([$message->first()]);
        }

        $result = CategoryAttribute::create([
            "category_id" => $categoryId,
            "name" => $params["name"],
            "title" => $params["title"],
            "data_type" => $params["data_type"],
            "is_required" => isset($params["is_required"]) ? CategoryAttribute::REQUIRED : CategoryAttribute::NOT_REQUIRED,
            "default_value" => isset($params["default_value"]) ? $params["default_value"] : null,
            "compare_flg" => isset($params["compare_flg"]) ? CategoryAttribute::COMPARE : CategoryAttribute::NOT_COMPARE
        ]);

        return Redirect::route('product.category_attributes.index', $categoryId);
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit($categoryId, $id)
    {
        $obj = CategoryAttribute::where("id", $id)->first();
        if ($obj) {
            return view('product::categoryAttributes/edit', [
                "categoryId" => $categoryId,
                "productCategory" => $obj,
                //"listCategory" => Category::getListCategory()
            ]);
        } else {
            return Redirect::route('product.category_attributes.index', $categoryId)->withErrors(["Bản ghi không tồn tại!"]);
        }
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request, $categoryId, $id)
    {
        $params = $request->all();
        $validatorArray = [
            //'category_id' => 'required',
            'name' => 'required',
            'title' => 'required',
            'data_type' => 'required'
        ];

        $validator = Validator::make($request->all(), $validatorArray);
        if ($validator->fails()) {
            $message = $validator->errors();
            return Redirect::route('product.category_attributes.edit', $categoryId, $id)->withErrors([$message->first()]);
        }

        $obj = CategoryAttribute::where("id", $id)->first();
        if ($obj) {
            $obj->category_id = $categoryId;
            $obj->name = $params["name"];
            $obj->title = $params["title"];
            $obj->data_type = $params["data_type"];
            $obj->is_required = isset($params["is_required"]) ? CategoryAttribute::REQUIRED : CategoryAttribute::NOT_REQUIRED;
            $obj->default_value = isset($params["default_value"]) ? $params["default_value"] : null;
            $obj->compare_flg = isset($params["compare_flg"]) ? CategoryAttribute::COMPARE : CategoryAttribute::NOT_COMPARE;
            $obj->save();

            return Redirect::route('product.category_attributes.index', $categoryId);
        } else {
            return Redirect::route('product.category_attributes.index')->withErrors(["Bản ghi không tồn tại!"]);
        }
    }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy($categoryId, $id)
    {
        $obj = CategoryAttribute::where("id", $id)->first();
        if ($obj) {
            $obj->delete();
            return Redirect::route('product.category_attributes.index', $categoryId);
        } else {
            return Redirect::route('product.category_attributes.index', $categoryId)->withErrors(["Bản ghi không tồn tại!"]);
        }
    }
}
