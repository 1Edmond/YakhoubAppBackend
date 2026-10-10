<?php

use App\Utils\MigrationHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Décision : le NNI vit UNIQUEMENT dans nni_records.
 * - nni_records gagne seller_id (un vendeur n'est pas un "user")
 * - users.nni_number est migrée puis supprimée
 * Déployer le code (qui n'écrit plus users.nni_number) AVANT d'exécuter cette migration.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('nni_records', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
            if (!Schema::hasColumn('nni_records', 'seller_id')) {
                $table->unsignedBigInteger('seller_id')->nullable()->after('user_id')->index();
            }
        });

        if (Schema::hasColumn('users', 'nni_number')) {
            DB::statement("
                INSERT INTO nni_records (user_id, nni_number, document_type, is_verified, created_at, updated_at)
                SELECT u.id, u.nni_number, 'nni', 0, NOW(), NOW()
                FROM users u
                WHERE u.nni_number IS NOT NULL AND u.nni_number <> ''
                  AND NOT EXISTS (SELECT 1 FROM nni_records n WHERE n.nni_number = u.nni_number)
            ");

            try {
                Schema::table('users', fn (Blueprint $t) => $t->dropUnique(['nni_number']));
            } catch (\Throwable $e) {
                // index déjà absent
            }
            Schema::table('users', fn (Blueprint $t) => $t->dropColumn('nni_number'));
        }

        MigrationHelper::addForeignKeyIfCompatible('nni_records', 'user_id', 'users', 'id', 'cascade');
        MigrationHelper::addForeignKeyIfCompatible('nni_records', 'seller_id', 'sellers', 'id', 'cascade');
    }

    public function down(): void
    {
        if (!Schema::hasColumn('users', 'nni_number')) {
            Schema::table('users', fn (Blueprint $t) => $t->string('nni_number', 20)->nullable()->unique());
        }
    }
};
