<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Part\PartReleaseController;
use App\Http\Controllers\SupportFilesController;
use App\Http\Controllers\Omr\SetController;
use App\Http\Controllers\Part\NextReleaseController;
use App\Http\Controllers\Part\PartUpdateController;
use App\Http\Controllers\Part\PartDownloadController;
use App\Http\Controllers\ReviewSummaryController;
use App\Http\Controllers\TrackerHistoryController;
use App\Livewire\Omr\Set\Index;
use App\Livewire\Part\Index as PartIndex;
use App\Livewire\Part\Show;
use App\Livewire\Part\Submit;
use App\Livewire\Part\Weekly;
use App\Livewire\PartEvent\Index as PartEventIndex;
use App\Livewire\Search\Parts;
use App\Livewire\Search\Suffix;
use App\Livewire\Tracker\ConfirmCA;
use App\Livewire\User\Manage;

Route::view('/', 'index')->name('index');

Route::middleware(['throttle:file'])->get('/categories.txt', [SupportFilesController::class, 'categories'])->name('categories-txt');
Route::middleware(['throttle:file'])->get('/library.csv', [SupportFilesController::class, 'librarycsv'])->name('library-csv');
Route::middleware(['throttle:file'])->get('/ptreleases/{output}', [SupportFilesController::class, 'ptreleases'])->name('ptreleases');

Route::prefix('tracker')->name('tracker.')->group(function () {
    Route::view('/', 'tracker.main')->name('main');

    Route::middleware(['auth', 'currentlic'])->get('/submit', Submit::class)->name('submit');

    Route::get('/list', PartIndex::class)->name('index');
    Route::get('/weekly', Weekly::class)->name('weekly');
    Route::get('/history', TrackerHistoryController::class)->name('history');
    Route::get('/summary/{summary}', ReviewSummaryController::class)->name('summary');

    Route::middleware(['auth'])->get('/confirmCA', ConfirmCA::class)->name('confirmCA.show');

    Route::redirect('/search', '/search/part');
    Route::redirect('/suffixsearch', '/search/suffix');

    Route::get('/activity', PartEventIndex::class)->name('activity');

    Route::get('/next-release', NextReleaseController::class)->name('next-release');

    Route::middleware(['can:release.create'])->get('/release/create', [PartReleaseController::class, 'create'])->name('release.create');
    Route::middleware(['can:release.create'])->post('/release/create/2', [PartReleaseController::class, 'createStep2'])->name('release.create2');
    Route::middleware(['can:release.store'])->post('/release/store', [PartReleaseController::class, 'store'])->name('release.store');
    
    Route::get('/{unofficialpart}', Show::class)->name('show.filename');
    Route::get('/{part}', Show::class)->name('show');
});

Route::prefix('omr')->name('omr.')->group(function () {
    Route::view('/', 'omr.main')->name('main');
    Route::get('/sets', Index::class)->name('sets.index');
    Route::resource('sets', SetController::class)->only(['show']);
    Route::get('/set/{setnumber}', [SetController::class, 'show'])->name('show.setnumber');
});


Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/users', Manage::class)->name('users.index');
});


Route::middleware(['auth'])->prefix('dashboard')->name('dashboard.')->group(function () {
//    Route::get('/', UserDashboardController::class)->name('index');
});


Route::get('/updates', [PartUpdateController::class, 'index'])->name('part-update.index');
Route::get('/updates/view{release:short}', [PartUpdateController::class, 'view'])->name('part-update.view');

Route::redirect('/search', '/search/part');
Route::get('/search/part', Parts::class)->name('search.part');
Route::get('/search/suffix', Suffix::class)->name('search.suffix');

Route::prefix('official')->name('official.')->group(function () {
    Route::redirect('/search', '/search/part');
    Route::redirect('/suffixsearch', '/search/suffix');
    Route::get('/list', PartIndex::class)->name('index');
    Route::get('/{officialpart}', Show::class)->name('show.filename');
    Route::get('/{part}', Show::class)->name('show');
});

Route::redirect('/login', 'https://forums.ldraw.org/member.php?action=login');
Route::redirect('/documentation', 'https://www.ldraw.org/docs-main.html')->name('doc');

Route::middleware(['throttle:file'])->get('/library/official/{officialpart}', PartDownloadController::class)->name('official.download');
Route::middleware(['throttle:file'])->get('/library/unofficial/{unofficialpart}', PartDownloadController::class)->name('unofficial.download');

Route::middleware(['auth'])->get('/logout', function () {
    auth()->logout();
    return back();
});

Route::middleware(['can:assume-user'])->get('/login-user-{number}', function (int $number) {
    if (app()->environment() === 'local') {
        auth()->logout();
        auth()->login(\App\Models\User::find($number));
        return back();      
    }
    
    return back();
});



