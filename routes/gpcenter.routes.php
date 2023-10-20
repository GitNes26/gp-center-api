<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

#region CONTROLLERS
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\ModelController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\VehiclePlatesController;
use App\Http\Controllers\VehicleStatusController;

#endregion CONTROLLERS

Route::post('/login', [UserController::class, 'login']);
Route::post('/signup', [UserController::class, 'signup']);

// Route::middleware('auth:sanctum')->group(function () {
    // Route::get('/getUser/{token}', [UserController::class,'getUser']); //cerrar sesión (eliminar los tokens creados)
    Route::delete('/logout/{id}', [UserController::class, 'logout']); //cerrar sesión (eliminar los tokens creados)

    Route::controller(UserController::class)->group(function () {
    Route::get('/users', 'index');
    Route::get('/users/selectIndex', 'selectIndex');
    Route::get('/users/{id}', 'show');
    Route::post('/users', 'create');
    Route::put('/users/{id?}', 'update');
    Route::delete('/users/{id}', 'destroy');
    });

    Route::controller(RoleController::class)->group(function () {
    Route::get('/roles', 'index');
    Route::get('/roles/selectIndex', 'selectIndex');
    Route::get('/roles/{id}', 'show');
    Route::post('/roles', 'create');
    Route::put('/roles/{id?}', 'update');
    Route::delete('/roles/{id}', 'destroy');
    });

    Route::controller(DepartmentController::class)->group(function () {
    Route::get('/departments', 'index');
    Route::get('/departments/selectIndex', 'selectIndex');
    Route::get('/departments/{id}', 'show');
    Route::post('/departments', 'create');
    Route::put('/departments/{id?}', 'update');
    Route::delete('/departments/{id}', 'destroy');
    });

    Route::controller(BrandController::class)->group(function () {
    Route::get('/brands', 'index');
    Route::get('/brands/selectIndex', 'selectIndex');
    Route::get('/brands/{id}', 'show');
    Route::post('/brands', 'create');
    Route::post('/brands/{id}', 'update');
    //    Route::put('/brands/{id?}', 'update');
    Route::delete('/brands/{id}', 'destroy');
    });

    Route::controller(ModelController::class)->group(function () {
    Route::get('/models', 'index');
    Route::get('/models/brand/{brand_id}', 'selectIndex');
    Route::get('/models/{id}', 'show');
    Route::post('/models', 'create');
    Route::put('/models/{id?}', 'update');
    Route::delete('/models/{id}', 'destroy');
    });

    Route::controller(VehicleStatusController::class)->group(function () {
    Route::get('/vehicleStatus', 'index');
    Route::get('/vehicleStatus/selectIndex', 'selectIndex');
    Route::get('/vehicleStatus/{id}', 'show');
    Route::post('/vehicleStatus', 'create');
    Route::put('/vehicleStatus/{id?}', 'update');
    Route::delete('/vehicleStatus/{id}', 'destroy');
    });

    Route::controller(VehicleController::class)->group(function () {
    Route::get('/vehicles', 'index');
    Route::get('/vehicles/selectIndex', 'selectIndex');
    Route::get('/vehicles/{id}', 'show');
    Route::get('/vehicles/{searchBy?}/{value}', 'showBy');
    Route::post('/vehicles', 'create');
    Route::post('/vehicles/{id}', 'update');
    //    Route::put('/vehicles/{id?}', 'update');
    Route::delete('/vehicles/{id}', 'destroy');
    });
    Route::controller(VehiclePlatesController::class)->group(function () {
    Route::get('/vehiclesPlates', 'index');
    Route::get('/vehiclesPlates/selectIndex', 'selectIndex');
    Route::get('/vehiclesPlates/{id}', 'show');
    Route::post('/vehiclesPlates', 'create');
    Route::put('/vehiclesPlates/{id?}', 'update');
    Route::delete('/vehiclesPlates/{id}', 'destroy');

    Route::get('/vehiclesPlates/history/{vehicle_id}', 'history');
    });


    Route::controller(ServiceController::class)->group(function () {
        Route::get('/services', 'index');
        Route::get('/services/selectIndex', 'selectIndex');
        Route::get('/services/{id}', 'show');
        Route::get('/services/{searchBy?}/{value}', 'showBy');
        Route::post('/services', 'create');
        // Route::post('/services/{id}', 'update'); // por si quiero subir una imagen
        Route::put('/services/{id?}', 'update');
        Route::delete('/services/{id}', 'destroy');

        Route::post('/services/{id?}', 'update');

    });


// });
