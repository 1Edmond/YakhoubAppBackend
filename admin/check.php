<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$wallet = \App\Models\BusinessSetting::where('type', 'wallet_status')->first();
$loyalty = \App\Models\BusinessSetting::where('type', 'loyalty_point_status')->first();

echo "wallet_status: " . ($wallet ? $wallet->value : 'null') . "\n";
echo "loyalty_point_status: " . ($loyalty ? $loyalty->value : 'null') . "\n";
echo "getWebConfig wallet: " . var_export(getWebConfig('wallet_status'), true) . "\n";
echo "getWebConfig loyalty: " . var_export(getWebConfig('loyalty_point_status'), true) . "\n";
unlink(__FILE__);
