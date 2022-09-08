<?php

namespace Modules\Product\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Modules\Product\Models\Category;
use Modules\Product\Models\CategoryClass;

class CategoryClassController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index($categoryId)
    {
        $classes = CategoryClass::where('category_id', $categoryId)
            ->where('status', '>', CategoryClass::STATUS_DELETED)
            ->orderBy('order_number')->paginate(15);

        // Get category info
        $category = Category::find($categoryId);

        return view('product::category_class.index', compact('classes', 'category'));
    }

    /**
     *
     */
    public function listCategory()
    {
        // Get list parent category
        $categories = Category::where('status', '>', Category::STATUS_DELETED)
            ->where(function ($query) {
                $query->where('parent_id', 0)->orWhereNull('parent_id');
            })
            ->paginate(15);

        return view('product::category_class.list_category', compact('categories'));
    }

    /**
     * @param $categoryId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create($categoryId)
    {
        // Get category info
        $category = Category::find($categoryId);

        // Get total class
        $total = CategoryClass::getTotalActive($categoryId);

        return view('product::category_class.create', compact('category', 'total'));
    }

    /**
     * @param Request $request
     * @param $categoryId
     * @return $this|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store(Request $request, $categoryId)
    {
        $params = $request->only(['name', 'status', 'order_number']);
        $validatorArray = [
            'name'        => 'required',
            'status'      => 'required'
        ];

        $validator = Validator::make($request->all(), $validatorArray);
        if ($validator->fails()) {
            $message = $validator->errors();
            return redirect(route('product.category_class.create', $categoryId))->withErrors([$message->first()]);
        }

        $params['category_id'] = $categoryId;

        CategoryClass::createClass($params);

        return redirect(route('product.category_class.index', $categoryId));
    }

    /**
     * @param $categoryId
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($categoryId, $id)
    {
        // Get category info
        $category = Category::find($categoryId);

        // Get class info
        $class = CategoryClass::find($id);

        // Get total class
        $total = CategoryClass::getTotalActive($categoryId);

        return view('product::category_class.edit', compact('category', 'class', 'total'));
    }

    /**
     * @param Request $request
     * @param $categoryId
     * @param $id
     * @return $this|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update(Request $request, $categoryId, $id)
    {
        $params = $request->only(['name', 'status', 'order_number']);

        $validatorArray = [
            'name'        => 'required',
            'status'      => 'required'
        ];

        $validator = Validator::make($request->all(), $validatorArray);
        if ($validator->fails()) {
            $message = $validator->errors();
            return Redirect::route('product.category_class.edit', $categoryId, $id)->withErrors([$message->first()]);
        }

        $params['category_id'] = $categoryId;

        if (CategoryClass::updateClass($id, $params)) {
            return redirect(route('product.category_class.index', $categoryId));
        } else {
            return redirect(route('product.category_class.index', $categoryId))->withErrors(["Bản ghi không tồn tại!"]);
        }
    }

    /**
     * @param $categoryId
     * @param $id
     * @return $this|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function destroy($categoryId, $id)
    {
        $obj = CategoryClass::where("id", $id)->first();
        if ($obj) {
            $obj->status = CategoryClass::STATUS_DELETED;
            $obj->save();

            // Update order
            CategoryClass::updateOrderNumber($categoryId);

            return redirect(route('product.category_class.index', $categoryId));
        } else {
            return redirect(route('product.category_class.index', $categoryId))->withErrors(["Bản ghi không tồn tại!"]);
        }
    }
}
