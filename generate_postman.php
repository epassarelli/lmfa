<?php

$entities = [
  'news' => 'Noticias',
  'albums' => 'Albumes',
  'songs' => 'Canciones',
  'foods' => 'Comidas',
  'festivals' => 'Festivales',
  'artists' => 'Interpretes',
  'myths' => 'Mitos'
];

$item = [];

foreach ($entities as $endpoint => $name) {
  $folder = [
    'name' => $name,
    'item' => []
  ];

  // List
  $folder['item'][] = [
    'name' => "Listar $name",
    'request' => [
      'method' => 'GET',
      'header' => [],
      'url' => [
        'raw' => '{{base_url}}/' . $endpoint,
        'host' => ['{{base_url}}'],
        'path' => [$endpoint]
      ]
    ]
  ];

  // Create
  $folder['item'][] = [
    'name' => "Crear $name",
    'request' => [
      'method' => 'POST',
      'header' => [
        ['key' => 'Content-Type', 'value' => 'application/json']
      ],
      'body' => [
        'mode' => 'raw',
        'raw' => "{\n    \"titulo\": \"Ejemplo\",\n    \"publicar\": \"2024-01-01\"\n}"
      ],
      'url' => [
        'raw' => '{{base_url}}/' . $endpoint,
        'host' => ['{{base_url}}'],
        'path' => [$endpoint]
      ]
    ]
  ];

  // Show
  $folder['item'][] = [
    'name' => "Ver $name",
    'request' => [
      'method' => 'GET',
      'header' => [],
      'url' => [
        'raw' => '{{base_url}}/' . $endpoint . '/1',
        'host' => ['{{base_url}}'],
        'path' => [$endpoint, '1']
      ]
    ]
  ];

  // Update
  $folder['item'][] = [
    'name' => "Actualizar $name",
    'request' => [
      'method' => 'PUT',
      'header' => [
        ['key' => 'Content-Type', 'value' => 'application/json']
      ],
      'body' => [
        'mode' => 'raw',
        'raw' => "{\n    \"titulo\": \"Actualizado\"\n}"
      ],
      'url' => [
        'raw' => '{{base_url}}/' . $endpoint . '/1',
        'host' => ['{{base_url}}'],
        'path' => [$endpoint, '1']
      ]
    ]
  ];

  // Delete
  $folder['item'][] = [
    'name' => "Eliminar $name",
    'request' => [
      'method' => 'DELETE',
      'header' => [],
      'url' => [
        'raw' => '{{base_url}}/' . $endpoint . '/1',
        'host' => ['{{base_url}}'],
        'path' => [$endpoint, '1']
      ]
    ]
  ];

  $item[] = $folder;
}

$collection = [
  'info' => [
    'name' => 'MFA Internal API',
    'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json'
  ],
  'item' => $item,
  'auth' => [
    'type' => 'bearer',
    'bearer' => [
      [
        'key' => 'token',
        'value' => '{{token}}',
        'type' => 'string'
      ]
    ]
  ],
  'variable' => [
    [
      'key' => 'base_url',
      'value' => 'http://localhost/api/v1',
      'type' => 'string'
    ],
    [
      'key' => 'token',
      'value' => '',
      'type' => 'string'
    ]
  ]
];

echo json_encode($collection, JSON_PRETTY_PRINT);
