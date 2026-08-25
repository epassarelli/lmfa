<?php

return [
    'default_project' => 'mfa',

    'projects' => [
        'mfa' => [
            'name' => 'Mi Folklore Argentino',
            'drive' => [
                'spreadsheet_url' => 'https://docs.google.com/spreadsheets/d/1kT8VnE83aUKfV0FtZH1sX1MSCI-njzf7K0lbmSFrXYs/edit',
                'sheet_name' => 'Backlog',
                'columns' => [
                    'id' => 'ID',
                    'project' => 'Proyecto',
                    'title' => 'Tarea',
                    'expected_result' => 'Resultado esperado',
                    'owner' => 'Responsable',
                    'eduardo_role' => 'Rol de Eduardo',
                    'owner_next_action' => 'Proxima accion responsable',
                    'delegation_state' => 'Estado delegacion',
                    'definition_of_done' => 'Definicion de terminado',
                    'impact' => 'Impacto',
                    'urgency' => 'Urgencia',
                    'effort_hours' => 'Esfuerzo hs',
                    'dependency_blocker' => 'Dependencia / bloqueo',
                    'blocker_owner' => 'Responsable bloqueo',
                    'status' => 'Estado',
                    'can_schedule' => 'Puede calendarizar',
                    'preferred_window' => 'Ventana preferida',
                    'priority' => 'Prioridad sugerida',
                    'plan_date' => 'Plan fecha',
                    'closing_notes' => 'Notas cierre',
                    'focus_category' => 'Categoría foco',
                ],
                'read_range' => 'A1:AG400',
                'snapshot_path' => 'project/automation/orchestrator/fixtures/mfa_backlog_snapshot.json',
            ],
            'limits' => [
                'max_tasks_per_run' => 1,
                'max_runtime_minutes' => 20,
                'allowed_hours' => [
                    'start' => '06:00',
                    'end' => '23:00',
                ],
            ],
            'eligibility' => [
                'pending_states' => ['Pendiente'],
                'blocked_states' => ['Bloqueada', 'En revision', 'En revisión'],
                'autonomy_labels' => ['IA_AUTONOMA', 'IA_CON_VALIDACION'],
                'priority_order' => ['P1', 'P2', 'P3', 'P4'],
                'allowed_project_fragments' => ['Mi Folklore Argentino'],
            ],
            'paths' => [
                'logs' => 'app/automation/orchestrator/logs',
                'locks' => 'app/automation/orchestrator/locks',
                'reports' => 'app/automation/orchestrator/reports',
                'prompts' => 'project/automation/orchestrator/prompts',
                'legacy_backlog' => 'project/docs/backlog.json',
            ],
            'validation' => [
                'commands' => [],
            ],
            'execution' => [
                'default_driver' => 'fixture',
                'codex_command' => 'codex exec',
                'builtin_evidence_tasks' => [
                    'BL-0011F' => [
                        'summary' => 'El inventario técnico y legacy del portal ya quedó relevado en el repositorio y puede verificarse sin despliegue.',
                        'required_files' => [
                            'project/docs/08_inventario_tecnico_legacy.md',
                            'project/docs/00_estado_actual.md',
                        ],
                        'evidence' => [
                            'Inventario técnico consolidado en project/docs/08_inventario_tecnico_legacy.md.',
                            'Estado actual actualizado con el cierre del inventario local al 2026-08-20.',
                        ],
                    ],
                ],
            ],
        ],
    ],
];
