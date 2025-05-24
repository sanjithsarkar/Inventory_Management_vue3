<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PosController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('index');
})->name('home');


// Route::get('/{vue_capture}', function () {
//     return view('index');
// })->where('vue_capture', '[\/\w\.-]*');

// Route::get('/{any}', function () {
//     return view('index');
// })->where('any', '.*');

Route::get('/{any}', function () {
    return view('index2'); // This should be your Vue entry point
})->where('any', '.*')->name('spa');
// Route::post('/stripe/payment', [PosController::class, 'payment'])->name('stripe.payment');
// Route::get('stripe/success', [PosController::class, 'success'])->name('stripe.success');
// Route::get('stripe/cancel', [PosController::class, 'cancel'])->name('stripe.cancel');
// Route::post('/webhook/stripe', [PosController::class, 'handleWebhook']);

Route::get('/test-paypal', function () {
    try {
        $paypalService = app(App\Services\PayPal\PayPalService::class);
        $token = $paypalService->client->getAccessToken();
        return response()->json(['success' => true, 'message' => 'PayPal configuration is working!']);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
    }
});
