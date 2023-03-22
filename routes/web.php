<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\PartController;
use App\Http\Controllers\VoteController;
use App\Http\Controllers\PartEventController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ReleaseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SupportFilesController;
use App\Http\Controllers\RoleController;

Route::redirect('/', '/tracker');

Route::get('/categories.txt', [SupportFilesController::class, 'categories'])->name('categories-txt');
Route::get('/library.csv', [SupportFilesController::class, 'librarycsv'])->name('library-csv');
Route::get('/ptreleases', [SupportFilesController::class, 'ptreleases'])->name('ptreleases');

Route::get('/ldbi/{part}/parts', [PartController::class, 'webgl']);

Route::prefix('tracker')->name('tracker.')->group(function () {
  Route::view('/', 'tracker.main')->name('main');

  Route::middleware(['auth'])->get('/submit', [PartController::class, 'create'])->name('submit');
  Route::middleware(['auth'])->post('/submit', [PartController::class, 'store'])->name('store');

  Route::get('/list', [PartController::class, 'index'])->name('index');
  Route::get('/weekly', [PartController::class, 'weekly'])->name('weekly');

  Route::middleware(['auth'])->get('/{part}/edit', [PartController::class, 'editheader'])->name('editheader');
  Route::middleware(['auth'])->put('/{part}/edit', [PartController::class, 'doeditheader'])->name('doeditheader');

  Route::middleware(['auth'])->get('/{part}/move', [PartController::class, 'move'])->name('move');
  Route::middleware(['auth'])->put('/{part}/move', [PartController::class, 'domove'])->name('domove');

  Route::middleware(['auth'])->get('/{part}/updateimage', [PartController::class, 'updateimage'])->name('updateimage');
  Route::middleware(['auth'])->get('/{part}/updatesubparts', [PartController::class, 'updatesubparts'])->name('updatesubparts');

  Route::middleware(['auth'])->get('/{missingpart}/updatemissing', [PartController::class, 'updatemissing'])->name('updatemissing');
  Route::middleware(['auth'])->put('/{missingpart}/updatemissing', [PartController::class, 'doupdatemissing'])->name('doupdatemissing');

  Route::middleware(['auth'])->delete('/{part}/delete', [PartController::class, 'destroy'])->withTrashed()->name('destroy');

  Route::get('/search', [SearchController::class, 'partsearch'])->name('search');
  Route::get('/suffixsearch', [SearchController::class, 'suffixsearch'])->name('suffixsearch');

  Route::middleware(['auth'])->get('/{part}/vote/create', [VoteController::class, 'create'])->name('vote.create');
  Route::middleware(['auth'])->get('/vote/{vote}/edit', [VoteController::class, 'edit'])->name('vote.edit');
  Route::middleware(['auth'])->post('/{part}/vote', [VoteController::class, 'store'])->name('vote.store');
  Route::middleware(['auth'])->put('/vote/{vote}', [VoteController::class, 'update'])->name('vote.update');

  Route::get('/activity', [PartEventController::class, 'index'])->name('activity');

  Route::middleware(['auth'])->match(['get', 'post'], '/release/create/{step?}', [ReleaseController::class, 'create'])->name('release.create');
  
  Route::get('/{unofficialpart}', [PartController::class, 'show'])->name('show.filename');
  Route::get('/{part}', [PartController::class, 'show'])->name('show');
});

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
  Route::resource('users', UserController::class);
  Route::resource('roles', RoleController::class);
});

Route::middleware(['auth'])->prefix('dashboard')->name('dashboard.')->group(function () {
  Route::get('/', [DashboardController::class, 'index'])->name('index');
});

Route::get('/updates', [ReleaseController::class, 'index'])->name('release.index');
Route::get('/updates/view{release:short}', [ReleaseController::class, 'view'])->name('release.view');

Route::redirect('/search', '/tracker/search');

Route::prefix('official')->name('official.')->group(function () {
  Route::redirect('/search', '/tracker/search');
  Route::get('/list', [PartController::class, 'index'])->name('index');
  Route::get('/{officialpart}', [PartController::class, 'show'])->name('show.filename');
  Route::get('/{part}', [PartController::class, 'show'])->name('show');
});

Route::redirect('/login', 'https://forums.ldraw.org/member.php?action=login');

Route::get('/library/official/{officialpart}', [PartController::class, 'download'])->name('official.download');
Route::get('/library/unofficial/{unofficialpart}', [PartController::class, 'download'])->name('unofficial.download');

/*
// Only enable this route for testing
Route::get('/login-user-233', function () {
  Auth::logout();
  Auth::login(\App\Models\User::find(233));
  return response()->redirectTo('/', 302);
});
*/

