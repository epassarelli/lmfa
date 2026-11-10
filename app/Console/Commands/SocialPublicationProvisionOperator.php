<?php

namespace App\Console\Commands;

use App\Models\{AuditLog, User};
use Illuminate\Console\Command;
use Illuminate\Support\Facades\{DB, Hash};
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

class SocialPublicationProvisionOperator extends Command
{
    protected $signature = 'social-publications:provision-operator {--apply : Crea el operador y sus dos permisos; requiere autorización}';
    protected $description = 'Prepara un operador técnico dedicado, sin rol administrador y sin emitir token';
    private const EMAIL = 'mfa-social-automation@localhost.invalid';
    private const NAME = 'MFA automatización social';

    public function handle(): int
    {
        if (!$this->option('apply')) {
            $this->info('Dry-run: operador dedicado sin roles; permisos read/execute. No se modificaron datos ni se emitió token.');
            return self::SUCCESS;
        }
        return DB::transaction(function () {
            $user = User::where('email', self::EMAIL)->lockForUpdate()->first();
            if ($user && ($user->name !== self::NAME || $user->roles()->exists()
                || $user->getAllPermissions()->contains(fn ($p) => !in_array($p->name, ['social-publications:read', 'social-publications:execute'], true))
                || $user->tokens()->get()->contains(fn ($t) => array_diff($t->abilities, ['social-publications:read', 'social-publications:execute'])))) {
                $this->error('La identidad reservada ya existe con otro uso o permisos. No se modificó.');
                return self::FAILURE;
            }
            if (!$user) {
                $user = User::create(['name' => self::NAME, 'email' => self::EMAIL, 'password' => Hash::make(Str::random(80))]);
            }
            foreach (['social-publications:read', 'social-publications:execute'] as $ability) {
                $user->givePermissionTo(Permission::findOrCreate($ability, 'web'));
            }
            AuditLog::create(['entity_type' => 'user', 'entity_id' => $user->id, 'action' => 'social_operator_provisioned',
                'new_values_json' => ['permissions' => ['social-publications:read', 'social-publications:execute']], 'created_at' => now()]);
            $this->info('Operador dedicado preparado. No se emitió token. Configurar en el .env de este mismo entorno:');
            $this->line('SOCIAL_PUBLICATION_OPERATOR_IDS='.$user->id);
            return self::SUCCESS;
        });
    }
}
