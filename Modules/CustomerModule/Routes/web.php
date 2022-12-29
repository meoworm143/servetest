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
use Stevebauman\Location\Facades\Location;

Route::get('about-us', 'PagesController@about_us')->name('about-us');
Route::get('privacy-policy', 'PagesController@privacy_policy')->name('privacy-policy');
Route::get('terms-and-conditions', 'PagesController@terms_and_conditions')->name('terms-and-conditions');
Route::get('refund-policy', 'PagesController@refund_policy')->name('refund-policy');
Route::get('return-policy', 'PagesController@return_policy')->name('return-policy');
Route::get('cancellation-policy', 'PagesController@cancellation_policy')->name('cancellation-policy');


Route::group(['prefix' => 'admin', 'as' => 'admin.', 'namespace' => 'Web\Admin', 'middleware' => ['admin','mpc:customer_management']], function () {
    Route::group(['prefix' => 'customer', 'as' => 'customer.'], function () {
        Route::any('list', 'CustomerController@index')->name('index');
        Route::any('create', 'CustomerController@create')->name('create');
        Route::post('store', 'CustomerController@store')->name('store');
        Route::any('detail/{id}', 'CustomerController@show')->name('detail');
        Route::get('edit/{id}', 'CustomerController@edit')->name('edit');
        Route::put('update/{id}', 'CustomerController@update')->name('update');
        Route::any('status-update/{id}', 'CustomerController@status_update')->name('status-update');
        Route::delete('delete/{id}', 'CustomerController@destroy')->name('delete');
        Route::any('download', 'CustomerController@download')->name('download');
    });
});

//test
Route::get('test-one',function (){
    return dd(Location::get('66.102.0.0'));
});
