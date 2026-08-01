<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReplyController;
use App\Http\Controllers\ServerStatusController;
use App\Http\Controllers\ThreadController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/register', [RegisterController::class, 'create'])->name('register');
Route::post('/register', [RegisterController::class, 'store']);

Route::get('/login', [LoginController::class, 'create'])->name('login');
Route::post('/login', [LoginController::class, 'store']);
Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit')->middleware('auth');
Route::post('/profile/edit', [ProfileController::class, 'update'])->name('profile.update')->middleware('auth');
Route::get('/profile/mc-bind', [ProfileController::class, 'mcBind'])->name('profile.mc-bind')->middleware('auth');
Route::post('/profile/mc-bind', [ProfileController::class, 'mcBindUpdate'])->name('profile.mc-bind.update')->middleware('auth');
Route::get('/profile/{user}', [ProfileController::class, 'show'])->name('profile.show');

Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
Route::get('/categories/{category:slug}', [CategoryController::class, 'show'])->name('categories.show');

Route::get('/threads/create', [ThreadController::class, 'create'])->name('threads.create')->middleware('auth');
Route::post('/threads', [ThreadController::class, 'store'])->name('threads.store')->middleware('auth');
Route::get('/threads/{thread:slug}', [ThreadController::class, 'show'])->name('threads.show');
Route::get('/threads/{thread:slug}/edit', [ThreadController::class, 'edit'])->name('threads.edit')->middleware('auth');
Route::put('/threads/{thread:slug}', [ThreadController::class, 'update'])->name('threads.update')->middleware('auth');
Route::delete('/threads/{thread:slug}', [ThreadController::class, 'destroy'])->name('threads.destroy')->middleware('auth');

Route::post('/threads/{thread:slug}/replies', [ReplyController::class, 'store'])->name('replies.store')->middleware('auth');
Route::get('/replies/{reply}/edit', [ReplyController::class, 'edit'])->name('replies.edit')->middleware('auth');
Route::put('/replies/{reply}', [ReplyController::class, 'update'])->name('replies.update')->middleware('auth');
Route::delete('/replies/{reply}', [ReplyController::class, 'destroy'])->name('replies.destroy')->middleware('auth');

Route::get('/api/server-status', [ServerStatusController::class, 'index'])->name('server-status');
