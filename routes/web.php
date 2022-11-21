<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FileEditController;
use App\Http\Controllers\UnofficialPartController;
use App\Http\Controllers\VoteController;
use App\Http\Controllers\PartEventController;
use App\Http\Controllers\Auth\LoginController;

Route::get('/', function () {
  return view('index');
});

Route::prefix('tracker')->name('tracker.')->group(function () {
  Route::get('/submit', [UnofficialPartController::class, 'create'])->name('submit');
  Route::post('/submit', [UnofficialPartController::class, 'store'])->name('store');
});

// Auth Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout']);

/*
Route::middleware('auth')->group(function () {

  Route::prefix('tracker')->name('tracker.')->group(function () {

//    Route::get('/', function () {
//      return view('tracker.index');
//    })->name('index');

//    Route::get('/activity', [PartEventController::class, 'index'])->name('activity');

//    Route::get('/list', [UnofficialPartController::class, 'index'])->name('list');
//    Route::get('/{part}', [UnofficialPartController::class, 'show'])->name('show');
//    Route::get('/submit', [UnofficialPartController::class, 'create'])->name('submit');
//    Route::post('/submit', [UnofficialPartController::class, 'store'])->name('store');

//    Route::middleware(['auth'])->get('/{part}/vote/create', [VoteController::class, 'create'])->name('vote.create');
//    Route::middleware(['auth'])->get('/vote/{vote}/edit', [VoteController::class, 'edit'])->name('vote.edit');
//    Route::middleware(['auth'])->post('/{part}/vote', [VoteController::class, 'store'])->name('vote.store');
//    Route::middleware(['auth'])->put('/vote/{vote}', [VoteController::class, 'update'])->name('vote.update');
  });

  Route::get('/fileedit', [FileEditController::class, 'show'])->name('fileedit');
  Route::post('/fileedit/save', [FileEditController::class, 'save'])->name('fileedit.save');

});

//Auth::routes(['register' => false, 'reset' => false, 'confirm' => false, 'verify' => false]);

//Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
*/