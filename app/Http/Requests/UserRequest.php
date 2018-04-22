<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;
use App\Models\User;

class UserRequest extends Request
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
			'first_name' => 'required|min:1|max:50|alpha',
			'last_name' => 'min:1|max:50|alpha',
			'password' => 'required|min:6|confirmed',
			'phone_number' => 'required|regex:/^\d{5,15}?$/',
		];
	}

	public function messages()
	{
		return [
			'phone_number.regex' => 'Phone number can be only numbers',
		];
	}
}
