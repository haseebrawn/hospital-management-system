<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class BillingStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = Auth::user();

        /** @var \App\Models\User $user */
        if (! $user) {
            return false;
        }

        return $user->hasAnyRole(['super_admin', 'admin', 'accountant']);
    }

    public function rules(): array
    {
        return [
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.service_name' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'items.*.type' => ['required', 'in:lab,medicine,appointment,other'],
        ];
    }
}

