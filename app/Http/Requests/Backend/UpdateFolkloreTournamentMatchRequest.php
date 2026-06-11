<?php

namespace App\Http\Requests\Backend;

use App\Models\FolkloreTournamentMatch;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFolkloreTournamentMatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var FolkloreTournamentMatch $match */
        $match = $this->route('match');
        $tournamentParticipantIds = $match?->tournament?->participants()->pluck('id')->all() ?? [];

        return [
            'participant_1_id' => ['required', 'integer', Rule::in($tournamentParticipantIds)],
            'participant_2_id' => ['required', 'integer', 'different:participant_1_id', Rule::in($tournamentParticipantIds)],
            'participant_1_votes' => ['required', 'integer', 'min:0'],
            'participant_2_votes' => ['required', 'integer', 'min:0'],
            'winner_participant_id' => ['nullable', Rule::in(array_filter([
                (int) $this->input('participant_1_id'),
                (int) $this->input('participant_2_id'),
            ]))],
            'status' => ['required', Rule::in([
                FolkloreTournamentMatch::STATUS_SCHEDULED,
                FolkloreTournamentMatch::STATUS_VOTING_OPEN,
                FolkloreTournamentMatch::STATUS_CLOSED,
                FolkloreTournamentMatch::STATUS_FINISHED,
                FolkloreTournamentMatch::STATUS_CANCELLED,
            ])],
            'instagram_url' => ['nullable', 'url', 'max:255'],
            'scheduled_at' => ['nullable', 'date'],
            'voting_opens_at' => ['nullable', 'date'],
            'voting_closes_at' => ['nullable', 'date', 'after_or_equal:voting_opens_at'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
