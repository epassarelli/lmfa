<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Soporte del mail de reactivación de usuarios: baja del usuario y registro de cada envío.
 * Aditiva: no modifica ni elimina datos existentes.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'reengagement_opt_out_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->timestamp('reengagement_opt_out_at')->nullable();
            });
        }

        if (! Schema::hasTable('reengagement_mail_sends')) {
            Schema::create('reengagement_mail_sends', function (Blueprint $table) {
                $table->id();
                $table->char('batch_uuid', 36)->index();
                $table->unsignedBigInteger('user_id');
                $table->string('email');
                $table->string('variant', 20); // lapsed | never
                $table->string('status', 20)->default('queued'); // queued | sent | failed | skipped
                $table->timestamp('sent_at')->nullable();
                $table->text('error')->nullable();
                $table->unsignedBigInteger('triggered_by')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'created_at']);
                $table->index(['status', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('reengagement_mail_sends');

        if (Schema::hasColumn('users', 'reengagement_opt_out_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('reengagement_opt_out_at');
            });
        }
    }
};
