<?php

use App\Http\Controllers\AssignedVehicleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

#region CONTROLLERS
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\DeliveredVehicleController;
use App\Http\Controllers\DirectorController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\LoanedVehicleController;
use App\Http\Controllers\MechanicController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\ModelController;
use App\Http\Controllers\ServiceController;
// use App\Http\Controllers\SSEController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\VehicleMovementController;
use App\Http\Controllers\VehiclePlatesController;
use App\Http\Controllers\VehicleStatusController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\VoucherDetailController;
use App\Http\Controllers\VoucherRequesterController;

#endregion CONTROLLERS

Route::post('/login', [UserController::class, 'login']);
Route::post('/signup', [UserController::class, 'signup']);

// Route::get('/sse/{channel}', [SSEController::class, 'listen']);  // Ruta para escuchar los eventos
// Route::post('/disparar', [SSEController::class, 'disparar']);
Route::middleware('auth:sanctum')->group(function () {

    // Route::get('/getUser/{token}', [UserController::class,'getUser']); //cerrar sesión (eliminar los tokens creados)
    Route::get('/logout/{all_sessions?}', [UserController::class, 'logout']); //cerrar sesión (eliminar los tokens creados)

    Route::controller(RoleController::class)->group(function () {
        Route::get('/roles/role_id/{role_id}', 'index');
        Route::get('/roles/selectIndex/role_id/{role_id}', 'selectIndex');
        Route::get('/roles/{id}', 'show');
        Route::post('/roles', 'create');
        Route::post('/roles/update/{id?}', 'update');
        Route::post('/roles/destroy/{id}', 'destroy');

        Route::get('/roles/{id}/DisEnableRole/{active}', 'DisEnableRole');
        Route::post('/roles/updatePermissions', 'updatePermissions');
    });

    Route::controller(MenuController::class)->group(function () {
        Route::get('/menus', 'index');
        Route::get('/menus/selectIndex', 'selectIndex');
        Route::get('/menus/selectIndexToRoles', 'selectIndexToRoles');
        // Route::get('/menus/selectIndexUrl', 'selectIndexUrl');
        Route::get('/menus/headers/selectIndex', 'headersSelectIndex');
        Route::get('/menus/id/{id}', 'show');
        Route::post('/menus/create', 'createOrUpdate');
        Route::post('/menus/update/{id?}', 'createOrUpdate');
        Route::post('/menus/destroy/{id}', 'destroy');

        Route::get('/menus/MenusByRole/{pages_read}', 'MenusByRole');
        Route::post('/menus/getIdByUrl', 'getIdByUrl');
        Route::get('/menus/{id}/DisEnableMenu/{active}', 'DisEnableMenu');
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
        Route::post('/users/changePasswordAuth', 'changePasswordAuth');
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

    Route::prefix("mechanics")->group(function () {
        Route::get("/", [MechanicController::class, 'index']);
        Route::get("/selectIndex", [MechanicController::class, 'selectIndex']);
        Route::post("/createOrUpdate/{id?}", [MechanicController::class, 'createOrUpdate']);
        Route::get("/id/{id}", [MechanicController::class, 'show']);
        Route::get("/delete/{id}", [MechanicController::class, 'delete']);
        Route::get("/disEnable/{id}/{active}", [MechanicController::class, 'disEnable']);
        Route::get("/deleteMultiple", [MechanicController::class, 'deleteMultiple']);
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

    Route::controller(VehicleMovementController::class)->group(function () {
        Route::get('/vehicleMovements', 'index');
        Route::get('/vehicleMovements/history/{vehicle_id}', 'history');
    });


    Route::controller(ServiceController::class)->group(function () {
        Route::get('/services', 'index');
        Route::get('/services/status/{status}', 'index');
        Route::get('/services/selectIndex', 'selectIndex');
        Route::get('/services/{id}', 'show');
        Route::get('/services/{searchBy?}/{value}', 'showBy');
        Route::post('/services', 'create');
        // Route::post('/services/{id}', 'update'); // por si quiero subir una imagen
        Route::post('/services/update/{id?}', 'update');
        Route::post('/services/destroy/{id}', 'destroy');

        Route::post('/services/{id?}', 'update');
        Route::get('/services/{id}/changeStatus/{status}', 'changeStatus');

        Route::get('/services/{id}/loadMaterial/{request_material}', 'loadMaterial');
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

        Route::post('/loanedVehicle/returnLoan', 'returnLoan');
    });

    Route::controller(DeliveredVehicleController::class)->group(function () {
        Route::get('/deliveredVehicle', 'index');
        Route::get('/deliveredVehicle/{id}', 'show');
        Route::post('/deliveredVehicle/create', 'createOrUpdate');
        Route::post('/deliveredVehicle/update/{id?}', 'createOrUpdate');
        Route::post('/deliveredVehicle/destroy/{id}', 'destroy');
    });

    Route::controller(VoucherController::class)->group(function () {
        Route::get('/vouchers', 'index');
        Route::get('/vouchers/year/{year}', 'index');
        Route::get('/vouchers/status/{status}', 'index');
        Route::get('/vouchers/{id}', 'show');
        Route::get('/vouchers/selectIndex', 'selectIndex');
        Route::post('/vouchers/create', 'createOrUpdate');
        Route::post('/vouchers/update/{id?}', 'createOrUpdate');
        Route::post('/vouchers/destroy/{id}', 'destroy');

        Route::post('/vouchers/updateStatus/id/{id}/voucher_status/{voucher_status}', 'updateStatus');
        Route::post('/vouchers/seenVoucher/{id}', 'seenVoucher');
        Route::get('/vouchers/counter/{key}/{value}', 'counter');
    });


    Route::controller(VoucherDetailController::class)->group(function () {
        Route::get('/voucherDetails', 'index');
        Route::get('/voucherDetails/voucher_id/{voucher_id}', 'indexByVoucher');
        Route::get('/voucherDetails/id/{id}', 'showVoucherDetail');
        Route::post('/voucherDetails/create', 'createOrUpdate');
        Route::post('/voucherDetails/update/{id?}', 'createOrUpdate');
        Route::post('/voucherDetails/destroy', 'destroy');
    });

    Route::controller(VoucherRequesterController::class)->group(function () {
        Route::get('/voucherRequesters', 'index');
        Route::get('/voucherRequesters/selectIndex', 'selectIndex');
        Route::get('/voucherRequesters/{id}', 'show');
        // Route::post('/voucherRequesters/create', 'createOrUpdate'); #ES POR MEDIO DEL UserController
        // Route::post('/voucherRequesters/update/{id?}', 'createOrUpdate');
        // Route::post('/voucherRequesters/destroy/{id}', 'destroy');
    });
});
