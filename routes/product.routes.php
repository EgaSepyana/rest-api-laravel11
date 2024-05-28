<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\ProductController;
   
Route::controller(ProductController::class)->group(function(){
    Route::post('register', 'register');
    Route::post('login', 'login');
});