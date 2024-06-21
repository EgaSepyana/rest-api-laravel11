<?php
  
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
  
use App\Http\Controllers\API\RegisterController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\ProvinceController;

Route::controller(ProvinceController::class)->group(function () {
    Route::get('mvt/{x}/{y}/{z}' , 'Mvt');
    Route::post('catalog-location/{location_level}/get-all' , 'GetSussestionProvince');
});

Route::controller(RegisterController::class)->group(function(){
    Route::post('register', 'register');
    Route::post('login', 'login');
});