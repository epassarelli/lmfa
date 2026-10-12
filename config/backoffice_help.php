<?php

/*
| Textos de ayuda del backoffice (componentes <x-backend.form-help>, <x-backend.list-help> y
| <x-backend.image-hint>). Escritos para quien no es técnico. Si cambia una regla de acceso,
| publicación u obligatoriedad, actualizar acá y en tests/Feature/BackofficeHelpTest.php.
*/

return [
    // Medidas SUGERIDAS por tipo de contenido. Fuente: project/docs/editorial/image-upload-guide.md y
    // config/image_profiles.php. El tamaño es SUGERIDO (nunca se agranda ni se bloquea por ser chica); lo que
    // sí es obligatorio es el ajuste: el formulario reduce lo que supere el ancho de la variante más grande
    // del perfil ('profile' -> config/image_profiles.php) y pide recortar si la proporción no coincide. El
    // servidor valida tipo y peso (max_mb) y acota el original guardado a ese mismo ancho.
    // min_w/min_h = tamaño mínimo recomendado; ratio = proporción objetivo; mode 'portrait' = artistas.
    'images' => [
        'artist' => ['shape' => 'vertical o cuadrada (retrato)', 'profile' => 'artist', 'min_w' => 800, 'min_h' => 1024, 'ratio' => [3, 4], 'mode' => 'portrait', 'max_mb' => 5, 'tip' => 'Subí un retrato, no un paisaje ancho. Dejá la cara o el grupo en el centro: en algunas tarjetas se recorta cuadrada.'],
        'album' => ['shape' => 'cuadrada (1:1)', 'profile' => 'album', 'min_w' => 800, 'min_h' => 800, 'ratio' => [1, 1], 'mode' => 'cover', 'max_mb' => 5, 'tip' => 'Usá la tapa del disco, sin bordes ni textos agregados.'],
        'festival' => ['shape' => 'horizontal (16:9)', 'profile' => 'festival', 'min_w' => 1600, 'min_h' => 900, 'ratio' => [16, 9], 'mode' => 'cover', 'max_mb' => 5, 'tip' => 'Se muestra grande arriba de la página: evitá texto cerca de los bordes, porque se recorta.'],
        'event' => ['shape' => 'horizontal (16:9)', 'profile' => 'event', 'min_w' => 1600, 'min_h' => 900, 'ratio' => [16, 9], 'mode' => 'cover', 'max_mb' => 5, 'tip' => 'Si es un afiche vertical, agregale margen a los costados para que no se corte.'],
        'news' => ['shape' => 'horizontal (16:9)', 'profile' => 'news_full', 'min_w' => 1600, 'min_h' => 900, 'ratio' => [16, 9], 'mode' => 'cover', 'max_mb' => 5, 'tip' => 'Es la imagen principal de la nota y la que se ve al compartirla.'],
        'recipe' => ['shape' => 'apaisada (4:3)', 'profile' => 'recipe', 'min_w' => 800, 'min_h' => 600, 'ratio' => [4, 3], 'mode' => 'cover', 'max_mb' => 5, 'tip' => 'Una foto del plato terminado, bien iluminada.'],
        'mito' => ['shape' => 'apaisada (4:3)', 'profile' => 'mito', 'min_w' => 900, 'min_h' => 675, 'ratio' => [4, 3], 'mode' => 'cover', 'max_mb' => 5, 'tip' => 'Una ilustración o foto relacionada con la leyenda.'],
        'classified' => ['shape' => 'horizontal (16:9)', 'profile' => 'news_full', 'min_w' => 1600, 'min_h' => 900, 'ratio' => [16, 9], 'mode' => 'cover', 'max_mb' => 2, 'tip' => 'Podés subir varias; cada una hasta 2 MB.'],
    ],
    'sections' => [
        'interprete' => [
            'what' => 'La ficha de un artista o grupo: biografía, foto y datos de contacto. Es la base para cargar sus discos, canciones y noticias.',
            'scope' => [
                'admin' => 'Ves todos los artistas del portal.',
                'member' => 'Ves los artistas que administrás (tus fichas) y los que cargaste vos. Para trabajar sobre otro, pedí vincularte desde Mis fichas.',
                // Colaboradores con el acceso por fichas apagado: el listado es completo (se eligen al cargar discos/canciones).
                'member_open' => 'Ves todos los artistas, para poder elegirlos al cargar discos o canciones, pero solo podés editar los de tus fichas. Para editar otro, pedí vincularte desde Mis fichas.',
            ],
            'required' => ['Intérprete (nombre)', 'Biografía'],
        ],
        'album' => [
            'what' => 'Un disco de un artista, con su tapa, año y canciones.',
            'scope' => [
                'admin' => 'Ves todos los discos.',
                'member' => 'Ves los discos que cargaste vos y los de los artistas que administrás.',
            ],
            'required' => ['Disco (título)', 'Intérprete', 'Año'],
        ],
        'cancion' => [
            'what' => 'Una canción dentro de un disco. Acá no se publica la letra completa: solo los datos de la obra y los créditos.',
            'scope' => [
                'admin' => 'Ves todas las canciones.',
                'member' => 'Ves las canciones que cargaste vos y las de los artistas que administrás.',
            ],
            'required' => ['Canción (título)', 'Intérprete', 'Disco'],
        ],
        'festival' => [
            'what' => 'Un festival o fiesta tradicional: cuándo y dónde se hace, su historia y los artistas que pasan por él.',
            'scope' => [
                'admin' => 'Ves todos los festivales.',
                'member' => 'Ves los festivales que administrás (tus fichas) y los que cargaste vos.',
            ],
            'required' => ['Título', 'Contenido', 'Provincia', 'Mes'],
        ],
        'event' => [
            'what' => 'Un evento con fecha: recital, peña, encuentro. Aparece en la cartelera mientras no haya pasado.',
            'scope' => [
                'admin' => 'Ves todos los eventos.',
                'member' => 'Ves los eventos que cargaste vos y los vinculados a artistas o festivales que administrás.',
            ],
            'required' => ['Título', 'Fecha y hora de inicio', 'Detalles del evento'],
        ],
        'news' => [
            'what' => 'Una nota del portal. Puede estar ligada a artistas, festivales o eventos.',
            'scope' => [
                'admin' => 'Ves todas las noticias.',
                'member' => 'Ves las noticias que cargaste vos y las vinculadas a fichas que administrás.',
            ],
            'required' => ['Título', 'Categoría', 'Cuerpo de la noticia', 'Imagen destacada (al crear)', 'Fecha de publicación'],
        ],
        'mito' => [
            'what' => 'Un mito o leyenda del folklore, contado en tus palabras o con su fuente.',
            'scope' => [
                'admin' => 'Ves todos los mitos.',
                'member' => 'Solo ves los mitos que cargaste vos.',
            ],
            'required' => ['Título', 'Mito'],
        ],
        'comida' => [
            'what' => 'Una receta o comida típica, con ingredientes y preparación.',
            'scope' => [
                'admin' => 'Ves todas las comidas.',
                'member' => 'Solo ves las comidas que cargaste vos.',
            ],
            'required' => ['Título', 'Receta'],
        ],
        'classified' => [
            'what' => 'Un aviso clasificado: se publica después de que el equipo lo revisa.',
            'scope' => [
                'admin' => 'Ves todos los avisos y los que esperan moderación.',
                'member' => 'Ves los avisos que cargaste vos.',
            ],
            'required' => ['Categoría', 'Título', 'Descripción', 'Ubicación', 'Contacto'],
        ],
    ],

    // Cómo se publica lo que carga cada persona.
    'publishing' => [
        'can' => 'Podés publicar directamente: elegí «Publicado» (o «Activo») en el estado y se ve en el portal.',
        'cannot' => 'Lo que cargues queda en borrador hasta que el equipo lo revise y lo publique. Mientras tanto no se ve en el portal.',
    ],
];
