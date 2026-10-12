<?php

return [
    // Si es true, el sidebar y las rutas del backoffice de las secciones listadas abajo quedan
    // limitados a quienes administran al menos una ficha del tipo indicado (administrador: siempre).
    // Apagado por defecto: activar recién cuando el equipo esté listo para el cambio.
    'enabled' => env('BACKOFFICE_ACCESS_BY_ENTITIES', false),

    // Sección => qué la habilita: tipos de ficha administrados ('entities', claves de
    // config/entity_management.php) y/o roles ('roles'). Alcanza con cumplir una condición.
    // Las secciones que no aparecen acá no se restringen. Mis fichas siempre está disponible.
    'sections' => [
        'interpretes' => ['entities' => ['interprete']],
        'discos' => ['entities' => ['interprete']],
        'canciones' => ['entities' => ['interprete']],
        'festivales' => ['entities' => ['festival']],
        'eventos' => ['entities' => ['interprete', 'festival']],
        // Contenido editorial: noticias siguen la lógica de fichas; mitos y comidas son del equipo.
        'noticias' => ['entities' => ['interprete'], 'roles' => ['verificado']],
        'mitos' => ['roles' => ['verificado']],
        'comidas' => ['roles' => ['verificado']],
    ],
];
