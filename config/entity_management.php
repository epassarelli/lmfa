<?php

return [
    // Si es true, el listado de Intérpretes del backoffice se limita para no administradores
    // a las fichas que crearon o administran. Festivales ya se limita por rol.
    'scope_listings' => env('ENTITY_MANAGEMENT_SCOPE_LISTINGS', false),

    // Roles de membresía que otorgan permiso de escritura sobre las entidades de la organización.
    'writer_roles' => ['owner', 'editor'],

    // Fichas del portal que se pueden vincular a una organización desde el backoffice.
    // Para sumar peñas o radios alcanza con registrar una entrada acá.
    'entities' => [
        'interprete' => [
            'model' => \App\Models\Interprete::class,
            'label' => 'Intérprete',
            'name_column' => 'interprete',
            'edit_route' => 'backend.interpretes.edit',
        ],
        'festival' => [
            'model' => \App\Models\Festival::class,
            'label' => 'Festival',
            'name_column' => 'title',
            'edit_route' => 'backend.festivales.edit',
        ],
    ],

    'organization_types' => [
        'artista' => 'Artista',
        'productora' => 'Productora',
        'festival' => 'Festival',
        'peña' => 'Peña',
        'radio' => 'Radio',
        'prensa' => 'Prensa',
        'municipio' => 'Municipio',
        'otro' => 'Otro',
    ],

    'member_roles' => [
        'owner' => 'Responsable (owner)',
        'editor' => 'Editor',
        'member' => 'Miembro (solo lectura)',
    ],

    'member_statuses' => [
        'active' => 'Activo',
        'pending' => 'Pendiente',
        'inactive' => 'Inactivo',
    ],

    'sources' => ['manual', 'email_reply', 'claim', 'import'],

    'verification_methods' => ['email_match', 'code', 'social_proof', 'manual'],
];
