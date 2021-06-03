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

/** NOTE MIDDLEWARE FOR AUTH SHOULD BE ADDED INSIDE THE CONTROLLER*/
Route::prefix('admin')->group(function () {
    Route::apiResource('/admin-operations', AdminController::class);
});


/**
 * ==============
 * To make the URL path like this: http://localhost:800/api/admin/admin-operations
 * For more arrangement
 * ==============
 */



/** NOTE MIDDLEWARE FOR AUTH SHOULD BE ADDED INSIDE THE CONTROLLER */
Route::prefix('moderator')->group(function () {
    Route::apiResource('/moderator-operations', ModeratorController::class);
});

/**
 * ==============
 * To make the URL path like this: http://localhost:800/api/moderator/moderator-operations
 * For more arrangement
 * ==============
 */



/** NOTE MIDDLEWARE FOR AUTH SHOULD BE ADDED INSIDE THE CONTROLLER*/
Route::prefix('supplier')->group(function () {
    Route::apiResource('/supplier-operations', SupplierController::class);
});

 /**
 * ==============
 * To make the URL path like this: http://localhost:800/api/supplier/supplier-operations
 * For more arrangement
 * ==============
 */


/** NOTE MIDDLEWARE FOR AUTH SHOULD BE ADDED IN SOME ROUTE INSIDE THE CONTROLLER */
Route::prefix('client')->group(function () {
    Route::apiResource('/client-operations', ClientController::class);
});

  /**
 * ==============
 * To make the URL path like this: http://localhost:800/api/client/client-operations
 * For more arrangement
 * ==============
 */


Route::prefix('shipper')->group(function () {
    Route::apiResource('/shipper-operations', ShipperController::class);
});

  /**
 * ==============
 * To make the URL path like this: http://localhost:800/api/shipper/shipper-operations
 * For more arrangement
 * ==============
 */

Route::prefix('brand')->group(function () {
    Route::apiResource('/brand-operations', BrandController::class);
});


  /**
 * ==============
 * To make the URL path like this: http://localhost:800/api/brand/brand-operations
 * For more arrangement
 * ==============
 */

Route::prefix('model')->group(function () {
    Route::apiResource('/model-operations', MModelController::class);
});
 

  /**
 * ==============
 * To make the URL path like this: http://localhost:800/api/model/model-operations
 * For more arrangement
 * ==============
 */

Route::prefix('request')->group(function () {
    Route::apiResource('/request-operations', RequestController::class);
    //To update the amount of each supplier to be displayed to the client er to choose from
    Route::put("/update-amounts", [RequestController::class, 'updateAmounts']);

    //To show full data of each supplier with his amount offer 
    Route::get("/show-amounts", [RequestController::class, 'showFullAmounts']);

    //To update the request with the final amount + supplier id which has the best offer
    Route::post("/select-best-price", [RequestController::class, 'selectBestPrice']);
});

  /**
 * ==============
 * To make the URL path like this: http://localhost:800/api/request/request-operations
 * For more arrangement
 * ==============
 */
