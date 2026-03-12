<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $repository = app(\App\Repositories\ProductRepository::class);
});
