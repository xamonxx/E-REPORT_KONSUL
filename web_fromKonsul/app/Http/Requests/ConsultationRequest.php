<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ConsultationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $accountId = $this->input('account_id') ?? auth()->user()->account_id;

        return [
            'client_name'        => 'required|string|max:255',
            'phone'              => [
                'required',
                'string',
                'min:9',
                'max:20',
                'regex:/^([0-9\s\-\+\(\)]*)$/',
                Rule::unique('consultations')->where(function ($query) use ($accountId) {
                    return $query->where('account_id', $accountId);
                })->ignore($this->route('consultation'))
            ],
            'province'           => 'nullable|string|max:100',
            'city'               => 'nullable|string|max:100',
            'district'           => 'nullable|string|max:100',
            'address'            => 'nullable|string|max:500',
            'account_id'         => [
                Rule::requiredIf(auth()->user()->isSuperAdmin()),
                'nullable',
                'exists:accounts,id'
            ],
            'needs_category_id'  => 'required|exists:needs_categories,id',
            'status_category_id' => 'required|exists:status_categories,id',
            'notes'              => 'nullable|string|max:1000',
            'consultation_date'  => 'nullable|date',
        ];
    }

    /**
     * Custom validation messages in Bahasa Indonesia.
     */
    public function messages(): array
    {
        return [
            'client_name.required'        => 'Nama klien wajib diisi.',
            'phone.required'              => 'Nomor telepon wajib diisi.',
            'phone.min'                   => 'Nomor telepon minimal berisi 9 karakter.',
            'phone.regex'                 => 'Format nomor telepon tidak valid (hanya mendukung angka dan simbol spesifik).',
            'phone.unique'                => 'Nomor telepon ini sudah terdaftar sebagai Leads pada cabang terkait.',
            'account_id.required'         => 'Akun interior wajib dipilih untuk level Super Admin.',
            'account_id.exists'           => 'Akun interior tidak valid.',
            'needs_category_id.required'  => 'Jenis kebutuhan wajib dipilih.',
            'needs_category_id.exists'    => 'Jenis kebutuhan tidak valid.',
            'status_category_id.required' => 'Status wajib dipilih.',
            'status_category_id.exists'   => 'Status tidak valid.',
        ];
    }
}
