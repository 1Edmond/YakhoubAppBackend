<?php

use App\Utils\MigrationHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('order_transfer_proofs', function (Blueprint $table) {
            if (!Schema::hasColumn('order_transfer_proofs', 'transfer_group')) {
                $table->uuid('transfer_group')->nullable()->after('order_id')->index();
            }
            if (!Schema::hasColumn('order_transfer_proofs', 'transfer_fee_amount')) {
                $table->decimal('transfer_fee_amount', 15, 2)->default(0);
            }
            if (!Schema::hasColumn('order_transfer_proofs', 'expected_amount')) {
                $table->decimal('expected_amount', 15, 2)->nullable();
            }
            if (!Schema::hasColumn('order_transfer_proofs', 'reviewed_by')) {
                $table->unsignedBigInteger('reviewed_by')->nullable();
                $table->string('reviewed_by_type', 20)->nullable();
                $table->timestamp('reviewed_at')->nullable();
                $table->string('rejection_reason')->nullable();
            }
            $table->index('transaction_reference', 'otp_txref_idx');
            $table->index('status', 'otp_status_idx');
        });

        Schema::table('transfer_accounts', function (Blueprint $table) {
            $table->index(['owner_type', 'owner_id'], 'ta_owner_idx');
            $table->index('is_active', 'ta_active_idx');
        });

        MigrationHelper::addForeignKeyIfCompatible('transfer_accounts', 'operator_id', 'transfer_operators', 'id', 'set null');
    }

    public function down(): void
    {
        Schema::table('order_transfer_proofs', function (Blueprint $table) {
            $table->dropColumn(['transfer_group', 'expected_amount', 'reviewed_by', 'reviewed_by_type', 'reviewed_at', 'rejection_reason']);
        });
    }
};
