<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Spatie\Permission\Models\Role;

class AssignRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization is handled by route middleware (EnsureDepartmentAdmin or role)
        return true;
    }

    public function rules(): array
    {
        return [
            'role' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    // ensure role exists for guard 'api'
                    if (! Role::where(['name' => $value, 'guard_name' => 'api'])->exists()) {
                        $fail("The selected role [{$value}] does not exist.");
                    }
                },
            ],
        ];
    }
}
