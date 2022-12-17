<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\UnofficialPartController;
use App\Http\Controllers\OfficialPartController;
use App\Http\Controllers\VoteController;
use App\Http\Controllers\PartEventController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\LoginController;
use App\Models\Part;
use App\LDraw\LibraryUtils;
use App\LDraw\PartCheck;
use App\Models\PartCategory;

Route::get('/categories.txt', function () {
  return response(implode("\n", PartCategory::all()->pluck('category')->all()))->header('Content-Type','text/plain');
})->name('main');

/*
// Only enable this route for testing
Route::get('/user-224', function () {
  Auth::logout();
  Auth::login(\App\Models\User::find(224));
});
*/

Route::get('/ldbi/{part}/parts', function (Part $part) {
  LibraryUtils::WebGLPart($part, $parts, true);
  return response(json_encode($parts));
});

Route::prefix('tracker')->name('tracker.')->group(function () {
  Route::view('/', 'tracker.main', ['summary' => Part::whereRelation('release','short','unof')->pluck('vote_sort')->countBy()->all()])->name('main');

  Route::get('/submit', [UnofficialPartController::class, 'create'])->name('submit');
  Route::post('/submit', [UnofficialPartController::class, 'store'])->name('store');

  Route::get('/list', [UnofficialPartController::class, 'index'])->name('index');
  Route::get('/weekly', [UnofficialPartController::class, 'weekly'])->name('weekly');
  Route::get('/{part}/edit', [UnofficialPartController::class, 'edit'])->name('edit');

  Route::get('/search', [SearchController::class, 'partsearch'])->name('search');
  Route::get('/suffixsearch', [SearchController::class, 'suffixsearch'])->name('suffixsearch');

  Route::middleware(['auth'])->get('/{part}/vote/create', [VoteController::class, 'create'])->name('vote.create');
  Route::middleware(['auth'])->get('/vote/{vote}/edit', [VoteController::class, 'edit'])->name('vote.edit');
  Route::middleware(['auth'])->post('/{part}/vote', [VoteController::class, 'store'])->name('vote.store');
  Route::middleware(['auth'])->put('/vote/{vote}', [VoteController::class, 'update'])->name('vote.update');

  Route::get('/activity', [PartEventController::class, 'index'])->name('activity');

  // These have to be last
  Route::put('/{part}', [UnofficialPartController::class, 'update'])->name('update');
  Route::get('/{part}', [UnofficialPartController::class, 'show'])->where('part', '[a-z0-9_/.-]+')->name('show');
});

Route::prefix('dashboard')->name('dashboard.')->group(function () {
  Route::get('/', [DashboardController::class, 'index'])->name('index');
  Route::get('/submits', [DashboardController::class, 'submits'])->name('submits');
  Route::get('/votes', [DashboardController::class, 'votes'])->name('votes');
  Route::get('/notifications', [DashboardController::class, 'notifications'])->name('notifications');
});

Route::redirect('/search', '/tracker/search');
Route::post('/search', [SearchController::class, 'dopartssearch'])->name('dosearch');

Route::prefix('official')->name('official.')->group(function () {
  Route::get('/list', [OfficialPartController::class, 'index'])->name('index');
  // This has to be last
  Route::get('/{part}', [OfficialPartController::class, 'show'])->where('part', '[a-z0-9_/.-]+')->name('show');
});

Route::middleware(['auth'])->get('/test', function () {
  $data = '';
  return view('test', ['data' => $data]);
})->name('test');
