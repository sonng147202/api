<?php
namespace Modules\Api\Http\Controllers\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Http\Controllers\ApiController;
use App\Models\Document;
use App\Models\DocumentCategory;

class DocumentController extends ApiController
{
    // API lấy danh sách tất cả danh mục tài liệu
    public function getListDocumentCategories(Request $request)
    {   
        try {
            $data = DocumentCategory::all()->toArray();
        } catch (\Exception $e) {
            $data = [
                'error_msg' => $e->getMessage(),
            ];
        }
        return $this->apiResponse($data);
    }

    // API lấy danh sách danh mục tài liệu con theo parent_id
    public function getListDocumentCategoriesChildren(Request $request)
    {  
        try {
	        $params = $request->all();
            // Check params có tồn tại "parent_id"
            if(isset($params['parent_id'])){
                // "parent_id" có phải là kiểu number
                if (is_numeric($params['parent_id'])) {
                    $data = DocumentCategory::
                        where('parent_id', $params['parent_id'])
                        ->get()
                        ->toArray();
                } else {
                    $data = [
                        'error_msg' => "ID danh mục tài liệu cha phải là số"
                    ];
                }
            } else {
                $data = [
                    'error_msg' => "ID danh mục tài liệu cha không được bỏ trống"
                ];
            }
        } catch (\Exception $e) {
            $data = [
                'error_msg' => $e->getMessage(),
            ];
        }
        return $this->apiResponse($data);
    }

    // API lấy danh sách tài liệu theo danh mục tài liệu theo document_category_id
    public function getListDocumentsByCategory(Request $request)
    {  
        try {
	        $params = $request->all();
            // Check params có tồn tại "document_category_id"
            if(isset($params['document_category_id'])){
                // "document_category_id" có phải là kiểu number
                if (is_numeric($params['document_category_id'])) {
                    $data = Document::
                        where('document_category_id', $params['document_category_id'])
                        ->get()
                        ->toArray();
                } else {
                    $data = [
                        'error_msg' => "ID danh mục tài liệu phải là số"
                    ];
                }
            } else {
                $data = [
                    'error_msg' => "ID danh mục tài liệu không được bỏ trống"
                ];
            }
        } catch (\Exception $e) {
            $data = [
                'error_msg' => $e->getMessage(),
            ];
        }
        return $this->apiResponse($data);
    }
}
