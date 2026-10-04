<?php

namespace App\Http\Requests;

use App\Models\Publisher;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PublisherRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $publisher = $this->route('publisher');
        return [
            'name' => ['required', 'string', Rule::unique('publishers', 'name')->ignore($publisher)],
            'address' => 'nullable|string',
            'phone' => 'nullable|string',
            'email' => ['nullable', 'email', Rule::unique('publishers', 'email')->ignore($publisher)]
        ];
    }
}
