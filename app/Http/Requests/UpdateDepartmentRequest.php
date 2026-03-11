<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Department;

class UpdateDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        // admin checks are done by middleware; super_admin allowed too
        return true;
    }

    public function rules(): array
    {
        return [
            'department' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if (! Department::where('name', $value)->orWhere('slug', $value)->exists()) {
                        $fail("The selected department [{$value}] does not exist.");
                    }
                },
            ],
        ];
    }
}
