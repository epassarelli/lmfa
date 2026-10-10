<?php

return [
    // Si es true, el listado de Intérpretes del backoffice se limita para no administradores
    // a las fichas que crearon o administran. Festivales ya se limita por rol.
    'scope_listings' => env('ENTITY_MANAGEMENT_SCOPE_LISTINGS', false),

    // Roles de membresía que otorgan permiso de escritura sobre las entidades de la organización.
    'writer_roles' => ['owner', 'editor'],
];
