<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Frontend\AuthController;
use Rap2hpoutre\LaravelLogViewer\LogViewerController;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});

Route::get('/cache-clear', function () {
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    Artisan::call('optimize');
    $env = env('APP_ENVIRONMENT');
    return "Optimized and environmet set to: ".$env;
});

// Route::get('/card', function () {
//    return view('card');
// });

// require __DIR__.'/auth.php';
Route::get('/logs/login', [AuthController::class, 'login'])->name('login');
Route::post('login', [AuthController::class, 'loginWeb'])->name('login.web');

Route::group(['middleware' => 'auth'], function () {
    Route::get('logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('logs', [LogViewerController::class, 'index'])->name('logs');
});

Route::get('/google-pay/{amount?}', [AuthController::class, 'googlePay'])->name('google-pay');
Route::post('/google-pay-response', [AuthController::class, 'googlePayResponse'])->name('google-pay.response');

Route::get('/google-pay-success/{token?}', [AuthController::class, 'googlePaySuccess'])->name('google-success');
Route::get('/google-pay-failed', [AuthController::class, 'googlePayFailed'])->name('google-failed');




Route::get('/run-composer', function () {
    try {
        // Execute the Composer command
        $output = shell_exec('composer install 2>&1');

        // Return the output
        return response()->json([
            'success' => true,
            'output' => $output,
        ]);
    } catch (\Exception $e) {
        // Handle errors and return an error response
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
        ], 500);
    }
});
