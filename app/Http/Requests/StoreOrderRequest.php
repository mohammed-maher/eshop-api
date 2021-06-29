<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'products'=>'required|array|min:1',
            'products.*.product_id'=>'required|numeric|exists:products,id',
            'products.*.weight_id'=>'required|numeric|exists:product_weights,id',
            'products.*.quantity'=>'required|numeric|min:1',
            'is_preorder'=>'sometimes|boolean'
        ];
    }
}
