<?php

return [
    // Casilla que recibe el aviso de cada mensaje nuevo (antes estaba fija en el código).
    'notify_to' => env('CONTACT_NOTIFY_TO', 'mifolkloreargentino@gmail.com'),

    // Correo de confirmación al remitente. Apagarlo evita que el formulario escriba a terceros.
    'send_confirmation' => (bool) env('CONTACT_SEND_CONFIRMATION', true),

    'subjects' => [
        'Consulta General',
        'Sugerencia',
        'Reclamo',
        'Quiero publicitar en el sitio',
        'Me gustaría colaborar con material',
        'Otros',
    ],

    'message_min' => 10,
    'message_max' => 5000,

    // Trampa de tiempo: un envío más rápido que esto (segundos) o más viejo que el máximo se rechaza.
    'min_seconds' => 4,
    'max_age_seconds' => 7200,

    // Límites contra abuso.
    'throttle_per_ip' => '5,10',     // envíos, minutos (middleware throttle)
    'max_per_email_hour' => 3,
    'duplicate_window_minutes' => 10,

    // Más enlaces que esto en el mensaje => se guarda como spam, sin correos.
    'spam_link_limit' => 3,
];
