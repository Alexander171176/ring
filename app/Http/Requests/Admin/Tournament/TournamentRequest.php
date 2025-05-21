<?php

namespace App\Http\Requests\Admin\Tournament;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Lang;
use Illuminate\Validation\Rule;

class TournamentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sort' => ['nullable', 'integer', 'min:0'],
            'activity' => ['required', 'boolean'],
            'parent_tournament_id' => ['nullable', 'integer', 'exists:tournaments,id'],
            'type' => ['nullable', 'string', Rule::in(['boxing_bout', 'mma_tournament', 'press_conference'])],
            'name' => ['required', 'string', 'max:255'],
            'tournament_date_time' => ['required', 'date', 'after_or_equal:2000-01-01'],
            'status' => ['required', 'string', Rule::in(['scheduled', 'live', 'completed', 'postponed', 'cancelled'])],
            'venue' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'short' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'weight_class_name' => ['nullable', 'string', 'max:255'],
            'rounds_scheduled' => ['nullable', 'integer', 'min:1', 'max:12'],
            'is_title_fight' => ['nullable', 'boolean'],
            'winner_id' => ['nullable', 'integer', 'exists:athletes,id'],
            'method_of_victory' => ['nullable', 'string', 'max:255'],
            'round_of_finish' => ['nullable', 'integer', 'min:1', 'max:12'],
            'time_of_finish' => ['nullable', 'string', 'max:255'],
            'details' => ['nullable', 'string'],
            'is_main_card_event' => ['nullable', 'boolean'],

            'images'             => ['nullable','array'],
            'images.*.id'        => [
                'nullable','integer',
                Rule::exists('tournament_images','id'),
                Rule::prohibitedIf(fn() => $this->isMethod('POST')),
            ],
            'images.*.order'     => ['nullable','integer','min:0'],
            'images.*.alt'       => ['nullable','string','max:255'],
            'images.*.caption'   => ['nullable','string','max:255'],
            'images.*.file'      => [
                'nullable',
                'required_without:images.*.id',
                'file',
                'image',
                'mimes:jpeg,jpg,png,gif,svg,webp',
                'max:10240',
            ],

            'deletedImages'      => ['sometimes','array'],
            'deletedImages.*'    => ['integer','exists:tournament_images,id'],
        ];
    }

    public function messages(): array
    {
        return Lang::get('admin/requests/TournamentRequest');
    }
}
