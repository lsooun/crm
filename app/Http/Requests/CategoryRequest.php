<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;
use App\Models\Category;

class CategoryRequest extends Request
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

    public function rules()
    {
        $category = Category::all();
        switch($this->method())
        {
            case 'GET':
            case 'DELETE':
            {
                return [];
            }
            case 'POST':
            {
                return [
                    'name'      => 'required|min:3|unique:categories,name',
                ];
            }
            case 'PUT':
            case 'PATCH':
            {
                $category_id = $this->route('category')->id;
                return [
                    'name'      => 'required|min:3|unique:categories,name,'.$category_id,
                ];
            }
            default:break;
        }
    }
    public function messages()
    {
        return [
            'name.required'    => 'The Category name field is required',
        ];
    }
}
