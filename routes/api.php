<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Brand\BrandController;
use App\Http\Controllers\Client\ClientController;
use App\Http\Controllers\Model\MModelController;
use App\Http\Controllers\Moderator\ModeratorController;
use App\Http\Controllers\RRequest\RequestController;
use App\Http\Controllers\Shipper\ShipperController;
use App\Http\Controllers\Supplier\SupplierController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/** NOTEl MIDDLEWARE FOR AUTH SHOULD BE ADDED INSIDE THE CONTROLLER*/
Route::prefix('admin')->group(function () {
    Route::resource('/admin-operations', AdminController::class);
});


/**
 * ==============
 * To make the URL path like this: http://localhost:800/api/admin/admin-operations
 * For more arrangement
 * ==============
 */



/** NOTEl MIDDLEWARE FOR AUTH SHOULD BE ADDED INSIDE THE CONTROLLER */
Route::prefix('moderator')->group(function () {
    Route::resource('/moderator-operations', ModeratorController::class);
});

/**
 * ==============
 * To make the URL path like this: http://localhost:800/api/moderator/moderator-operations
 * For more arrangement
 * ==============
 */



/** NOTEl MIDDLEWARE FOR AUTH SHOULD BE ADDED INSIDE THE CONTROLLER*/
Route::prefix('supplier')->group(function () {
    Route::resource('/supplier-operations', SupplierController::class);
});

 /**
 * ==============
 * To make the URL path like this: http://localhost:800/api/supplier/supplier-operations
 * For more arrangement
 * ==============
 */


/** NOTEl MIDDLEWARE FOR AUTH SHOULD BE ADDED IN SOME ROUTE INSIDE THE CONTROLLER */
Route::prefix('client')->group(function () {
    Route::resource('/client-operations', ClientController::class);
});

  /**
 * ==============
 * To make the URL path like this: http://localhost:800/api/client/client-operations
 * For more arrangement
 * ==============
 */


Route::prefix('shipper')->group(function () {
    Route::resource('/shipper-operations', ShipperController::class);
});

  /**
 * ==============
 * To make the URL path like this: http://localhost:800/api/shipper/shipper-operations
 * For more arrangement
 * ==============
 */

Route::prefix('brand')->group(function () {
    Route::resource('/brand-operations', BrandController::class);
});


  /**
 * ==============
 * To make the URL path like this: http://localhost:800/api/brand/brand-operations
 * For more arrangement
 * ==============
 */

Route::prefix('model')->group(function () {
    Route::resource('/model-operations', MModelController::class);
});
 

  /**
 * ==============
 * To make the URL path like this: http://localhost:800/api/model/model-operations
 * For more arrangement
 * ==============
 */

Route::prefix('request')->group(function () {
    Route::resource('/request-operations', RequestController::class);
});

  /**
 * ==============
 * To make the URL path like this: http://localhost:800/api/request/request-operations
 * For more arrangement
 * ==============
 */