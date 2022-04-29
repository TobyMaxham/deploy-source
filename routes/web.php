<?php

use App\Http\Controllers\DeployController;
use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;
use TobyMaxham\Logger\LogViewerController;

Route::get('/login', [LoginController::class, 'login'])->name('login');
Route::get('/login/callback', [LoginController::class, 'callback']);
Route::post('/webhook/bitbucket', [DeployController::class, 'deployFromBitbucket']);

Route::group(['middleware' => 'auth.simple'], function() {
    Route::get('/', [DeployController::class, 'index'])->name('index');
    Route::post('/', [DeployController::class, 'deploy']);
    Route::get('log', [LogViewerController::class, 'index']);
});
