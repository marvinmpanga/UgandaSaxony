<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendContactMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'from_name' => ['required', 'string', 'max:120'],
            'from_email' => ['required', 'email', 'max:190'],
            'from_mobile' => ['nullable', 'string', 'max:40'],
            'from_address' => ['nullable', 'string', 'max:190'],
            'from_organisation' => ['nullable', 'string', 'max:190'],
            'message' => ['required', 'string', 'max:5000'],
        ];
    }
}
