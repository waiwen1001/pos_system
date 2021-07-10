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
Route::get('/setup', 'HomeController@getSetupPage')->name('setup');
Route::get('/key_setup', 'HomeController@getKeySetupPage')->name('key_setup');
Route::get('/profile', 'HomeController@getBranchProfile')->name('getBranchProfile');
Route::post('/updateProfile', 'HomeController@updateProfile')->name('updateProfile');

Route::post('/searchAndAddItem', 'HomeController@searchAndAddItem')->name('searchAndAddItem');
Route::post('/submitDeleteItem', 'HomeController@submitDeleteItem')->name('submitDeleteItem');
Route::post('/submitTransaction', 'HomeController@submitTransaction')->name('submitTransaction');
Route::post('/submitVoidTransaction', 'HomeController@submitVoidTransaction')->name('submitVoidTransaction');
Route::post('/submitUnvoidTransaction', 'HomeController@submitUnvoidTransaction')->name('submitUnvoidTransaction');
Route::post('/clearTransaction', 'HomeController@clearTransaction')->name('clearTransaction');
Route::post('/editReferenceNo', 'HomeController@editReferenceNo')->name('editReferenceNo');
Route::post('/editQuantity', 'HomeController@editQuantity')->name('editQuantity');
Route::post('/submitVoucher', 'HomeController@submitVoucher')->name('submitVoucher');
Route::post('/removeVoucher', 'HomeController@removeVoucher')->name('removeVoucher');
Route::post('/submitOpening', 'HomeController@submitOpening')->name('submitOpening');
Route::post('/submitClosing', 'HomeController@submitClosing')->name('submitClosing');
Route::post('/submitDailyClosing', 'HomeController@submitDailyClosing')->name('submitDailyClosing');
Route::post('/submitCashFloat', 'HomeController@submitCashFloat')->name('submitCashFloat');
Route::get('/getClosingAmount', 'HomeController@calculateClosingAmount')->name('calculateClosingAmount');
Route::post('/transaction_detail', 'HomeController@getTransactionDetail')->name('getTransactionDetail');
Route::get('/getDailyReport', 'HomeController@getDailyReport')->name('getDailyReport');
Route::post('/searchRelatedItem', 'HomeController@searchRelatedItem')->name('searchRelatedItem');
Route::get('/serverCashFloatReport', 'HomeController@serverCashFloatReport')->name('serverCashFloatReport');

Route::get('/branchSync', 'HomeController@branchSync')->name('branchSync');
Route::get('/productSync', 'HomeController@productSync')->name('productSync');

Route::post('/deleteUser', 'HomeController@deleteUser')->name('deleteUser');
Route::post('/addNewUser', 'HomeController@addNewUser')->name('addNewUser');
Route::post('/editUser', 'HomeController@editUser')->name('editUser');

Route::post('/saveCashier', 'HomeController@createCashier')->name('createCashier');
Route::post('/deleteCashier', 'HomeController@deleteCashier')->name('deleteCashier');
Route::post('/editCashier', 'HomeController@editCashier')->name('editCashier');

Route::post('/saveShortcutKey', 'HomeController@saveShortcutKey')->name('saveShortcutKey');

Route::get('/testing', 'HomeController@testing')->name('testing');
Route::get('/myIP', 'HomeController@myIP')->name('myIP');
Route::get('/getlogout', 'HomeController@getlogout')->name('getlogout');


