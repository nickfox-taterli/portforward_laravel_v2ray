<?php

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

Route::get('/', function () {
    return view('welcome');
})->name('index');

Route::get('/create', function () {
    return view('create');
});

Route::get('show', 'App\Http\Controllers\PortForwardController@show')->name('show');
Route::get('cron', 'App\Http\Controllers\PortForwardController@cron')->name('cron');
Route::post('create', 'App\Http\Controllers\PortForwardController@create')->name('create');