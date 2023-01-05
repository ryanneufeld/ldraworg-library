<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\UnofficialPartController;
use App\Http\Controllers\OfficialPartController;
use App\Http\Controllers\VoteController;
use App\Http\Controllers\PartEventController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ReleaseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\LoginController;
use App\Models\Part;
use App\Models\Vote;
use App\LDraw\PartCheck;
use App\LDraw\LibraryOperations;
use App\Models\PartCategory;

Route::get('/categories.txt', function () {
  return response(LibraryOperations::categoriesText())->header('Content-Type','text/plain');
})->name('categories-txt');

Route::get('/ptreleases', function (Request $request) {
  $output = $request->get('output');
  $releases = LibraryOperations::ptreleases($request->get('output'), $request->get('type'), $request->get('fields'));
  if ($output == 'TAB') {
    return response($releases)->header('Content-Type','text/plain');
  }
  else {
    return response($releases)->header('Content-Type','application/xml');    
  }
})->name('ptreleases-cgi');

/*
// Only enable this route for testing
Route::get('/user-224', function () {
  Auth::logout();
  Auth::login(\App\Models\User::find(224));
});
*/

Route::get('/ldbi/{part}/parts', function (Part $part) {
  \App\LDraw\WebGL::WebGLPart($part, $parts, true);
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

  Route::middleware(['auth'])->match(['get', 'post'], '/release/{step?}', [ReleaseController::class, 'create'])->name('release.create');
  
  // These have to be last
  Route::put('/{part}', [UnofficialPartController::class, 'update'])->name('update');
  Route::get('/{part}', [UnofficialPartController::class, 'show'])->where('part', '[a-z0-9_/.-]+')->name('show');
});

Route::prefix('dashboard')->name('dashboard.')->group(function () {
  Route::get('/', [DashboardController::class, 'index'])->name('index');
});

Route::get('/updates', [ReleaseController::class, 'index'])->name('release.index');

Route::redirect('/search', '/tracker/search');

Route::prefix('official')->name('official.')->group(function () {
  Route::get('/list', [OfficialPartController::class, 'index'])->name('index');
  Route::view('/orphans', 'official.list', ['parts' => 
    Part::whereRelation('release','short','<>','unof')->
    whereRelation('type', 'folder', '<>', 'parts/')->
    where('description', 'not like', '%obsolete%')->
    whereDoesntHave('parents')->get()
  ]);
  Route::redirect('/search', '/tracker/search');
  // This has to be last
  Route::get('/{part}', [OfficialPartController::class, 'show'])->where('part', '[a-z0-9_/.-]+')->name('show');
});

Route::middleware(['auth'])->get('/test', function () {
  $users = App\Models\User::withCount(['parts' => function (Illuminate\Database\Eloquent\Builder $query) {
    $query->whereRelation('release','short', 'like', '22%');
  }])->get();
  return view('test', ['users' => $users]);
})->name('test');

