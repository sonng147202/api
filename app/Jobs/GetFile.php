<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Lib\InsuranceServices\VBI\VBIService;
use App\Models\InsuranceContract;
use App\Models\InsuranceContractFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class GetFile implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels, Queueable;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $id_hd;
    protected $id_dt;
    protected $contract_id;
    protected $service;
    protected $product_code;
    protected $type; // beneficiary | contract

    public function __construct($id_hd, $id_dt, $contract_id, $product_code, $service, $type = 'beneficiary')
    {
        $this->id_hd = $id_hd;
        $this->id_dt = $id_dt;
        $this->contract_id = $contract_id;
        $this->service = $service;
        $this->product_code = $product_code;
        $this->type = $type;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info('[GetFile] Dispatch!');
        // Get service
        switch ($this->service) {
            case 'VBI':
                $service = new VBIService();
                break;
            default:
                $service = new VBIService();
                break;
        }
        $check = $service->saveFile($this->id_hd, $this->id_dt, $this->contract_id, $this->type);
        // if (empty($check)) {
        //     // Try to get file again
        //     $contract = InsuranceContract::where('id', $this->contract_id)->select('get_file_times')->first();
        //     $getFileTimes = $contract->get_file_times;

        //     if ($getFileTimes < config('max_get_file_times')) {
        //         $job = (new GetFile($this->id_hd, $this->id_dt, $this->contract_id, $this->product_code, $service, $this->type))
        //             ->delay(Carbon::now()->addSeconds(config('delay_get_file')));
        //         dispatch($job);

        //         // Update get file times
        //         $contract->get_file_times = $contract->get_file_times + 1;
        //         InsuranceContract::where('id', $this->contract_id)->update(['get_file_times' => $contract->get_file_times]);
        //     }
        // } else {
            $insert_file = new InsuranceContractFile();
            $insert_file->contract_id = $this->contract_id;
            $insert_file->file_name = $check['name'];
            $insert_file->file_path = $check['url'];
            $insert_file->type      = InsuranceContractFile::TYPE_CERTIFICATE;
            $insert_file->save();

            // Clear cache
            Cache::forget('list_contract_files_' . $this->contract_id);

            // Check for notification
            $contract = InsuranceContract::where('id', $this->contract_id)->select('notify_provide_contract')->first();
            Log::info('[InsuranceGetFile Success] Notify provide contract: ' . $contract->notify_provide_contract . '. Cache clear: list_contract_files_' . $this->contract_id);

            if (isset($contract->notify_provide_contract) && $contract->notify_provide_contract == InsuranceContract::NOTIFY_PROVIDE_WAIT) {
                // Update contract notify status
                $contract->notify_provide_contract == InsuranceContract::NOTIFY_PROVIDE_SUCCESS;
                $contract->save();
                Log::info('[InsuranceGetFile] Update contract notify provide contract success');

                //dispatch(new NotifyContractProvideSuccess($this->contract_id));
            }
        // }
    }
}
