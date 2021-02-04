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

Auth::routes();

Route::get('/', 'HomeController@index')->name('home');
Route::post('/searchAndAddItem', 'HomeController@searchAndAddItem')->name('searchAndAddItem');
Route::post('/submitDeleteItem', 'HomeController@submitDeleteItem')->name('submitDeleteItem');
Route::post('/submitTransaction', 'HomeController@submitTransaction')->name('submitTransaction');
Route::post('/submitVoidTransaction', 'HomeController@submitVoidTransaction')->name('submitVoidTransaction');
Route::post('/submitUnvoidTransaction', 'HomeController@submitUnvoidTransaction')->name('submitUnvoidTransaction');
Route::post('/clearTransaction', 'HomeController@clearTransaction')->name('clearTransaction');
Route::post('/editInvoiceNo', 'HomeController@editInvoiceNo')->name('editInvoiceNo');
Route::post('/editQuantity', 'HomeController@editQuantity')->name('editQuantity');
Route::post('/submitVoucher', 'HomeController@submitVoucher')->name('submitVoucher');
Route::post('/removeVoucher', 'HomeController@removeVoucher')->name('removeVoucher');
Route::post('/submitOpening', 'HomeController@submitOpening')->name('submitOpening');
Route::post('/submitClosing', 'HomeController@submitClosing')->name('submitClosing');
Route::post('/submitDailyClosing', 'HomeController@submitDailyClosing')->name('submitDailyClosing');

Route::get('/myIP', 'HomeController@myIP')->name('myIP');

