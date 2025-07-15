<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|in:pending,in_progress,completed',
            'priority_id' => 'required|exists:priority,id',
            'image' => ['nullable', 'file', 'mimetypes:image/jpeg,image/png,image/jpg,image/gif,application/pdf', 'max:2048'], // max en KB
            'reminder_time' => 'nullable|date',
            'reminder_before' => 'nullable|integer|min:0',
            'reminder_unit' => 'nullable|string|in:minutes,hours,days',
        ];
    }

    public function rulesIdDeleted(): array
    {
        return [
            'id' => 'required|number|max:50',
        ];
    }
}
