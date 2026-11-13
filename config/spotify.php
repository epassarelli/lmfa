<?php

// Spec: project/docs/specs/spotify-artistas-discos.md (credenciales solo por entorno)
return [
    'client_id' => env('SPOTIFY_CLIENT_ID'),
    'client_secret' => env('SPOTIFY_CLIENT_SECRET'),
    // Usuario al que se atribuyen los discos propuestos por la tarea programada.
    'proposals_user_id' => env('SPOTIFY_PROPOSALS_USER_ID'),
    // La tarea diaria queda apagada salvo que se habilite explícitamente.
    'schedule_enabled' => (bool) env('SPOTIFY_SCHEDULE_ENABLED', false),
];
