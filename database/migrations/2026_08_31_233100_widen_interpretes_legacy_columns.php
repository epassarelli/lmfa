<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
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
