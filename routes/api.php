<?php

use App\Http\Controllers\Api\HoldingLaporanController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// Holding API — laporan keuangan tenant untuk integrasi aplikasi holding pusat.
// Autentikasi: header X-Holding-Token + X-Holding-Tenant (lihat HOLDING-API.md §1).
Route::middleware('holding.license')->prefix('v1/holding/laporan')->group(function () {
    Route::get('neraca', [HoldingLaporanController::class, 'neraca']);
    Route::get('laba-rugi', [HoldingLaporanController::class, 'labaRugi']);
    Route::get('arus-kas', [HoldingLaporanController::class, 'arusKas']);
    Route::get('perubahan-ekuitas', [HoldingLaporanController::class, 'perubahanEkuitas']);
    Route::get('calk', [HoldingLaporanController::class, 'calk']);
});