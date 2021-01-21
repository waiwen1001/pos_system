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

