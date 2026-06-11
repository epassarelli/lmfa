<?php

namespace App\Http\Requests\Backend;

use App\Models\FolkloreTournament;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFolkloreTournamentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tournament = $this->route('tournament');

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('folklore_tournaments', 'slug')->ignore($tournament?->id)],
            'description' => ['nullable', 'string'],
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'status' => ['required', Rule::in([
                FolkloreTournament::STATUS_DRAFT,
                FolkloreTournament::STATUS_ACTIVE,
                FolkloreTournament::STATUS_FINISHED,
                FolkloreTournament::STATUS_ARCHIVED,
            ])],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'rules' => ['nullable', 'string'],
            'participant_groups' => ['required', 'array'],
            'participant_groups.*' => ['required', 'integer', Rule::exists('folklore_tournament_groups', 'id')],
            'participant_artists' => ['required', 'array'],
            'participant_artists.*' => ['required', 'integer', 'distinct', Rule::exists('interpretes', 'id')],
        ];
    }
}
