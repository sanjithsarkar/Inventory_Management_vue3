<?php

use App\Http\Controllers\API\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\PayPalController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Route::controller(AuthController::class)->group(function(){
//     Route::post(('login', 'login'));
// })

Route::post('/login', 'App\Http\Controllers\API\AuthController@login');
Route::post('/register', 'App\Http\Controllers\API\AuthController@register');
Route::apiResource('/employees', 'App\Http\controllers\EmployeeController');
Route::apiResource('/customers', 'App\Http\controllers\CustomerController');
Route::apiResource('/categories', 'App\Http\controllers\CategoryController');
Route::apiResource('/products', 'App\Http\controllers\ProductController');
Route::apiResource('/orders', 'App\Http\controllers\OrderController');
Route::get('/add/cart/{id}', 'App\Http\controllers\CartController@AddToCart');
Route::get('/get/cart', 'App\Http\controllers\CartController@getAllCart');
Route::get('/increase/cart/{id}', 'App\Http\controllers\CartController@increaseCart');
Route::get('/decrease/cart/{id}', 'App\Http\controllers\CartController@decreaseCart');
Route::delete('/delete/cart/{id}', 'App\Http\controllers\CartController@deleteCart');
Route::post('/order/done', 'App\Http\controllers\CartController@orderDone');
// Route::post('/', 'App\Http\controllers\CartController@searchOrderDate');
Route::get('/search/by/date', 'App\Http\controllers\OrderController@searchByDate');
Route::get('/order/product/{id}', 'App\Http\controllers\OrderController@getOrderProduct');
Route::get('/order/{id}', 'App\Http\controllers\OrderController@getOrder');
Route::get('/today/sell', 'App\Http\controllers\OrderController@todaySale');
Route::get('/today/income', 'App\Http\controllers\OrderController@todayIncome');
Route::get('/today/due', 'App\Http\controllers\OrderController@todayDue');
Route::get('/today/expense', 'App\Http\controllers\ExpenseController@todayExpense');
Route::delete('/products', 'App\Http\controllers\ProductController@bulkDelete');
Route::post('/stripe/payment', 'App\Http\controllers\PosController@payment');
Route::get('/stripe/success', [PosController::class, 'success'])->name('stripe.success');
Route::get('/stripe/cancel', [PosController::class, 'cancel'])->name('stripe.cancel');
Route::post('/stripe/webhook', [PosController::class, 'handleWebhook']);
Route::post('/paypal/payment', 'App\Http\controllers\PayPalController@payment')->name('paypal.payment');
Route::get('/paypal/cancel', 'App\Http\controllers\PayPalController@cancel')->name('paypal.cancel');
Route::get('/payment/success', 'App\Http\controllers\PayPalController@success')->name('paypal.success');
Route::post('/paypal/webhook', [PayPalController::class, 'handleWebhook'])->name('paypal.webhook');
Route::get('/yesterday/sales', 'App\Http\controllers\OrderController@yesterdaySales');
Route::get('/yesterday/income', 'App\Http\controllers\OrderController@yesterdayIncome');
Route::get('/yesterday/due', 'App\Http\controllers\OrderController@yesterdayDue');
Route::get('/yesterday/expense', 'App\Http\controllers\ExpenseController@yesterdayExpense');
Route::APIResource('/suppliers', 'App\Http\controllers\SupplierController');

