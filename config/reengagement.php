<?php

return [
    // Días de inactividad desde el último ingreso para considerar a un usuario inactivo, y también el
    // intervalo mínimo entre dos mails de reactivación al mismo usuario.
    'inactivity_days' => (int) env('REENGAGEMENT_INACTIVITY_DAYS', 30),

    // Cada cuántos días se avisa en el backoffice que corresponde una nueva tanda.
    'cooldown_days' => (int) env('REENGAGEMENT_COOLDOWN_DAYS', 30),

    // Tope de correos por minuto (límites del proveedor); lo que excede vuelve a la cola.
    'max_per_minute' => (int) env('REENGAGEMENT_MAX_PER_MINUTE', 30),
];
