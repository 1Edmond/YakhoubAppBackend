<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $rows = DB::table('business_settings')
        ->where('value', 'LIKE', '%6am%')
        ->orWhere('value', 'LIKE', '%6valley%')
        ->orWhere('value', 'LIKE', '%Sixvalley%')
        ->get();

    echo "FOUND IN business_settings (" . count($rows) . "):\n";
    foreach ($rows as $r) {
        echo "TYPE: {$r->type} => VALUE: {$r->value}\n";
    }

    // Also check company_name, company_email, shop_address
    $settings = DB::table('business_settings')
        ->whereIn('type', [
            'company_name', 'company_email', 'company_phone', 'company_web_logo',
            'company_web_logo_png', 'shop_address', 'invoice_settings'
        ])
        ->get();
    echo "\nKEY SETTINGS:\n";
    foreach ($settings as $s) {
        echo "TYPE: {$s->type} => VALUE: {$s->value}\n";
    }

} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
