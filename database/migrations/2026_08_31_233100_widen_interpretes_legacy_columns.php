<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL vuelve a validar toda la fila durante el ALTER TABLE. Algunas
        // instalaciones legacy conservan timestamps cero, aceptados por
        // versiones antiguas pero rechazados cuando NO_ZERO_DATE está activo.
        // Laravel define estos timestamps como nullable, por lo que NULL es la
        // representación correcta de una fecha histórica desconocida.
        DB::statement(
            "UPDATE interpretes
                SET created_at = NULL
                WHERE created_at IS NOT NULL
                  AND created_at < '1000-01-01 00:00:00'"
        );

        DB::statement(
            "UPDATE interpretes
                SET updated_at = NULL
                WHERE updated_at IS NOT NULL
                  AND updated_at < '1000-01-01 00:00:00'"
        );

        DB::statement(
            'ALTER TABLE interpretes
                MODIFY interprete VARCHAR(255) NOT NULL,
                MODIFY slug VARCHAR(255) NULL,
                MODIFY telefono VARCHAR(255) NULL,
                MODIFY correo VARCHAR(255) NULL,
                MODIFY facebook VARCHAR(255) NULL,
                MODIFY youtube VARCHAR(255) NULL,
                MODIFY twitter VARCHAR(255) NULL,
                MODIFY instagram VARCHAR(255) NULL'
        );
    }

    public function down(): void
    {
        // Intencionalmente no se vuelven a reducir los largos legacy:
        // un rollback no debe truncar URLs, correos o nombres válidos creados
        // después de este upgrade. El cambio es compatible hacia atrás.
    }
};
