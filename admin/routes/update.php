<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Update Routes (Neutralized)
|--------------------------------------------------------------------------
| Software update via web interface has been disabled for security.
|--------------------------------------------------------------------------
*/

Route::any('/', function () {
    abort(404);
})->name('index');

Route::any('update-system', function () {
    abort(404);
})->name('update-system');

Route::fallback(function () {
    abort(404);
});
