<?php

namespace App\Jobs;

use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Mail;
use App\Lib\PushNotification;
use App\Lib\SendSMS;
use App\Models\Notification;
use App\Models\OauthAccessToken;
use App\Mail\MailContractFile;
use App\Models\Customer;
use App\Models\Contract;

class NotifyContractProvideSuccess implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels, Queueable;

    public $contractId;

    /**
     * NotifyContractProvideSuccess constructor.
     * @param $contractId
     */
    public function __construct($contractId)
    {
        $this->contractId = $contractId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $contract = Contract::getDetail($this->contractId);

        if ($contract) {
            $customer = Customer::getDetail($contract->customer_id);
            $sellerEmail = Contract::getSellerEmail($this->contractId);
            $emails = [];

            if (isset($customer->email) && !empty($customer->email)) {
                $emails[] = $customer->email;
            }

            if (!empty($sellerEmail)) {
                $emails[] = $sellerEmail;
            }

            if (!empty($emails)) {
                // Send email to customer, seller
                Mail::to($emails)->queue(new MailContractFile($contract->id));
                Log::info('[InsuranceContractUpdated] Email was send to customer: ' . $customer->email);
            }

            // Send SMS to customer
            $message = 'Hop dong #' . $this->contractId . ' da duoc cap giay chung nhan thanh cong!';
            if (!empty($customer->phone_number)) {
                $service = new SendSMS();
                $service->sendSms($customer->phone_number, $message);
            }

            // Send push to customer
            $customerTokens = OauthAccessToken::getByCustomer($customer->id);

            if (!empty($customerTokens)) {
                $message = 'Hợp đồng #' . $this->contractId . ' đã được cấp giấy chứng nhận thành công!';
                PushNotification::sendMultiDevices($customerTokens, $message, ['contract_id' => $this->contractId, 'member_id'=>$contract->customer_id]);
            }
        }
    }
}
