<?php

namespace App\Http\Requests\Admin\Athlete;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Lang;
use Illuminate\Validation\Rule;

class AthleteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sort' => 'nullable|integer|min:0',
            'activity' => 'required|boolean',

            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'nickname' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date_format:Y-m-d|before_or_equal:today',
            'nationality' => 'nullable|string|max:255',
            'height_cm' => 'nullable|integer|min:50|max:300',
            'reach_cm' => 'nullable|integer|min:50|max:350',
            'stance' => ['nullable', 'string', Rule::in(['orthodox', 'southpaw', 'switch'])],
            'bio' => 'nullable|string|max:65535',
            'short' => 'nullable|string|max:1000',
            'description' => 'nullable|string|max:65535',

            'avatar' => 'nullable|image|mimes:png|max:2048',

            'wins' => 'nullable|integer|min:0',
            'losses' => 'nullable|integer|min:0',
            'draws' => 'nullable|integer|min:0',
            'no_contests' => 'nullable|integer|min:0',
            'wins_by_ko' => 'nullable|integer|min:0',
            'wins_by_submission' => 'nullable|integer|min:0',
            'wins_by_decision' => 'nullable|integer|min:0',

            'images' => ['sometimes', 'array'],
            'images.*.id' => ['nullable', 'integer', 'exists:athlete_images,id'],
            'images.*.order' => ['nullable', 'integer'],
            'images.*.alt' => ['nullable', 'string', 'max:255'],
            'images.*.caption' => ['nullable', 'string', 'max:1000'],
            'images.*.file' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp,gif', 'max:5120'],
            'images.*' => ['array', function ($attribute, $value, $fail) {
                if (empty($value['id']) && empty($value['file'])) {
                    $fail('Изображение должно иметь либо файл, либо ID.');
                }
            }],
        ];
    }

    public function messages(): array
    {
        return Lang::get('admin/requests/AthleteRequest');
    }
}
