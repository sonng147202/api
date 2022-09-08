<?php
namespace Modules\Api\Http\Controllers\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ApiController;
use App\Models\Video;

class VideoController extends ApiController
{
    // API lấy chi tiết video
    public function getVideoDetailById(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'video_id' => 'required', 
            ], [
                'video_id.required' => "ID video không được để trống",
            ]);

            if ($validator->fails()) {
                $data = [
                    'error_msg' => $validator->errors()->first()
                ];
            } else {
                $video = Video::find($request->video_id);
                if ($video) {
                    $related_videos = Video::where([
                        ['video_category_id', $video->video_category_id],
                        ['id', '<>', $request->video_id]
                    ]);
    
                    $data['video'] = $video;
    
                    if ($related_videos->exists()) {
                        $videos = $related_videos->limit(5)->get();
                        $data['related_videos'] = $videos;
                    }
                } else {
                    $data = [
                        'error_msg' => "Video không tồn tại"
                    ];
                }
                
            }
        } catch (\Exception $e) {
            $data = [
                'error_msg' => $e->getMessage(),
            ];
        }
        return $this->apiResponse($data);
    }
}
