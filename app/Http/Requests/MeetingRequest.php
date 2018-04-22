<?php

namespace App\Http\Requests;

use Efriandika\LaravelSettings\Facades\Settings;

class MeetingRequest extends Request
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
            'meeting_subject' => 'required',
            'starting_date' => 'required|date_format:"'.Settings::get('date_format').' '.Settings::get('time_format').'"',
            'ending_date' => 'required|date_format:"'.Settings::get('date_format').' '.Settings::get('time_format').'"',
            'responsible_id' => "required",
//            'attendees' => "required",
            'company_attendees' => "required",
//            'staff_attendees' => "required",
            'location' => "required"
        ];
    }

    /**
     * Get the validator instance for the request.
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function getValidatorInstance()
    {
        $this->merge(['attendees' => implode(',', $this->get('attendees', []))]);
        return parent::getValidatorInstance();
    }
}
