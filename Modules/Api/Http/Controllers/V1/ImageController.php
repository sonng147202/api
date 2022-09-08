<?php
namespace Modules\Api\Http\Controllers\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Http\Controllers\ApiController;
use App\Models\Image;

class ImageController extends ApiController
{
    // API lấy danh sách ảnh
    public function getListImages()
    {
        try {
            $data = Image::all()->toArray();
        } catch (\Exception $e) {
            $data = ['error_msg' => $e->getMessage()];
        }
        return $this->apiResponse($data);
    }

    // API lấy danh sách ảnh theo danh mục ảnh
    public function getListImagesByCategory(Request $request)
    {  
        try {
	        $params = $request->all();
            // Check params có tồn tại "image_category_id"
            if(isset($params['image_category_id'])){
                    $data = Image::
                        where('image_category_id', $params['image_category_id'])
                        ->get()
                        ->toArray();
            } else {
                $data = [
                    'error_msg' => "ID danh mục ảnh không được bỏ trống"
                ];
            }
        } catch (\Exception $e) {
            $data = ['error_msg' => $e->getMessage()];
        }
        return $this->apiResponse($data);
    }
}
