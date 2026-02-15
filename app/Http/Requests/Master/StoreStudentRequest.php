<?php

namespace App\Http\Requests\Master;

use App\Http\Requests\BaseRequest;

class StoreStudentRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nis' => ['required', 'string', 'max:20', 'unique:students,nis'],
            'nisn' => ['nullable', 'string', 'max:20', 'unique:students,nisn'],
            'name' => ['required', 'string', 'max:255'],
            'class_id' => ['required', 'exists:classes,id'],
            'category_id' => ['required', 'exists:student_categories,id'],
            'gender' => ['required', 'in:L,P'],
            'birth_date' => ['nullable', 'date'],
            'birth_place' => ['nullable', 'string', 'max:100'],
            'parent_name' => ['nullable', 'string', 'max:255'],
            'parent_phone' => ['nullable', 'string', 'max:20'],
            'parent_whatsapp' => ['nullable', 'regex:/^628\d{7,15}$/'],
            'address' => ['nullable', 'string'],
            'status' => ['required', 'in:active,graduated,dropout,transferred'],
            'enrollment_date' => ['required', 'date'],
        ];
    }
}
