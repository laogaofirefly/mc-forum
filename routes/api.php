<?php

use App\Http\Controllers\ServerStatusController;
use Illuminate\Support\Facades\Route;

Route::get('/server-status', [ServerStatusController::class, 'index'])->name('api.server-status');
