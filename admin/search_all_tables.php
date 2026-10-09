<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$tables = DB::select('SHOW TABLES');
$dbName = DB::getDatabaseName();
$tableKey = "Tables_in_" . $dbName;

foreach ($tables as $t) {
    $tableName = $t->$tableKey;
    $columns = DB::getSchemaBuilder()->getColumnListing($tableName);
    foreach ($columns as $col) {
        try {
            $found = DB::table($tableName)
                ->where($col, 'LIKE', '%6am%')
                ->orWhere($col, 'LIKE', '%6valley%')
                ->limit(5)
                ->get();
            if ($found->isNotEmpty()) {
                echo "Table [$tableName] Column [$col]: " . count($found) . " match(es)\n";
                foreach ($found as $f) {
                    $val = is_object($f) ? ($f->$col ?? '') : '';
                    echo "  -> " . substr(strip_tags((string)$val), 0, 100) . "\n";
                }
            }
        } catch (\Throwable $e) {}
    }
}
