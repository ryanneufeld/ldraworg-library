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
use App\Http\Controllers\UserController;

use App\Models\Part;

use App\LDraw\LibraryOperations;

Route::redirect('/', '/tracker');

Route::get('/categories.txt', function () {
  return response(LibraryOperations::categoriesText())->header('Content-Type','text/plain');
})->name('categories-txt');

/*
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
  Route::view('/', 'tracker.main', ['summary' => null/*Part::whereRelation('release','short','unof')->pluck('vote_sort')->countBy()->all()*/])->name('main');

  Route::get('/submit', [UnofficialPartController::class, 'create'])->name('submit');
  Route::post('/submit', [UnofficialPartController::class, 'store'])->name('store');

  Route::get('/list', [UnofficialPartController::class, 'index'])->name('index');
  Route::get('/weekly', [UnofficialPartController::class, 'weekly'])->name('weekly');

  Route::get('/{part}/edit', [UnofficialPartController::class, 'editheader'])->name('editheader');
  Route::put('/{part}/edit', [UnofficialPartController::class, 'doeditheader'])->name('doeditheader');

  Route::get('/{part}/move', [UnofficialPartController::class, 'move'])->name('move');
  Route::put('/{part}/move', [UnofficialPartController::class, 'domove'])->name('domove');

  Route::delete('/{part}/delete', [UnofficialPartController::class, 'domove'])->name('destroy');

  Route::get('/search', [SearchController::class, 'partsearch'])->name('search');
  Route::get('/suffixsearch', [SearchController::class, 'suffixsearch'])->name('suffixsearch');

  Route::middleware(['auth'])->get('/{part}/vote/create', [VoteController::class, 'create'])->name('vote.create');
  Route::middleware(['auth'])->get('/vote/{vote}/edit', [VoteController::class, 'edit'])->name('vote.edit');
  Route::middleware(['auth'])->post('/{part}/vote', [VoteController::class, 'store'])->name('vote.store');
  Route::middleware(['auth'])->put('/vote/{vote}', [VoteController::class, 'update'])->name('vote.update');

  Route::get('/activity', [PartEventController::class, 'index'])->name('activity');

  Route::middleware(['auth'])->match(['get', 'post'], '/release/create/{step?}', [ReleaseController::class, 'create'])->name('release.create');
  
  // These have to be last
  Route::get('/{unofficialpart}', [UnofficialPartController::class, 'show'])->name('show');
  Route::get('/{part}', [UnofficialPartController::class, 'show'])->name('show');
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
  Route::get('/list', [OfficialPartController::class, 'index'])->name('index');
/*
  Route::view('/orphans', 'official.list', ['parts' => 
    Part::whereRelation('release','short','<>','unof')->
    whereRelation('type', 'folder', '<>', 'parts/')->
    where('description', 'not like', '%obsolete%')->
    whereDoesntHave('parents')->get()
  ]);
*/  
  Route::redirect('/search', '/tracker/search');
  // This has to be last
  Route::get('/{officialpart}', [OfficialPartController::class, 'show'])->name('show');
  Route::get('/{part}', [OfficialPartController::class, 'show'])->name('show');
});

Route::get('/library/official/ldraw/{officialpart}', [OfficialPartController::class, 'download'])->name('official.download');
Route::get('/library/unofficial/{unofficialpart}', [UnofficialPartController::class, 'download'])->name('unofficial.download');

