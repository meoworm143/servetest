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

use Illuminate\Support\Facades\Route;


Route::group(['prefix' => 'admin', 'as'=>'admin.', 'namespace' => 'Web\Admin','middleware'=>['admin']], function () {

    Route::group(['prefix' => 'transaction', 'as'=>'transaction.'], function () {
        Route::any('list', 'TransactionController@index')->name('list');
        Route::any('download', 'TransactionController@download')->name('download');
    });

    Route::group(['prefix' => 'withdraw', 'as'=>'withdraw.'], function () {
        Route::any('list', 'WithdrawnController@index')->name('list');
        Route::post('update-status/{id}', 'WithdrawnController@update_status')->name('update_status');
    });

});
