<?php

use App\Http\Controllers\Part\LatestPartsController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Search\QuickSearchController;
use App\Http\Controllers\Part\PartWebGLController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::get('/search/quicksearch', [QuickSearchController::class, 'index'])->name('search.quicksearch');
Route::get('/{part}/ldbi', [PartWebGLController::class, 'show'])->name('part.ldbi');
Route::get('/tracker/latest-parts', [LatestPartsController::class, 'index'])->name('part.latest');

