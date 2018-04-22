<?php

namespace App\Http\Requests;

use App\Models\Company;
use Efriandika\LaravelSettings\Facades\Settings;

class CompanyRequest extends Request {
  /**
   * Determine if the user is authorized to make this request.
   *
   * @return bool
   */
  public function authorize() {
    return true;
  }

  /**
   * Get the validation rules that apply to the request.
   *
   * @return array
   */
  public function rules() {
    $minimum_characters = Settings::get('minimum_characters');
    $max_upload_file_size = 1000000;
    $allowed_extensions = Settings::get('allowed_extensions');

    switch ($this->method()) {
      case 'GET':
      case 'DELETE':
        {
          return [];
        }
      case 'POST':
        {
//                return 'gfggf';
          return [
            'name' => 'required|min:' . $minimum_characters . '|max:50',
//                    'email' => 'required|email',
            'address' => 'required',
            'country_id' => 'required',
            'state_id' => 'required',
            'city_id' => 'required',
//                    'sales_team_id' => 'required',
//                    'main_contact_person' => 'required',
            'phone' => 'required|regex:/^\d{5,15}?$/',
            'website' => 'required|url',
//                    'mobile' => 'required|regex:/^\d{5,15}?$/',
            'fax' => 'regex:/^\d{5,15}?$/',
            'company_avatar_file' => 'image|max:' . $max_upload_file_size,
          ];
        }
      case 'PUT':
      case 'pluck':
        {
          if (preg_match("/\/(\d+)$/", $this->url(), $mt))
            $company = Company::find($mt[1]);
          return [
            'name' => 'required|min:' . $minimum_characters . '|max:50',
            'phone' => 'required|regex:/^\d{5,15}?$/',
            'address' => 'required',
            'country_id' => 'required',
            'state_id' => 'required',
            'city_id' => 'required',
//                    'mobile' => 'required|regex:/^\d{5,15}?$/',
            'fax' => 'regex:/^\d{5,15}?$/',
            'website' => 'required|url',
//                    'main_contact_person' => 'required',
//                    'sales_team_id' => 'required',
//                    'email' => 'required|email|unique:companies,email,' . $company->id,
            'company_avatar_file' => 'image|max:' . $max_upload_file_size,
          ];
        }
      default:
        break;
    }

    return [

    ];
  }

  /**
   * Get the validator instance for the request.
   *
   * @return \Illuminate\Contracts\Validation\Validator
   */
  protected function getValidatorInstance() {
    //$this->merge(['ip_address' => $this->ip()]);
    //$this->merge(['register_time' => strtotime(date('d F Y g:i a'))]);
    return parent::getValidatorInstance();
  }

  public function messages() {
    return [
      'website.required' => 'The company website field is required.',
      'phone.required' => 'The phone number can be only numbers',
      'mobile.regex' => 'Mobile number can be only numbers',
      'fax.regex' => 'Fax number can be only numbers',
      'country_id.required' => 'The country field is required.',
      'state_id.required' => 'The state field is required.',
      'city_id.required' => 'The city field is required.'
    ];
  }
}
