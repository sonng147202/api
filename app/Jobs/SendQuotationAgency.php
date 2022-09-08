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
use App\Mail\QuotationAgency;
use App\Models\Customer;
use App\Models\Agency;
use App\Models\InsuranceQuotation;

class SendQuotationAgency implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels, Queueable;

    protected $agencyId;
    protected $quotationId;
    protected $userId;

    /**
     * SendQuotationAgency constructor.
     * @param $agencyId
     * @param $quotationId
     * @param int $userId
     */
    public function __construct($agencyId, $quotationId, $userId = 0)
    {
        $this->agencyId = $agencyId;
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
            // Get agency info
            $agency = Agency::getDetail($this->agencyId);

            // Get quotation info
            $quotation = InsuranceQuotation::getDetail($this->quotationId);

            if (isset($agency->email) && !empty($agency->email)) {
                // Send email to customer
                Mail::to($agency->email)
                    ->send(new QuotationAgency($this->agencyId, $this->userId, $quotation->product_id, $quotation->product_price, $this->quotationId));
            } else {
                Log::error('[SendQuotation] Error: customer email not found.');
            }
        } catch (\Exception $ex) {
            Log::error($ex->getMessage() . '. File: ' . $ex->getFile() . '. Line: ' . $ex->getLine());
        }
    }
}
