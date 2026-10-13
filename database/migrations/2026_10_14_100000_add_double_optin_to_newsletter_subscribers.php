<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Doble confirmación del newsletter. Aditiva: no modifica filas existentes. Los suscriptores
     * previos quedan `active` pero sin `confirmed_at`, por lo que no reciben envíos hasta confirmar.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE newsletter_subscribers MODIFY status ENUM('pending','active','unsubscribed','bounced') NOT NULL DEFAULT 'pending'");
        }

        Schema::table('newsletter_subscribers', function (Blueprint $table) {
            $table->timestamp('consented_at')->nullable()->after('source');
            $table->timestamp('confirmed_at')->nullable()->after('consented_at');
            $table->string('consent_ip_hash', 64)->nullable()->after('confirmed_at');
            $table->timestamp('last_confirmation_sent_at')->nullable()->after('consent_ip_hash');
        });
    }

    public function down(): void
    {
        Schema::table('newsletter_subscribers', function (Blueprint $table) {
            $table->dropColumn(['consented_at', 'confirmed_at', 'consent_ip_hash', 'last_confirmation_sent_at']);
        });

        if (DB::getDriverName() === 'mysql') {
            // Los `pending` no existían: pasan a `unsubscribed` (no recibían envíos) antes de quitar el valor.
            DB::table('newsletter_subscribers')->where('status', 'pending')->update(['status' => 'unsubscribed']);
            DB::statement("ALTER TABLE newsletter_subscribers MODIFY status ENUM('active','unsubscribed','bounced') NOT NULL DEFAULT 'active'");
        }
    }
};
