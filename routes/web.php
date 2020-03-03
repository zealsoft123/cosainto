
<?php

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

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
Route::get('/profile', 'ProfileController@show');
Route::post('/profile', 'ProfileController@update');
Route::get('/dashboard', 'DashboardController@index')->name('dashboard');
Route::post('/dashboard', 'DashboardController@uploadTransactions');
Route::get('/transaction/export', 'TransactionController@export')->name('export');
Route::get('/transaction/last_updated', 'TransactionController@last_updated')->name('hash');
Route::get('/transaction/{id}', 'TransactionController@show');
Route::get('/transaction/{id}/payment', 'PaymentsController@show');
Route::post('/transaction/{id}/update', 'TransactionController@update');
Route::post('/transaction/{id}/upload', 'TransactionController@upload');

Route::get('/transaction/{id}/file/delete/{path_hash}', 'TransactionController@delete_file');

Route::post('/charge', 'PaymentsController@charge');
