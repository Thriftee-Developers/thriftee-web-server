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
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\BidController;
use App\Http\Controllers\FeaturedProductController;
use App\Http\Controllers\SliderProductController;
use App\Http\Controllers\StoreBillingMethodController;

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
Route::post('/product/get', [ProductController::class, 'getProduct']);
Route::post('/product/id', [ProductController::class, 'getProductByID']);
Route::post('/product/add', [ProductController::class, 'addProduct']);
Route::post('/product/store', [ProductController::class, 'getStoreProducts']);
Route::post('/product/delete', [ProductController::class, 'deleteProduct']);
Route::post('/product/category', [CategoryController::class, 'getCategoryByProduct']);
Route::post('/product/condition', [ConditionController::class, 'getConditionByProduct']);

//Product Condition
// Route::get('/product_condition/all',[ProductConditionController::class, 'getProductCondition']);
//Route::post('/product_condition/add', [ProductConditionController::class, 'addProductCondition']);
// Route::post('/product_condition/delete', [ProductConditionController::class, 'deleteProductCondition']);

//Product Category
// Route::get('/product_category/all',[ProductCategoryController::class, 'getProductCategory']);
// Route::post('/product_category/add', [ProductCategoryController::class, 'addProductCategory']);
// Route::post('/product_category/delete', [ProductCategoryController::class, 'deleteProductCategory']);

//Condition
Route::get('/category/all',[CategoryController::class, 'getAllCategory']);
Route::post('/category/add', [CategoryController::class, 'addCategory']);
Route::post('/category/delete', [CategoryController::class, 'deleteCategory']);
Route::post('/category/get', [CategoryController::class, 'getCategory']);

//Category
Route::get('/condition/all',[ConditionController::class, 'getAllConditions']);
Route::post('/condition/add', [ConditionController::class, 'addCondition']);
Route::post('/condition/delete', [ConditionController::class, 'deleteCondition']);
Route::post('/condition/get', [ConditionController::class, 'getCondition']);

//Media
Route::post('/media/upload_product_images', [MediaController::class, 'uploadProductImages']);
Route::post('/media/get_product_images', [MediaController::class, 'getProductImages']);
Route::post('media/make_dir', [MediaController::class, 'makeDirectory']);

//Bidding
Route::post('/bidding/add', [BiddingController::class, 'addBidding']);
Route::post('/bidding/update', [BiddingController::class, 'updateBidding']);
Route::get('/bidding/all', [BiddingController::class, 'getAllBidding']);
Route::post('/bidding/get', [BiddingController::class, 'getBidding']);
Route::post('/bidding/by_product', [BiddingController::class, 'getBiddingByProduct']);
Route::post('/bidding/latest_by_product', [BiddingController::class, 'getLatestBiddingByProduct']);
Route::post('/bidding/by_store', [BiddingController::class, 'getBiddingByStore']);

//Customer
Route::post('/customer/register', [CustomerController::class, 'addCustomer']);
Route::post('/customer/update', [CustomerController::class, 'updateCustomer']);
Route::post('/customer/update_password', [CustomerController::class, 'updatePassword']);
Route::post('/customer/get', [CustomerController::class, 'getCustomerByUUID']);
Route::post('/customer/by_email', [CustomerController::class, 'getCustomerByEmail']);
Route::post('/customer/status', [CustomerController::class, 'getStatus']);
Route::post('/customer/login', [CustomerController::class, 'login']);

//Bid
Route::post('/bid/add', [BidController::class, 'addBid']);
Route::post('/bid/highest', [BidController::class, 'getHighestBidByProduct']);
Route::post('/bid/highest_by_bidding', [BidController::class, 'getHighestBidByBidding']);
Route::post('/bid/by_customer', [BidController::class, 'getAllBidByCustomer']);
Route::post('/bid/by_product', [BidController::class, 'getBidByProduct']);
Route::post('/bid/by_customer_product', [BidController::class, 'getBidByProductAndCustomer']);
Route::post('/bid/total_number_of_bids', [BidController::class, 'getTotalNumberOfBids']);
Route::post('/bid/by_bidding_customer', [BidController::class, 'getBidByBiddingAndCustomer']);
//Rating
Route::post('/rating/add', [RatingController::class, 'addRating']);
Route::post('/rating/by_store', [RatingController::class, 'getRatingByStore']);
Route::post('/rating/by_customer_store', [RatingController::class, 'getRatingByCustomerAndStore']);

//Featured Product
Route::post('/product/featured/add', [FeaturedProductController::class, 'addFeaturedProduct']);
Route::get('/product/featured/all', [FeaturedProductController::class, 'getAllFeaturedProduct']);
Route::post('/product/featured/update', [FeaturedProductController::class, 'updateFeaturedProduct']);
Route::post('/product/featured/delete', [FeaturedProductController::class, 'deleteFeaturedProduct']);
//Slider Product
Route::post('/product/slider/add', [SliderProductController::class, 'addSliderProduct']);
Route::get('/product/slider/all', [SliderProductController::class, 'getAllSliderProduct']);
Route::post('/product/slider/update', [SliderProductController::class, 'updateSliderProduct']);
Route::post('/product/slider/delete', [SliderProductController::class, 'deleteSliderProduct']);

//StoreBillingMethod API
Route::post('/storebilling/add', [StoreBillingMethodController::class, 'addStoreBilling']);
Route::get('/storebilling/all', [StoreBillingMethodController::class, 'getAllStoreBilling']);
Route::post('/storebilling/get', [StoreBillingMethodController::class, 'getStoreBilling']);
//Dummy Route
Route::get('/product/delete_all', [ProductController::class, 'deleteAllProduct']);


