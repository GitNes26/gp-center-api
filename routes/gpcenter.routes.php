<?php

use App\Http\Controllers\AssignedVehicleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

#region CONTROLLERS
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\DirectorController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\LoanedVehicleController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\ModelController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\VehiclePlatesController;
use App\Http\Controllers\VehicleStatusController;

#endregion CONTROLLERS

Route::post('/login', [UserController::class, 'login']);
Route::post('/signup', [UserController::class, 'signup']);

Route::middleware('auth:sanctum')->group(function () {
    // Route::get('/getUser/{token}', [UserController::class,'getUser']); //cerrar sesión (eliminar los tokens creados)
    Route::get('/logout', [UserController::class, 'logout']); //cerrar sesión (eliminar los tokens creados)

    Route::controller(MenuController::class)->group(function () {
        Route::get('/menus', 'index');
        Route::get('/menus/selectIndex', 'selectIndex');
        Route::get('/menus/{id}', 'show');
        Route::post('/menus', 'create');
        Route::post('/menus/update/{id?}', 'update');
        Route::post('/menus/destroy/{id}', 'destroy');

        Route::get('/menus/MenusByRole/{pages_read}', 'MenusByRole');
        Route::post('/menus/getIdByUrl', 'getIdByUrl');
    });

    Route::controller(UserController::class)->group(function () {
        Route::get('/users/role_id/{role_id}', 'index');
        Route::get('/users/by/role_id/{role_id}', 'indexByRole');
        Route::get('/users/selectIndex', 'selectIndex');
        Route::get('/users/{id}', 'show');
        // Route::post('/users', 'create');
        // Route::post('/users/update/{id?}', 'update');
        Route::post('/users/destroy/{id}', 'destroy');
        Route::post('/users/destroyMultiple', 'destroyMultiple');
        Route::get('/users/{id}/DisEnableUser/{active}', 'DisEnableUser');

        // Route::post('/users/create/{role_id}', 'create');
        Route::post('/users/create/role_id/{role_id}', 'createOrUpdate');
        Route::post('/users/update/role_id/{role_id}', 'createOrUpdate');
    });

    Route::controller(DirectorController::class)->group(function () {
        Route::get('/directors', 'index');
        Route::get('/directors/selectIndex', 'selectIndex');
        Route::get('/directors/{id}', 'show');
        // Route::post('/directors/create', 'createOrUpdate'); #ES POR MEDIO DEL UserController
        // Route::post('/directors/update/{id?}', 'createOrUpdate');
        // Route::post('/directors/destroy/{id}', 'destroy');
    });
    Route::controller(DriverController::class)->group(function () {
        Route::get('/drivers', 'index');
        Route::get('/drivers/selectIndex', 'selectIndex');
        Route::get('/drivers/{id}', 'show');
        // Route::post('/drivers/create', 'createOrUpdate'); #ES POR MEDIO DEL UserController
        // Route::post('/drivers/update/{id?}', 'createOrUpdate');
        // Route::post('/drivers/destroy/{id}', 'destroy');
    });

    Route::controller(RoleController::class)->group(function () {
        Route::get('/roles', 'index');
        Route::get('/roles/selectIndex/{role_id}', 'selectIndex');
        Route::get('/roles/{id}', 'show');
        Route::post('/roles', 'create');
        Route::post('/roles/update/{id?}', 'update');
        Route::post('/roles/destroy/{id}', 'destroy');
    });

    Route::controller(DepartmentController::class)->group(function () {
        Route::get('/departments', 'index');
        Route::get('/departments/selectIndex', 'selectIndex');
        Route::get('/departments/{id}', 'show');
        Route::post('/departments/create', 'create');
        Route::post('/departments/update/{id?}', 'update');
        Route::post('/departments/destroy/{id}', 'destroy');
    });

    Route::controller(BrandController::class)->group(function () {
        Route::get('/brands', 'index');
        Route::get('/brands/selectIndex', 'selectIndex');
        Route::get('/brands/{id}', 'show');
        Route::post('/brands', 'create');
        Route::post('/brands/update/{id}', 'update');
        //    Route::post('/brands/{id?}', 'update');
        Route::post('/brands/destroy/{id}', 'destroy');
    });

    Route::controller(ModelController::class)->group(function () {
        Route::get('/models', 'index');
        Route::get('/models/brand/{brand_id}', 'selectIndex');
        Route::get('/models/{id}', 'show');
        Route::post('/models', 'create');
        Route::post('/models/update/{id?}', 'update');
        Route::post('/models/destroy/{id}', 'destroy');
    });

    Route::controller(VehicleStatusController::class)->group(function () {
        Route::get('/vehicleStatus', 'index');
        Route::get('/vehicleStatus/selectIndex', 'selectIndex');
        Route::get('/vehicleStatus/{id}', 'show');
        Route::post('/vehicleStatus', 'create');
        Route::post('/vehicleStatus/update/{id?}', 'update');
        Route::post('/vehicleStatus/destroy/{id}', 'destroy');
    });

    Route::controller(VehicleController::class)->group(function () {
        Route::get('/vehicles', 'index');
        Route::get('/vehicles/selectIndex', 'selectIndex');
        Route::get('/vehicles/{id}', 'show');
        Route::get('/vehicles/{searchBy?}/{value}', 'showBy');
        Route::post('/vehicles', 'create');
        Route::post('/vehicles/update/{id}', 'update');
        //    Route::post('/vehicles/{id?}', 'update');
        Route::post('/vehicles/destroy/{id}', 'destroy');
    });
    Route::controller(VehiclePlatesController::class)->group(function () {
        Route::get('/vehiclesPlates', 'index');
        Route::get('/vehiclesPlates/selectIndex', 'selectIndex');
        Route::get('/vehiclesPlates/{id}', 'show');
        Route::post('/vehiclesPlates', 'create');
        Route::post('/vehiclesPlates/update/{id?}', 'update');
        Route::post('/vehiclesPlates/destroy/{id}', 'destroy');

        Route::get('/vehiclesPlates/history/{vehicle_id}', 'history');
    });


    Route::controller(ServiceController::class)->group(function () {
        Route::get('/services', 'index');
        Route::get('/services/selectIndex', 'selectIndex');
        Route::get('/services/{id}', 'show');
        Route::get('/services/{searchBy?}/{value}', 'showBy');
        Route::post('/services', 'create');
        // Route::post('/services/{id}', 'update'); // por si quiero subir una imagen
        Route::post('/services/update/{id?}', 'update');
        Route::post('/services/destroy/{id}', 'destroy');

        Route::post('/services/{id?}', 'update');
    });

    Route::controller(AssignedVehicleController::class)->group(function () {
        Route::get('/assignedVehicle', 'index');
        Route::get('/assignedVehicle/{id}', 'show');
        Route::post('/assignedVehicle/create', 'createOrUpdate');
        Route::post('/assignedVehicle/update/{id?}', 'createOrUpdate');
        Route::post('/assignedVehicle/destroy/{id}', 'destroy');
    });

    Route::controller(LoanedVehicleController::class)->group(function () {
        Route::get('/loanedVehicle', 'index');
        Route::get('/loanedVehicle/{id}', 'show');
        Route::post('/loanedVehicle/create', 'createOrUpdate');
        Route::post('/loanedVehicle/update/{id?}', 'createOrUpdate');
        Route::post('/loanedVehicle/destroy/{id}', 'destroy');
    });
});
