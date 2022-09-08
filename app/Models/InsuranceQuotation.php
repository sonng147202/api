<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Emails\QuotationEmail;
use App\Emails\QuotationEmailHtml;
use App\Lib\InsuranceHelper;
use Modules\Product\Models\Product;
use App\Models\MailQueue;

class InsuranceQuotation extends Model
{
    protected $guarded = [];

    public function insurance_type()
    {
        return $this->belongsTo('App\Models\InsuranceType');
    }

    public function product()
    {
        return $this->belongsTo('Modules\Product\Models\Product', 'product_id');
    }

    public function customer()
    {
        return $this->belongsTo('App\Models\Customer', 'customer_id');
    }

    /**
     * @param array $filter
     * @param $page
     * @param int $page_size
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getList($filter = [], $page, $page_size = 10)
    {
        $query = self::with('insurance_type', 'product', 'customer')->offset(($page - 1) * $page_size)
            ->orderBy('created_at', 'desc');

        if (isset($filter['user_id']) && !empty($filter['user_id'])) {
            // Get list quotation by user
            $query->where('create_type', 0)->where('create_id', $filter['user_id']);
        }

        if (isset($filter['customer_ids']) && is_array($filter['customer_ids'])) {
            $query->whereIn('customer_id', $filter['customer_ids']);
        }

        return $query->paginate($page_size, ['*'], 'page', $page);
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getDetail($id)
    {
        return self::with('insurance_type')->where('id', $id)->first();
    }

    /**
     * @param $quotationId
     * @return bool
     */
    public static function isExists($quotationId)
    {
        return self::where('id', $quotationId)->exists();
    }

    /**
     * Create quotation, then send quotation to email
     */
    public static function createQuotation($insuranceTypeId, $params, $filterMoreData = [], $extraProducts = [], $extraFees = '')
    {
        // Get create id
        $createId = 0;
        if (Auth::user()) {
            $createId = Auth::user()->id;
        }
        // Create quotation
        $quotation = self::create([
            'insurance_type_id' => $insuranceTypeId,
            'filter_data'       => is_array($params['filter_data']) ? json_encode($params['filter_data']) : $params['filter_data'],
            'filter_conditions' => isset($params['conditions']) ? json_encode($params['conditions']) : '',
            'filter_more_data'  => $filterMoreData,
            'price_types'       => isset($params['price_type']) && !empty($params['price_type']) ? json_encode($params['price_type']) : '',
            'create_id'         => $createId,
            'receive_email'     => isset($params['email']) ? $params['email'] : '',
            'customer_id'       => isset($params['customer_id']) ? $params['customer_id'] : 0,
            'agency_id'         => isset($params['agency_id']) ? $params['agency_id'] : 0,
            'product_id'        => $params['product_id'],
            'product_price_id'  => isset($params['product_price_id']) ? (int)$params['product_price_id'] : 0,
            'product_code'      => isset($params['product_code']) ? $params['product_code'] : '',
            'product_price'     => isset($params['product_price']) ? $params['product_price'] : '',
            'main_fee'          => isset($params['use_fees']['main_fee']) ? $params['use_fees']['main_fee'] : (isset($params['product_price']) ? $params['product_price'] : 0),
            'extra_products'    => $extraProducts,
            'extra_product_filter_data' => isset($params['extra_product_filter_data']) ? json_encode($params['extra_product_filter_data']) : '',
            'extra_fees'        => !empty($extraFees) ? json_encode($extraFees) : '',
            'extra_fee_attributes' => isset($params['extra_fee_attributes']) ? json_encode($params['extra_fee_attributes']) : ''
        ]);

        if (array_key_exists('quotation_content_receive', $params)) {
            //just send email with custom content
            self::sendQuotationEmailCustomContent($quotation, $params);
        } else {
            self::handleQuotationSendEmail($quotation);
        }

        return $quotation;
    }

    /**
     * Data send to quotation default email template
     */
    public static function emailDefaultQuotation($postData)
    {
        // Get sender user
        $user = Auth::user();
        $product = Product::getProduct($postData['product_id']);
        $price = $postData['product_price'];
        $quotationId = '';
        $productName = $postData['product_code'];
        $customer = Customer::select('email')->where('id', $postData['customer_id'])->first();
        $email = '';
        if (!empty($customer)) {
            $email = $customer->email;
        }
        return compact('user', 'product', 'price', 'productName', 'quotationId', 'email');
    }

    /**
     * Send email quotation has custom content
     */
    public static function sendQuotationEmailCustomContent($quotation, $params)
    {
        $customerId = $quotation->customer_id;
        $customer = Customer::select('email')->where('id', $customerId)->first();
        if (!empty($customer)) {
            $email = $customer->email;
            $content = $params['quotation_content_receive'];
            $contentAddQuotationId = str_replace('quotation/create?id=', 'quotation/create?id='.$quotation->id, $content);
            $body = ['content'=> $contentAddQuotationId];

            MailQueue::saveMailToQueue([
                'send_to' => json_encode([$email]),
                'sender' =>  env('MAIL_FROM_NAME').' <'.env('MAIL_FROM_ADDRESS').'>',
                'subject' => (new QuotationEmailHtml($body))->subjectEmail(),
                'variable' => json_encode([
                    'body' => ['content' => $body['content']],
                ]),
                'templete' => 'insurance::emails.quotation_email_content_html'
            ]);
//            Mail::to($email)->queue(new QuotationEmailHtml($body));
        }
    }

    /**
     * Send email after create quotation
     */
    public static function handleQuotationSendEmail($quotation)
    {
        if (!empty($quotation->customer_id)) {
            CustomerActivity::createActivity(
                $quotation->customer_id,
                'Báo giá #' . $quotation->id . ' đã được gửi cho khách hàng.',
                1,
                $quotation->id
            );

            // Send quotation to customer
            InsuranceHelper::sendQuotation($quotation->id, $quotation->customer_id);
        }

        if (!empty($quotation->agency_id)) {
            // Send quotation to agency
            InsuranceHelper::sendQuotationAgency($quotation->id, $quotation->agency_id);
        }

        if (!empty($quotation->receive_email)) {
            // Send quotation to agency
            InsuranceHelper::sendQuotationEmail($quotation->id, $quotation->receive_email);
        }
    }
}
