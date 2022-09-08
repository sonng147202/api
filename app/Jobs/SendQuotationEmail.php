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
use App\Mail\QuotationEmail;
use App\Models\Customer;
use App\Models\Agency;
use App\Models\InsuranceQuotation;

class SendQuotationEmail implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels, Queueable;

    protected $email;
    protected $quotationId;
    protected $userId;

    /**
     * SendQuotationEmail constructor.
     * @param $email
     * @param $quotationId
     * @param int $userId
     */
    public function __construct($email, $quotationId, $userId = 0)
    {
        $this->email = $email;
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
            // Get quotation info
            $quotation = InsuranceQuotation::getDetail($this->quotationId);

            if (!empty($this->email)) {
                // Send email to customer
                Mail::to($this->email)
                    ->send(new QuotationEmail($this->email, $this->userId, $quotation->product_id, $quotation->product_price, $this->quotationId));
            } else {
                Log::error('[SendQuotationEmail] Error: email not found.');
            }
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . '. File: ' . $ex->getFile() . '. Line: ' . $ex->getLine());
        }
    }
}
