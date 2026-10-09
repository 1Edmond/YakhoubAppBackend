<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Install Routes (Neutralized)
|--------------------------------------------------------------------------
| All installation endpoints have been disabled for security.
|--------------------------------------------------------------------------
*/

Route::any('/', function () {
    abort(404);
});
Route::any('/step1', function () {
    abort(404);
})->name('step1');
Route::any('/step2', function () {
    abort(404);
})->name('step2');
Route::any('/step3/{error?}', function () {
    abort(404);
})->name('step3');
Route::any('/step4', function () {
    abort(404);
})->name('step4');
Route::any('/step5', function () {
    abort(404);
})->name('step5');

Route::any('database_installation', function () {
    abort(404);
})->name('install.db');
Route::any('import_sql', function () {
    abort(404);
})->name('import_sql');
Route::any('force-import-sql', function () {
    abort(404);
})->name('force-import-sql');
Route::any('system_settings', function () {
    abort(404);
})->name('system_settings');
Route::any('purchase_code', function () {
    abort(404);
})->name('purchase.code');
