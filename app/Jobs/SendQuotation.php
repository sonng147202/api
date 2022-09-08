<?php

namespace App\Jobs;

use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\Quotation;
use App\Models\Customer;
use App\Models\InsuranceQuotation;

class SendQuotation implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels, Queueable;

    protected $customerId;
    protected $quotationId;
    protected $userId;

    /**
     * SendQuotation constructor.
     * @param $customerId
     * @param $quotationId
     */
    public function __construct($customerId, $quotationId, $userId)
    {
        $this->customerId = $customerId;
        $this->quotationId = $quotationId;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            // Get customer info
            $customer = Customer::getDetail($this->customerId);

            // Get quotation info
            $quotation = InsuranceQuotation::getDetail($this->quotationId);

            if (isset($customer->email) && !empty($customer->email)) {
                // Send email to customer
                Mail::to($customer->email)
                    ->send(new Quotation($customer->id, $this->userId, $quotation->product_id, $quotation->product_price, $this->quotationId));
            } else {
                Log::error('[SendQuotation] Error: customer email not found.');
            }
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . '. File: ' . $ex->getFile() . '. Line: ' . $ex->getLine());
        }
    }
}
