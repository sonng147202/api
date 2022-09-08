<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class InsuranceContractFile extends Model
{
    protected $fillable = ['contract_id', 'file_name', 'status', 'file_info', 'file_path', 'type'];

    /**
     * Relationship
     */
    public function insurance_contract()
    {
        return $this->belongsTo('App\Models\InsuranceContract', 'contract_id');
    }

    /*const TYPE_OWNER = 1;
    const TYPE_BENEFICARY = 2;
    const TYPE_CERTIFICATE = 3;
    const TYPE_OTHER = 4;*/
    const TYPE_OWNER = 'owner';
    const TYPE_BENEFICARY = 'benificary';
    const TYPE_CERTIFICATE = 'certificate';
    const TYPE_OTHER = 'other';

    public function getTypeName() {
        if ($this->status == self::TYPE_OWNER)
            return "owner";
        elseif ($this->type == self::TYPE_BENEFICARY)
            return "benificary";
        elseif ($this->type == self::TYPE_CERTIFICATE)
            return "certificate";
        else
            return "other";
    }


    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    const STATUS_DELETED = -1;

    public function getStatusName() {
        if ($this->status == self::STATUS_ACTIVE)
            return "Đã kích hoạt ";
        elseif ($this->status == self::STATUS_INACTIVE)
            return "Không sử dụng";
        else
            return "Đã xóa";
    }

    public function saveFile($file) {
        $name = explode(".", $file->getClientOriginalName());
        $ext = end($name);
        // get file_path, domain
        // $fileName = $this->getTypeName() . "_" . (10000*microtime(true)) . '_' . $file->getClientOriginalExtension();
        //$fileName = str_slug($fileName);
        // if (!empty($this->contract_id)) {
        //     $savePath = $this->contract_id . DIRECTORY_SEPARATOR . 'certificate' . DIRECTORY_SEPARATOR . $fileName;
        // } else {
        //     $savePath = 'certificate' . DIRECTORY_SEPARATOR . $fileName;
        // }

        // // Create file
        // Storage::disk('contract')->put($savePath, file_get_contents($file->getRealPath()));

        // $params["file_path"] = Storage::disk('contract')->url($savePath);
        // $params['file_info'] = [
        //     'size' => filesize($file),
        //     'type' => $ext
        // ];
        // $this->file_path = $params["file_path"];
        // $this->file_info = json_encode($params["file_info"]);
        $file1Extension = $file->getClientOriginalExtension();
        $fileName = uniqid() . '.' . $file1Extension;
        if (!empty($this->contract_id)) {
            $savePath = $this->contract_id . DIRECTORY_SEPARATOR . 'certificate' . DIRECTORY_SEPARATOR . $fileName;
        } else {
            $savePath = 'certificate' . DIRECTORY_SEPARATOR . $fileName;
        }
        $file->storeAs('public/storage/insurance', $savePath);
        $file_info = [
            'size' => filesize($file),
            'type' => $ext
        ];
        $this->file_info = json_encode($file_info);
        $savePath = '/storage/storage/insurance/'.$savePath;
        $this->file_path = $savePath;
        return $this;
    }

    /**
     * Get list file for contract
     * @param $contractId
     * @return mixed
     */
    public static function getFilesForContract($contractId)
    {
        $cacheKey = 'list_contract_files_' . $contractId;
        $files = Cache::remember($cacheKey, config('insurance.default_cache_time', 60), function () use ($contractId) {
            return self::where('contract_id', $contractId)->get();
        });
        return $files;
    }
}
