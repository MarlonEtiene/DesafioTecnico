<?php

use App\Http\Controllers\Api\TaskController;
use Illuminate\Support\Facades\Route;

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

Route::get('/exports/download', [TaskController::class, 'downloadExport'])
    ->name('export.download')
    ->middleware('signed');

// Rota principal que serve a SPA
Route::get('/', function () {
    return view('welcome');
});

// Todas as outras rotas do frontend devem ser redirecionadas para a SPA
Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '.*');
