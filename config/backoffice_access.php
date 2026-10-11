<?php

return [
    // Si es true, el sidebar y las rutas del backoffice de las secciones listadas abajo quedan
    // limitados a quienes administran al menos una ficha del tipo indicado (administrador: siempre).
    // Apagado por defecto: activar recién cuando el equipo esté listo para el cambio.
    'enabled' => env('BACKOFFICE_ACCESS_BY_ENTITIES', false),

    // Sección => tipos de ficha (claves de config/entity_management.php 'entities') que la habilitan.
    // Las secciones que no aparecen acá no se restringen. Mis fichas siempre está disponible.
    'sections' => [
        'interpretes' => ['interprete'],
        'discos' => ['interprete'],
        'canciones' => ['interprete'],
        'festivales' => ['festival'],
        'eventos' => ['interprete', 'festival'],
    ],
];
