<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AnalyticsReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        $periodType = $this->input('period_type', 'monthly');
        $year = (int) ($this->input('year') ?: now()->year);

        $payload = [
            'period_type' => $periodType,
            'year' => $year,
        ];

        if ($periodType === 'monthly' && ! $this->filled('month')) {
            $payload['month'] = now()->month;
        }

        if ($periodType === 'weekly' && ! $this->filled('week_date')) {
            $payload['week_date'] = now()->toDateString();
        }

        $this->merge($payload);
    }

    public function rules(): array
    {
        return [
            'account' => ['nullable', 'integer', 'exists:accounts,id'],
            'period_type' => ['required', Rule::in(['weekly', 'monthly', 'yearly'])],
            'week_date' => ['nullable', 'date'],
            'month' => ['nullable', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'between:2020,' . (now()->year + 1)],
        ];
    }

    public function messages(): array
    {
        return [
            'account.exists' => 'Akun yang dipilih tidak valid.',
            'period_type.required' => 'Tipe periode wajib dipilih.',
            'period_type.in' => 'Tipe periode laporan tidak valid.',
            'week_date.date' => 'Tanggal acuan minggu tidak valid.',
            'month.between' => 'Bulan harus berada di antara 1 sampai 12.',
            'year.between' => 'Tahun yang dipilih tidak valid.',
        ];
    }
}
