<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\HomeController;
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

Route::get('/', HomeController::class)->name('home');

Route::resource('customers', CustomerController::class)->only('create', 'store');
Route::post('customers/export-all', [CustomerController::class, 'exportAll'])->name('customers.export-all');
Route::delete('customers/delete-all', [CustomerController::class, 'deleteAll'])->name('customers.delete-all');
