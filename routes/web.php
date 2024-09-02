<?php

use App\Http\Controllers\MyAnimeListController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/auth/mal', [MyAnimeListController::class, 'redirectToProvider']);
Route::get('/auth/mal/callback', [MyAnimeListController::class, 'handleProviderCallback']);
Route::get('/user/anime-list', [MyAnimeListController::class, 'getUserAnimeList']);
Route::post('/user/anime/add-episode/{id}/{quantity}', [MyAnimeListController::class, 'addEpisode'])->name('add-episode');
Route::get('/anime/{id}', [MyAnimeListController::class, 'getAnime']);
