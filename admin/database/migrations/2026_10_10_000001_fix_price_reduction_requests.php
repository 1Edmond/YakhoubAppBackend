<?php

use App\Utils\MigrationHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // La négociation par ligne de panier n'a pas encore de commande.
        Schema::table('price_reduction_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('order_id')->nullable()->change();
        });

        Schema::table('price_reduction_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('price_reduction_requests', 'cart_base_discount')) {
                $table->decimal('cart_base_discount', 10, 2)->default(0)->after('product_id');
            }
            if (!Schema::hasColumn('price_reduction_requests', 'applied_at')) {
                $table->timestamp('applied_at')->nullable()->after('round_number');
            }
            if (!Schema::hasColumn('price_reduction_requests', 'applied_reduction')) {
                $table->decimal('applied_reduction', 10, 2)->nullable()->after('applied_at');
            }
        });

        Schema::table('price_reduction_requests', function (Blueprint $table) {
            foreach (['cart_id', 'order_id', 'customer_id', 'vendor_id', 'status'] as $col) {
                if (!Schema::hasIndex('price_reduction_requests', "prr_{$col}_idx")) {
                    $table->index($col, "prr_{$col}_idx");
                }
            }
        });

        MigrationHelper::addForeignKeyIfCompatible('price_reduction_requests', 'customer_id', 'users', 'id', 'cascade');
        MigrationHelper::addForeignKeyIfCompatible('price_reduction_requests', 'order_id', 'orders', 'id', 'cascade');
    }

    public function down(): void
    {
        Schema::table('price_reduction_requests', function (Blueprint $table) {
            $table->dropColumn(['cart_base_discount', 'applied_at', 'applied_reduction']);
        });
    }
};
