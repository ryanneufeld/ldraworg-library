<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\PartController;
use App\Http\Controllers\VoteController;
use App\Http\Controllers\PartEventController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ReleaseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SupportFilesController;

use App\Models\Part;

use App\LDraw\LibraryOperations;

Route::redirect('/', '/tracker');

Route::get('/categories.txt', [SupportFilesController::class, 'categories'])->name('categories-txt');
Route::get('/library.csv', [SupportFilesController::class, 'librarycsv'])->name('library-csv');
Route::get('/ptreleases', [SupportFilesController::class, 'ptreleases'])->name('ptreleases');


/*
// Only enable this route for testing
Route::get('/user-233', function () {
  Auth::logout();
  Auth::login(\App\Models\User::find(233));
  return response()->redirectTo('/', 302);
});
*/

Route::get('/ldbi/{part}/parts', function (Part $part) {
  \App\LDraw\WebGL::WebGLPart($part, $parts, true, $part->isUnofficial());
  return response(json_encode($parts));
});

Route::prefix('tracker')->name('tracker.')->group(function () {
  Route::view('/', 'tracker.main')->name('main');

  Route::get('/submit', [PartController::class, 'create'])->name('submit');
  Route::post('/submit', [PartController::class, 'store'])->name('store');

  Route::get('/list', [PartController::class, 'index'])->name('index');
  Route::get('/weekly', [PartController::class, 'weekly'])->name('weekly');

  Route::get('/{part}/edit', [PartController::class, 'editheader'])->name('editheader');
  Route::put('/{part}/edit', [PartController::class, 'doeditheader'])->name('doeditheader');

  Route::get('/{part}/move', [PartController::class, 'move'])->name('move');
  Route::put('/{part}/move', [PartController::class, 'domove'])->name('domove');

  Route::get('/{part}/updateimage', [PartController::class, 'updateimage'])->name('updateimage');

  Route::delete('/{part}/delete', [PartController::class, 'domove'])->name('destroy');

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

/*
Route::get('/dailydigest', function () {  
  $yesterday = date_create('2023-01-12');
  $today = date_add(clone $yesterday, new \DateInterval('P1D'));

  $user = App\Models\User::findByName('Philo');

  $events = App\Models\PartEvent::whereBetween('created_at', [$yesterday, $today])
    ->whereIn('part_id', $user->notification_parts->pluck('id'))->get();

  return new App\Mail\DailyDigest($yesterday, $events);
});
*/

Route::prefix('admin')->name('admin.')->group(function () {
  Route::resource('users', UserController::class);
});

Route::prefix('dashboard')->name('dashboard.')->group(function () {
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

