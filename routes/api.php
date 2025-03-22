<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TransactionController;

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
// Secure the transactions API with auth:api middleware
Route::middleware('auth:sanctum')->group(function () {
    Route::get('transactions', [TransactionController::class, 'index']); // List all transactions
    Route::post('transactions', [TransactionController::class, 'store']); // Store a new transaction
    Route::get('transactions/{id}', [TransactionController::class, 'show']); // Show a single transaction
    Route::put('transactions/{id}', [TransactionController::class, 'update']); // Update a transaction
    Route::delete('transactions/{id}', [TransactionController::class, 'destroy']); // Delete a transaction
});
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/send-to-bank', [TransactionController::class, 'sendToBank']);
});
Route::post('transaction/callback', [TransactionController::class, 'transactionCallback']);
