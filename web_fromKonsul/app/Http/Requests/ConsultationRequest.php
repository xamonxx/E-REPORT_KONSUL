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
            'client_name'        => [
                'required',
                'string',
                'min:2',
                'max:100',
                'regex:/^[\pL0-9\s\-.,\'&()]+$/u' // Allow letters, numbers, spaces, and basic punctuation
            ],
            'phone'              => [
                'required',
                'string',
                'max:30',
                'regex:/^([0-9\s\-\+\(\)]*)$/',
                function ($attribute, $value, $fail) {
                    $digits = preg_replace('/[^0-9]/', '', $value);
                    if (strlen($digits) < 9) {
                        $fail('Nomor telepon minimal harus berisi 9 digit angka.');
                    }
                    if (strlen($digits) > 13) {
                        $fail('Nomor telepon tidak boleh lebih dari 13 digit angka.');
                    }
                },
                Rule::unique('consultations')->where(function ($query) use ($accountId) {
                    return $query->where('account_id', $accountId);
                })->ignore($this->route('consultation'))
            ],
            'province'           => ['nullable', 'string', 'min:3', 'max:100', 'regex:/^[\pL0-9\s\-.,]+$/u'],
            'city'               => ['nullable', 'string', 'min:3', 'max:100', 'regex:/^[\pL0-9\s\-.,]+$/u'],
            'district'           => ['nullable', 'string', 'min:3', 'max:100', 'regex:/^[\pL0-9\s\-.,]+$/u'],
            'address'            => ['nullable', 'string', 'min:5', 'max:500', 'regex:/^[^<>]+$/'], // No HTML tags
            'account_id'         => [
                Rule::requiredIf(auth()->user()->isSuperAdmin()),
                'nullable',
                'exists:accounts,id'
            ],
            'needs_category_id'  => 'required|exists:needs_categories,id',
            'status_category_id' => 'required|exists:status_categories,id',
            'notes'              => ['nullable', 'string', 'min:3', 'max:1000', 'regex:/^[^<>]+$/'], // No HTML tags
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
            'client_name.min'             => 'Nama klien minimal 2 karakter.',
            'client_name.max'             => 'Nama klien maksimal 100 karakter.',
            'client_name.regex'           => 'Nama klien hanya boleh berisi huruf, angka, spasi, dan tanda baca dasar (-.,\'&()).',
            'phone.required'              => 'Nomor telepon wajib diisi.',
            'phone.max'                   => 'Teks nomor telepon terlalu panjang (maksimal 30 karakter).',
            'phone.regex'                 => 'Format nomor telepon tidak valid (hanya mendukung angka dan simbol spesifik).',
            'phone.unique'                => 'Nomor telepon ini sudah terdaftar sebagai Leads pada cabang terkait.',
            'province.regex'              => 'Provinsi mengandung karakter yang tidak diizinkan.',
            'city.regex'                  => 'Kota mengandung karakter yang tidak diizinkan.',
            'district.regex'              => 'Kecamatan mengandung karakter yang tidak diizinkan.',
            'address.regex'               => 'Alamat tidak boleh mengandung tag HTML atau simbol < >.',
            'notes.regex'                 => 'Catatan tidak boleh mengandung tag HTML atau simbol < >.',
            'account_id.required'         => 'Akun interior wajib dipilih untuk level Super Admin.',
            'account_id.exists'           => 'Akun interior tidak valid.',
            'needs_category_id.required'  => 'Jenis kebutuhan wajib dipilih.',
            'needs_category_id.exists'    => 'Jenis kebutuhan tidak valid.',
            'status_category_id.required' => 'Status wajib dipilih.',
            'status_category_id.exists'   => 'Status tidak valid.',
        ];
    }
}
