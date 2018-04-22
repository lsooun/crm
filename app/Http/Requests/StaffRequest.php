<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;
use App\Models\User;
use Efriandika\LaravelSettings\Facades\Settings;

class StaffRequest extends Request
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
        $minimum_characters = Settings::get('minimum_characters');
        $max_upload_file_size = 101024;

        switch ($this->method()) {
            case 'GET':
            case 'DELETE':{
                    return [];
                }
            case 'POST':{
                    return [
                        'first_name' => 'required|min:' . $minimum_characters . '|max:50|alpha',
                        'last_name' => 'min:' . $minimum_characters . '|max:50|alpha',
                        'email' => 'required|email|unique:users,email',
                        'password' => 'required|min:3|max:32|confirmed',
                        'password_confirmation' => 'required|same:password',
                        'user_avatar_file' => 'image|max:' . $max_upload_file_size,
                        'phone_number' => 'required|regex:/^\d{5,15}?$/',
                    ];
                }
            case 'PUT':
            case 'pluck':{
                    if (preg_match("/\/(\d+)$/", $this->url(), $mt)) {
                        $user = User::find($mt[1]);
                    }

                    return [
                        'first_name' => 'required|min:' . $minimum_characters . '|max:50|alpha',
                        'last_name' => 'min:' . $minimum_characters . '|max:50|alpha',
                        'email' => 'required|email|unique:users,email,' . $user->id,
                        'password' => 'min:3|max:32|confirmed',
                        'password_confirmation' => 'same:password',
                        'user_avatar_file' => 'image|max:' . $max_upload_file_size,
                        'phone_number' => 'required|regex:/^\d{5,15}?$/',
                    ];
                }
            default:
                break;
        }

        return [

        ];
    }

	public function messages()
	{
		return [
			'phone_number.regex' => 'Phone number can be only numbers',
		];
	}
}
