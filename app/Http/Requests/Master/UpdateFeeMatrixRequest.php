<?php

namespace App\Http\Requests\Master;

use App\Http\Requests\BaseRequest;

class UpdateFeeMatrixRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fee_type_id' => ['required', 'exists:fee_types,id'],
            'class_id' => ['nullable', 'exists:classes,id'],
            'category_id' => ['nullable', 'exists:student_categories,id'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'effective_from' => ['required', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'is_active' => ['sometimes', 'boolean'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
