<?php

use Illuminate\Support\Facades\Route;

// Toutes les routes non-API servent la SPA Vue.
Route::get('/{any?}', function () {
    return view('app');
})->where('any', '^(?!api).*$');
