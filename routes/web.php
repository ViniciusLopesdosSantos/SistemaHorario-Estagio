<?php

use Illuminate\Support\Facades\Route;

Route::get('/{any}', function () {
    return view('app'); // carrega o Vue SPA
})->where('any', '^(?!api).*$');
