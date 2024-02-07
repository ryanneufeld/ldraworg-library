<?php

use App\Http\Controllers\AdminDashboardController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Part\PartController;
use App\Http\Controllers\Search\PartSearchController;
use App\Http\Controllers\Search\SuffixSearchController;
use App\Http\Controllers\Part\PartReleaseController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SupportFilesController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\CaConfirmController;
use App\Http\Controllers\Omr\SetController;
use App\Http\Controllers\Part\DatDiffController;
use App\Http\Controllers\Part\NonAdminReleaseController;
use App\Http\Controllers\Part\PartUpdateController;
use App\Http\Controllers\Part\PartMoveController;
use App\Http\Controllers\Part\PartDownloadController;
use App\Http\Controllers\ReviewSummaryController;
use App\Http\Controllers\TrackerHistoryController;
use App\Http\Controllers\UserDashboardController;
use App\Livewire\Part\PartList;
use App\Livewire\Part\Show;
use App\Livewire\Part\Submit;
use App\Livewire\Part\Weekly;
use App\Livewire\PartEventsShow;

Route::view('/', 'index')->name('index');

Route::get('/categories.txt', [SupportFilesController::class, 'categories'])->name('categories-txt');
Route::get('/library.csv', [SupportFilesController::class, 'librarycsv'])->name('library-csv');

Route::prefix('tracker')->name('tracker.')->group(function () {
    Route::view('/', 'tracker.main')->name('main');

    Route::middleware(['auth', 'currentlic'])->get('/submit', Submit::class)->name('submit');

    Route::get('/list', PartList::class)->name('index');
    Route::get('/weekly', Weekly::class)->name('weekly');
    Route::get('/history', TrackerHistoryController::class)->name('history');
    Route::get('/summary/{summary}', [ReviewSummaryController::class, 'show'])->name('summary');

    Route::middleware(['auth'])->get('/{part}/edit', [PartController::class, 'edit'])->name('edit');
    Route::middleware(['auth'])->put('/{part}/edit', [PartController::class, 'update'])->name('update');

    Route::middleware(['auth'])->get('/{part}/move', [PartMoveController::class, 'edit'])->name('move.edit');
    Route::middleware(['auth'])->put('/{part}/move', [PartMoveController::class, 'update'])->name('move.update');

    Route::middleware(['auth'])->get('/{part}/updateimage', [PartController::class, 'updateimage'])->name('updateimage');
    Route::middleware(['auth'])->get('/{part}/updatesubparts', [PartController::class, 'updatesubparts'])->name('updatesubparts');

    Route::middleware(['auth'])->get('/confirmCA', [CaConfirmController::class, 'edit'])->name('confirmCA.show');
    Route::middleware(['auth'])->put('/confirmCA', [CaConfirmController::class, 'update'])->name('confirmCA.store');

    Route::redirect('/search', '/search/part');
    Route::redirect('/suffixsearch', '/search/suffix');

    Route::get('/activity', PartEventsShow::class)->name('activity');

    Route::get('/next-release', NonAdminReleaseController::class)->name('next-release');

    Route::middleware(['can:release.create'])->get('/release/create', [PartReleaseController::class, 'create'])->name('release.create');
    Route::middleware(['can:release.create'])->post('/release/create/2', [PartReleaseController::class, 'createStep2'])->name('release.create2');
    Route::middleware(['can:release.store'])->post('/release/store', [PartReleaseController::class, 'store'])->name('release.store');
    
    Route::get('/{part}/diff/{part2}', [DatDiffController::class, 'show'])->name('datdiff.download');
    Route::get('/diff', [DatDiffController::class, 'index'])->name('datdiff');

    Route::get('/{unofficialpart}', Show::class)->name('show.filename');
    Route::get('/{part}', Show::class)->name('show');
});

Route::prefix('omr')->name('omr.')->group(function () {
    Route::view('/', 'omr.main')->name('main');
    Route::resource('sets', SetController::class)->only(['index', 'show']);
});

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('dashboard', AdminDashboardController::class)->middleware('can:admin.view-dashboard')->name('dashboard');
    Route::resource('users', UserController::class);
    Route::resource('roles', RoleController::class);
    Route::resource('review-summaries', ReviewSummaryController::class)->except(['create', 'show']);
});

Route::middleware(['auth'])->prefix('dashboard')->name('dashboard.')->group(function () {
    Route::get('/', UserDashboardController::class)->name('index');
});

Route::get('/updates', [PartUpdateController::class, 'index'])->name('part-update.index');
Route::get('/updates/view{release:short}', [PartUpdateController::class, 'view'])->name('part-update.view');

Route::redirect('/search', '/search/part');
Route::get('/search/part', PartSearchController::class)->name('search.part');
Route::get('/search/suffix', SuffixSearchController::class)->name('search.suffix');

Route::prefix('official')->name('official.')->group(function () {
    Route::redirect('/search', '/search/part');
    Route::redirect('/suffixsearch', '/search/suffix');
    Route::get('/list', PartList::class)->name('index');
    Route::get('/{officialpart}', Show::class)->name('show.filename');
    Route::get('/{part}', Show::class)->name('show');
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


