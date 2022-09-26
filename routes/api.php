<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\KelasController;
use App\Http\Controllers\Api\MapelController;
use App\Http\Controllers\Api\SiswaController;
use App\Http\Controllers\Api\NilaiController;

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


Route::apiResource('kelas', KelasController::class)->parameters([
    'kelas' => 'kelas'
]);

Route::put('kelas/{kelas}/siswa/{siswa}', [KelasController::class, 'addSiswa']);
Route::delete('kelas/{kelas}/siswa/{siswa}', [KelasController::class, 'removeSiswa']);
Route::delete('kelas/{kelas}/siswa', [KelasController::class, 'removeAllSiswa']);

Route::apiResource('siswa', SiswaController::class)->parameters([
    'siswa' => 'siswa'
]);

Route::apiResource('mapel', MapelController::class)->parameters([
    'mapel' => 'mapel'
]);

Route::get('nilai', [NilaiController::class, 'index'])->name('nilai.index');
Route::post('nilai', [NilaiController::class, 'insert'])->name('nilai.insert');
Route::delete('nilai', [NilaiController::class, 'destroy'])->name('nilai.destroy');

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
