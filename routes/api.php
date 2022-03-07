<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductConditionController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ConditionController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\BiddingController;

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
Route::get('/store/all', [StoreController::class, 'getAllStores']);
Route::post('/store/get', [StoreController::class, 'getStore']);
Route::post('/store/update', [StoreController::class, 'updateStore']);
Route::post('/store/add', [StoreController::class, 'addStore']);
Route::post('/store/delete', [StoreController::class, 'deleteStore']);
Route::post('/store/resend_completion_link', [StoreController::class, 'resendCompletionLink']);
Route::post('/store/login', [StoreController::class, 'login']);
Route::post('/store/update_password', [StoreController::class, 'updatePassword']);
Route::post('/store/check_password', [StoreController::class, 'checkPassword']);
Route::post('/store/get_status', [StoreController::class, 'getStatus']);

//Products
Route::get('/product/all',[ProductController::class, 'getAllProducts']);
Route::post('/product/add', [ProductController::class, 'addProduct']);
Route::post('/product/store', [ProductController::class, 'getStoreProducts']);
Route::post('/product/delete', [ProductController::class, 'deleteProduct']);

//Product Condition
Route::get('/product_condition/all',[ProductConditionController::class, 'getProductCondition']);
Route::post('/product_condition/add', [ProductConditionController::class, 'addProductCondition']);
Route::post('/product_condition/delete', [ProductConditionController::class, 'deleteProductCondition']);
//Product Category
Route::get('/product_category/all',[ProductCategoryController::class, 'getProductCategory']);
Route::post('/product_category/add', [ProductCategoryController::class, 'addProductCategory']);
Route::post('/product_category/delete', [ProductCategoryController::class, 'deleteProductCategory']);

//Condition
Route::get('/category/all',[CategoryController::class, 'getCategories']);
Route::post('/category/add', [CategoryController::class, 'addCategory']);
Route::post('/category/delete', [CategoryController::class, 'deleteCategory']);

//Category
Route::get('/condition/all',[ConditionController::class, 'getConditions']);
Route::post('/condition/add', [ConditionController::class, 'addCondition']);
Route::post('/condition/delete', [ConditionController::class, 'deleteCondition']);

//Media
Route::post('/media/upload', [MediaController::class, 'uploadFiles']);

//Bidding
Route::post('/bidding/add', [BiddingController::class, 'addBidding']);
Route::post('/bidding/update', [BiddingController::class, 'updateBidding']);
