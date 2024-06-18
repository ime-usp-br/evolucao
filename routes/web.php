<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NotaController;


Route::get('/', function () {
    return view('home');
});


// Importacao
Route::get('/notas/importar', [Notacontroller::class, 'importar']);
Route::post('/notas/importar/csv', [NotaController::class, 'importar_csv']);

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
