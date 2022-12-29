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

Route::get('invoice',function (){
    $booking=\Modules\BookingModule\Entities\Booking::first();
    return view('bookingmodule::mail-templates.booking-request-sent',compact('booking'));
});

Route::group(['prefix' => 'admin', 'as' => 'admin.', 'namespace' => 'Web\Admin', 'middleware' => ['admin','mpc:booking_management']], function () {

    Route::group(['prefix' => 'booking', 'as' => 'booking.'], function () {
        Route::any('list', 'BookingController@index')->name('list');
        Route::get('check', 'BookingController@check_booking')->name('check');
        Route::get('details/{id}', 'BookingController@details')->name('details');
        Route::get('status-update/{id}', 'BookingController@status_update')->name('status_update');
        Route::get('payment-update/{id}', 'BookingController@payment_update')->name('payment_update');
        Route::any('schedule-update/{id}', 'BookingController@schedule_upadte')->name('schedule_update');
        Route::get('serviceman-update/{id}', 'BookingController@serviceman_update')->name('serviceman_update');
        Route::any('download', 'BookingController@download')->name('download');
        Route::any('invoice/{id}', 'BookingController@invoice')->name('invoice');
    });
});

Route::group(['prefix' => 'provider', 'as' => 'provider.', 'namespace' => 'Web\Provider', 'middleware' => ['provider']], function () {

    Route::group(['prefix' => 'booking', 'as' => 'booking.'], function () {
        Route::any('list', 'BookingController@index')->name('list');
        Route::get('check', 'BookingController@check_booking')->name('check');
        Route::get('details/{id}', 'BookingController@details')->name('details');
        Route::get('status-update/{id}', 'BookingController@status_update')->name('status_update');
        Route::get('payment-update/{id}', 'BookingController@payment_update')->name('payment_update');
        Route::any('schedule-update/{id}', 'BookingController@schedule_upadte')->name('schedule_update');
        Route::get('serviceman-update/{id}', 'BookingController@serviceman_update')->name('serviceman_update');
        Route::any('download', 'BookingController@download')->name('download');
        Route::any('invoice/{id}', 'BookingController@invoice')->name('invoice');
    });
});
