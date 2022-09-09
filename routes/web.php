<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//
Route::middleware('auth')->group(function () {
Route::get('/', function () {
    return view('index');
});

Route::get('/edit', function () {
    return view('edit');
})->middleware('auth');

Route::get('/edit/files', function () {
    return response()->json(['message' => 'This is JSON!']);
})->middleware('auth');

Route::prefix('tracker')->name('tracker.')->group(function () {
  Route::get('/', function () {
    return view('tracker.index');
  })->name('index');
  Route::get('/activity', [PartEventController::class, 'index'])->name('activity');
  Route::get('/list', [UnofficialPartController::class, 'index'])->name('list');
  Route::get('/submit', [UnofficialPartController::class, 'create'])->name('submit');
  Route::get('/{part}', [UnofficialPartController::class, 'show'])->name('show');
  Route::middleware(['auth'])->get('/{part}/vote/create', [VoteController::class, 'create'])->name('vote.create');
  Route::middleware(['auth'])->get('/vote/{vote}/edit', [VoteController::class, 'edit'])->name('vote.edit');
  Route::middleware(['auth'])->post('/{part}/vote', [VoteController::class, 'store'])->name('vote.store');
  Route::middleware(['auth'])->put('/vote/{vote}', [VoteController::class, 'update'])->name('vote.update');
});
});
Auth::routes(['register' => false]);

//Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
