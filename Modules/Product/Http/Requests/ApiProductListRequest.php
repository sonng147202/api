<?php

namespace Modules\Product\Http\Requests;

use Modules\Core\Http\Requests\ApiFormRequest;

class ApiProductListRequest extends ApiFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'insurance_type_id' => 'required',
            'filter_data'       => 'required'
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }
}
