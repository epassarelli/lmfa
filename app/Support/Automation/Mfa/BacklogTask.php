<?php

namespace App\Support\Automation\Mfa;

class BacklogTask
{
    /**
     * @param  array<string, string>  $row
     */
    public function __construct(
        public readonly int $rowNumber,
        public readonly array $row,
    ) {
    }

    public function id(): string
    {
        return trim((string) ($this->row['ID'] ?? ''));
    }

    public function project(): string
    {
        return trim((string) ($this->row['Proyecto'] ?? ''));
    }

    public function title(): string
    {
        return trim((string) ($this->row['Tarea'] ?? ''));
    }

    public function status(): string
    {
        return trim((string) ($this->row['Estado'] ?? ''));
    }

    public function priority(): string
    {
        return trim((string) ($this->row['Prioridad sugerida'] ?? ''));
    }

    public function notes(): string
    {
        return trim((string) ($this->row['Notas cierre'] ?? ''));
    }

    public function dependencyBlocker(): string
    {
        return trim((string) ($this->row['Dependencia / bloqueo'] ?? ''));
    }

    public function blockerOwner(): string
    {
        return trim((string) ($this->row['Responsable bloqueo'] ?? ''));
    }

    public function delegationState(): string
    {
        return trim((string) ($this->row['Estado delegacion'] ?? ''));
    }

    public function autonomyLabel(): ?string
    {
        if (preg_match('/\b(IA_AUTONOMA|IA_CON_VALIDACION)\b/u', $this->notes(), $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * @return string[]
     */
    public function dependencyTaskIds(): array
    {
        preg_match_all('/\b(?:BL|INI)-\d{4}[A-Z]?\b/u', $this->dependencyBlocker(), $matches);

        return array_values(array_unique($matches[0] ?? []));
    }

    public function isDone(): bool
    {
        return in_array(mb_strtolower($this->status()), ['hecha', 'cerrada'], true);
    }

    public function toArray(): array
    {
        return [
            'row_number' => $this->rowNumber,
            'id' => $this->id(),
            'project' => $this->project(),
            'title' => $this->title(),
            'status' => $this->status(),
            'priority' => $this->priority(),
            'autonomy_label' => $this->autonomyLabel(),
            'dependency_blocker' => $this->dependencyBlocker(),
            'blocker_owner' => $this->blockerOwner(),
            'delegation_state' => $this->delegationState(),
            'notes' => $this->notes(),
        ];
    }
}
