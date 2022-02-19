<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StoreController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

//Stores
Route::get('/store/get', [StoreController::class, 'getStores']);
Route::post('/store/add', [StoreController::class, 'addStore']);
Route::post('/store/delete', [StoreController::class, 'deleteStore']);
Route::post('/store/resend_completion_link', [StoreController::class, 'resendCompletionLink']);
