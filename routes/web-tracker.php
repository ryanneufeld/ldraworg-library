<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Database\Eloquent\Builder;

use App\Http\Controllers\UserController;
use App\Http\Controllers\OfficialPartController;
use App\Http\Controllers\UnofficialPartController;
use App\Http\Controllers\PartEventController;
use App\Http\Controllers\VoteController;
use App\Models\PartEvent;
use App\Models\PartEventType;
use App\Models\User;
use App\Models\Part;

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

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->prefix('dashboard')->name('dashboard.')->group(function () {

  Route::get('/', function () {
    return view('dashboard.index');
  });

  Route::get('/submits', function () {
    $events = Auth::user()
      ->part_events()
      ->with('part')
      ->where('part_event_type_id', PartEventType::firstWhere('slug','submit')->id)->get()
      ->sortBy('part.description')
      ->unique('part.filename')->values()->all();
    return view('dashboard.submits', ['events' => $events]);
  })->name('submits');

  Route::get('/votes', function () {
    return view('dashboard.votes');
  })->name('votes');  
  
  Route::get('/notifications', function () {
    return view('dashboard.notifications');
  })->name('notifications');  
});



Route::middleware(['auth'])->resource('users', UserController::class);

Route::prefix('library')->name('library.')->group(function () {
  Route::resource('official', OfficialPartController::class)
    ->only(['index', 'show'])
    ->parameters(['official' => 'part']);

  Route::prefix('tracker')->name('tracker.')->group(function () {
    Route::get('/', function () {
      return view('library.tracker.index');
    })->name('index');
    Route::get('/activity', [PartEventController::class, 'index'])->name('activity');
    Route::get('/list', [UnofficialPartController::class, 'index'])->name('list');
    Route::get('/submit', [UnofficialPartController::class, 'create'])->name('submit');
    Route::get('/{part}', [UnofficialPartController::class, 'show'])->name('show');
//    Route::resource('part', UnofficialPartController::class)->only([
//    'create'
//    ]);
//    Route::get('/create', [UnofficialPartController::class, 'create'])->name('create');
    Route::middleware(['auth'])->get('/{part}/vote/create', [VoteController::class, 'create'])->name('vote.create');
    Route::middleware(['auth'])->get('/vote/{vote}/edit', [VoteController::class, 'edit'])->name('vote.edit');
    Route::middleware(['auth'])->post('/{part}/vote', [VoteController::class, 'store'])->name('vote.store');
    Route::middleware(['auth'])->put('/vote/{vote}', [VoteController::class, 'update'])->name('vote.update');
  });
});  

//Auth

// Temp analytics links
Route::middleware(['auth'])->get('/usermetrics', function () {
    return view('usermetric', ['users' => User::with('part_histories')->withCount('parts')->orderBy('parts_count', 'desc')->get()]);
});

Route::get('/statuscodecheck', function () {
  set_time_limit(0);
  $partlist = file_get_contents('http://www.ldraw.org/cgi-bin/ptcodes.cgi');
  $partlist = explode("\n", $partlist);
  $list = [];
  foreach ($partlist as $line) {
    if (!empty($line)) {
      $line = explode(',', $line);
      $list[$line[0]] = $line[1];
    }  
  }
  foreach(Part::where('unofficial', true)->lazy() as $part) {
    if (isset($list[$part->filename]) && $list[$part->filename] <> $part->vote_sort) {
      $parts[] = ['part' => $part, 'tracker' => $list[$part->filename]];
    }
  }
  return view('statuscodecheck', ['parts' => $parts]);
});

Route::get('/parthistmismatch', function () {
  $parts = Part::with(['history', 'events'])->where('unofficial', true)->lazy();
  foreach ($parts as $part) {
    $hist_authors = $part->history->pluck('user_id')->all();
    $hist_authors = array_merge([$part->user_id], $hist_authors);
    $events = $part->events
      ->filter(function ($event) {
        return $event->part_event_type_id == 2;
      })->values();
    $event_authors = $events->pluck('user_id')->all();  
    $event_authors = array_merge([$part->user_id], $event_authors);
    $diff = array_diff($event_authors,$hist_authors);
    if (!empty($diff)) {
      $authors = User::whereIn('id', $diff)->get()->pluck('name')->all();
      $diffp[] = ['part' => $part, 'authors' => $authors];
    }
  }
  return view('parthistmismatch', ['diffp' => $diffp]);
});

require __DIR__.'/auth.php';
