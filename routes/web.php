<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Part\PartController;
use App\Http\Controllers\VoteController;
use App\Http\Controllers\Part\PartEventController;
use App\Http\Controllers\Search\PartSearchController;
use App\Http\Controllers\Search\SuffixSearchController;
use App\Http\Controllers\Part\PartReleaseController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SupportFilesController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\CaConfirmController;
use App\Http\Controllers\Part\PartUpdateController;
use App\Http\Controllers\Part\PartMoveController;
use App\Http\Controllers\Part\PartDownloadController;

Route::view('/', 'index')->name('index');

Route::get('/categories.txt', [SupportFilesController::class, 'categories'])->name('categories-txt');
Route::get('/library.csv', [SupportFilesController::class, 'librarycsv'])->name('library-csv');

Route::prefix('tracker')->name('tracker.')->group(function () {
    Route::view('/', 'tracker.main')->name('main');

    Route::middleware(['auth', 'currentlic'])->get('/submit', [PartController::class, 'create'])->name('submit');
    Route::middleware(['auth', 'currentlic'])->post('/submit', [PartController::class, 'store'])->name('store');

    Route::get('/list', [PartController::class, 'index'])->name('index');
    Route::get('/weekly', \App\Http\Controllers\Part\WeeklyPartController::class)->name('weekly');
    Route::get('/history', \App\Http\Controllers\TrackerHistoryController::class)->name('history');
    Route::get('/summary/{summary}', [\App\Http\Controllers\ReviewSummaryController::class, 'show'])->name('summary');

    Route::middleware(['auth'])->get('/{part}/edit', [PartController::class, 'edit'])->name('edit');
    Route::middleware(['auth'])->put('/{part}/edit', [PartController::class, 'update'])->name('update');

    Route::middleware(['auth'])->get('/{part}/move', [PartMoveController::class, 'edit'])->name('move.edit');
    Route::middleware(['auth'])->put('/{part}/move', [PartMoveController::class, 'update'])->name('move.update');

    Route::middleware(['auth'])->get('/{part}/updateimage', [PartController::class, 'updateimage'])->name('updateimage');
    Route::middleware(['auth'])->get('/{part}/updatesubparts', [PartController::class, 'updatesubparts'])->name('updatesubparts');

    Route::middleware(['auth'])->get('/{part}/delete', [PartController::class, 'delete'])->withTrashed()->name('delete');
    Route::middleware(['auth'])->delete('/{part}/delete', [PartController::class, 'destroy'])->withTrashed()->name('destroy');

    Route::middleware(['auth'])->get('/confirmCA', [CaConfirmController::class, 'edit'])->name('confirmCA.show');
    Route::middleware(['auth'])->put('/confirmCA', [CaConfirmController::class, 'update'])->name('confirmCA.store');

    Route::redirect('/search', '/search/part');
    Route::redirect('/suffixsearch', '/search/suffix');

    Route::middleware(['auth'])->get('/{part}/vote/create', [VoteController::class, 'create'])->name('vote.create');
    Route::middleware(['auth'])->get('/vote/{vote}/edit', [VoteController::class, 'edit'])->name('vote.edit');
    Route::middleware(['auth'])->post('/{part}/vote', [VoteController::class, 'store'])->name('vote.store');
    Route::middleware(['auth'])->put('/vote/{vote}', [VoteController::class, 'update'])->name('vote.update');

    Route::middleware(['auth'])->get('/{part}/adminquickvote', [VoteController::class, 'adminquickvote'])->name('vote.adminquickvote');

    Route::get('/activity', PartEventController::class)->name('activity');

    Route::get('/next-release', \App\Http\Controllers\Part\NonAdminReleaseController::class)->name('next-release');

    Route::middleware(['can:release.create'])->get('/release/create', [PartReleaseController::class, 'create'])->name('release.create');
    Route::middleware(['can:release.create'])->post('/release/create/2', [PartReleaseController::class, 'createStep2'])->name('release.create2');
    Route::middleware(['can:release.store'])->post('/release/store', [PartReleaseController::class, 'store'])->name('release.store');
    
    Route::get('/{part}/diff/{part2}', [\App\Http\Controllers\Part\DatDiffController::class, 'show'])->name('datdiff.download');
    Route::get('/diff', [\App\Http\Controllers\Part\DatDiffController::class, 'index'])->name('datdiff');

    Route::get('/{unofficialpart}', \App\Livewire\Part\Show::class)->name('show.filename');
    Route::get('/{part}', \App\Livewire\Part\Show::class)->name('show');
});

Route::prefix('omr')->name('omr.')->group(function () {
    Route::view('/', 'omr.main')->name('main');
    Route::resource('sets', \App\Http\Controllers\Omr\SetController::class)->only(['index', 'show']);
});

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('dashboard', \App\Http\Controllers\AdminDashboardController::class)->middleware('can:admin.view-dashboard')->name('dashboard');
    Route::resource('users', UserController::class);
    Route::resource('roles', RoleController::class);
    Route::resource('review-summaries', \App\Http\Controllers\ReviewSummaryController::class)->except(['create', 'show']);
});

Route::middleware(['auth'])->prefix('dashboard')->name('dashboard.')->group(function () {
    Route::get('/', \App\Http\Controllers\UserDashboardController::class)->name('index');
});

Route::get('/updates', [PartUpdateController::class, 'index'])->name('part-update.index');
Route::get('/updates/view{release:short}', [PartUpdateController::class, 'view'])->name('part-update.view');

Route::redirect('/search', '/search/part');
Route::get('/search/part', PartSearchController::class)->name('search.part');
Route::get('/search/suffix', SuffixSearchController::class)->name('search.suffix');

Route::prefix('official')->name('official.')->group(function () {
    Route::redirect('/search', '/search/part');
    Route::redirect('/suffixsearch', '/search/suffix');
    Route::get('/list', [PartController::class, 'index'])->name('index');
    Route::get('/{officialpart}', \App\Livewire\Part\Show::class)->name('show.filename');
    Route::get('/{part}', \App\Livewire\Part\Show::class)->name('show');
});

Route::redirect('/login', 'https://forums.ldraw.org/member.php?action=login');
Route::redirect('/documentation', 'https://www.ldraw.org/docs-main.html')->name('doc');

Route::get('/library/official/{officialpart}', PartDownloadController::class)->name('official.download');
Route::get('/library/unofficial/{unofficialpart}', PartDownloadController::class)->name('unofficial.download');


// Only enable this route for testing
/*
Route::get('/login-user-291', function () {
  Auth::logout();
  Auth::login(\App\Models\User::find(291));
  return back();
});
*/


