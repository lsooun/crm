<?php

namespace App\Http\Requests;


use Efriandika\LaravelSettings\Facades\Settings;

class InvoiceRequest extends Request
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
            'customer_id' => 'required',
            'invoice_date' => 'required|date_format:"'.Settings::get('date_format').'"',
            'due_date' => 'required|date_format:"'.Settings::get('date_format').'"',
            'sales_person_id' => 'required',
            'sales_team_id' => 'required',
            'status' => 'required',
            'grand_total' => 'required',
            'total' => 'required',
            'final_price' => 'required',
            'payment_term' => "required",
            'product_id' => 'required',
            'product_id.*' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'product_id.required' => 'A product is required',
            'product_id*.required' => 'A product is required',
        ];
    }
}
