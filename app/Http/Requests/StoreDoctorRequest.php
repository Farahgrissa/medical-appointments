<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class StoreDoctorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', User::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            // unique:users vérifie aussi les comptes supprimés (soft delete)
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'specialty' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
        ];
    }
}
