<?php

namespace Modules\Core\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Modules\Core\Http\Controllers\ApiController;
use Modules\Core\Models\Image;
use Modules\Insurance\Models\InsuranceAgency;
use Modules\Insurance\Models\Customer;
use Modules\Insurance\Models\TypeSupportCustemerImage;

class ImageController extends ApiController
{
    /**
     * API upload image
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function upload(Request $request)
    {
        // $file = $request->file('file');
        // get file_path, domain
        // $fileName = (10000*microtime(true)) . '_' . str_slug($fileName) . '.' . $fileExt;
        // $savePath
        // Create file
        // if (Storage::disk('files')->put($savePath, file_get_contents($file->getRealPath()))) {
            // Create image record
            // dd($savePath);
        // $image = Image::createImage($image_name);
            // return $this->successResponse(['image' => $image]);
        // } else {
        //     return $this->errorResponse([]);
        // }

        // Api update avatar, img_code_before, img_code_after, tạo 1 bản ghi ở bảng Image
        $data = $request->all();
        $img_code_after = '';
        $img_code_before = '';
        $fileName1 = '';
        if(isset($data['avatar'])){
            $file = $request->file('avatar');
            $ext = $file->getClientOriginalExtension();
            $fileName = time().$file->getFilename().'.'.$ext;
        }
        // dd($data['type']);
        if(!isset($data['type'])){
            return \response()->json([
                'result' => 0,
                'messages' => 'fail',
                'data' => 'input type user: 0 - customer, 1 - agency',
            ]);
        }
        if(empty($data['id'])){
            return \response()->json([
                'result' => 0,
                'messages' => 'fail',
                'data' => 'input id agency - customer',
            ]);
        }
        if(empty($data['avatar']) && empty($data['img_code_after']) && empty($data['img_code_before'])){
            return \response()->json([
                'result' => 0,
                'messages' => 'fail',
                'data' => 'input your image',
            ]);
        }
        if ($request->hasFile('avatar')) {
            $file1Extension = $request->file('avatar')->getClientOriginalExtension();
            $fileName1 = uniqid() . '.' . $file1Extension;
            $request->file('avatar')->storeAs('public', $fileName1);
            // crea image record
            $image_avatar_name = "/storage/".$fileName1;
            $image_avatar = Image::createImage($image_avatar_name);
        }
        if ($request->hasFile('img_code_after')) {
            $file1Extension = $request->file('img_code_after')->getClientOriginalExtension();
            $img_code_after = uniqid() . '.' . $file1Extension;
            $request->file('img_code_after')->storeAs('public', $img_code_after);
            $image_code_after_name = "/storage/".$img_code_after;
            $image_img_code_after = Image::createImage($image_code_after_name);
        }
        if ($request->hasFile('img_code_before')) {
            $file1Extension = $request->file('img_code_before')->getClientOriginalExtension();
            $img_code_before = uniqid() . '.' . $file1Extension;
            $request->file('img_code_before')->storeAs('public', $img_code_before);
            $image_code_before_name = "/storage/".$img_code_before;
            $image_img_code_before = Image::createImage($image_code_before_name);

        }
        if(($data['type'] == 0 )){
            Customer::changeAvatar($data, $fileName1, $img_code_after, $img_code_before);
            return \response()->json([
                'result'=> 1,
                'messages'=>'success',
                'avatar'=> isset($fileName1) ? $fileName1 : '',
                'avatar_url'=> isset($image_avatar['image_url']) ? $image_avatar['image_url'] : '',
                'img_code_after' => isset($img_code_after) ? $img_code_after : '',
                'img_code_after_url' => isset($image_img_code_after['image_url']) ? $image_img_code_after['image_url'] : '',
                'img_code_before' => isset($img_code_before) ? $img_code_before : '',
                'img_code_before_url' => isset($image_img_code_before['image_url']) ? $image_img_code_before['image_url'] : '',
            ]);
        }
        else{
            InsuranceAgency::changeAvatar($data, $fileName1, $img_code_after, $img_code_before);
            return \response()->json([
                'result'=> 1,
                'messages'=>'success',
                'avatar'=> isset($fileName1) ? $fileName1 : '',
                'avatar_url'=> isset($image_avatar['image_url']) ? $image_avatar['image_url'] : '',
                'img_code_after' => isset($img_code_after) ? $img_code_after : '',
                'img_code_after_url' => isset($image_img_code_after['image_url']) ? $image_img_code_after['image_url'] : '',
                'img_code_before' => isset($img_code_before) ? $img_code_before : '',
                'img_code_before_url' => isset($image_img_code_before['image_url']) ? $image_img_code_before['image_url'] : '',
            ]);
        }
    }


    public function uploadImageSupport(Request $request)
    {
        $data = $request->all();
        $img_code_after = '';
        $img_code_before = '';
        $fileName1 = '';
        $url = array();
        $check =false;
        if ($request->hasFile('image_1')) {
            $file1Extension = $request->file('image_1')->getClientOriginalExtension();
            $img_code_after = uniqid() . '.' . $file1Extension;
            $request->file('image_1')->storeAs('public', $img_code_after);
            $image_code_after_name = "/storage/".$img_code_after;
            $image_img_code_after = Image::createImage($image_code_after_name);
            $check = true;
            array_push($url, $image_code_after_name);
        }
        if ($request->hasFile('image_2')) {
            $file1Extension = $request->file('image_2')->getClientOriginalExtension();
            $img_code_before = uniqid() . '.' . $file1Extension;
            $request->file('image_2')->storeAs('public', $img_code_before);
            $image_code_before_name = "/storage/".$img_code_before;
            $image_img_code_before = Image::createImage($image_code_before_name);
            array_push($url, $image_code_before_name);
            $check = true;
        }
        if ($request->hasFile('image_3')) {
            $file1Extension = $request->file('image_3')->getClientOriginalExtension();
            $img_3 = uniqid() . '.' . $file1Extension;
            $request->file('image_3')->storeAs('public', $img_3);
            $img_3_name = "/storage/".$img_3;
            $img_3create = Image::createImage($img_3_name);
            array_push($url, $img_3_name);
            $check = true;
        }
        if ($request->hasFile('image_4')) {
            $file1Extension = $request->file('image_4')->getClientOriginalExtension();
            $img_4 = uniqid() . '.' . $file1Extension;
            $request->file('image_4')->storeAs('public', $img_4);
            $img_4_name = "/storage/".$img_4;
            $img_4create = Image::createImage($img_4_name);
            array_push($url, $img_4_name);
            $check = true;
        }
        if($check){
            return \response()->json([
                'result'=> 1,
                'messages'=>'success',
                'image_1' => isset($img_code_after) ? $img_code_after : '',
                'image_1_url' => isset($image_img_code_after['image_url']) ? $image_img_code_after['image_url'] : '',
                'image_2' => isset($img_code_before) ? $img_code_before : '',
                'image_2_url' => isset($image_img_code_before['image_url']) ? $image_img_code_before['image_url'] : '',
                'image_3' => isset($img_3) ? $img_3 : '',
                'image_3_url' => isset($img_3create['image_url']) ? $img_3create['image_url'] : '',
                'image_4' => isset($img_4) ? $img_4 : '',
                'image_4_url' => isset($img_4create['image_url']) ? $img_4create['image_url'] : '',
                'image_url_total' => \GuzzleHttp\json_encode($url)
            ]);
        }else{
            return \response()->json([
                'result' => 0,
                'messages' => 'fail',
                'data' => 'input your image_1 , image_2 , image_3 , image_4',
            ]);
        }
    }

}
