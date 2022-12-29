<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['prefix' => 'admin', 'as' => 'admin.', 'namespace' => 'Web\Admin', 'middleware' => ['admin']], function () {
    Route::group(['prefix' => 'provider', 'as' => 'provider.'], function () {
        Route::any('list', 'ProviderController@index')->name('list');
        Route::any('status-update/{id}', 'ProviderController@status_update')->name('status_update');
        Route::post('commission-update/{id}', 'ProviderController@commission_update')->name('commission_update');

        Route::get('create', 'ProviderController@create')->name('create');
        Route::post('store', 'ProviderController@store')->name('store');
        Route::get('edit/{id}', 'ProviderController@edit')->name('edit');
        Route::put('update/{id}', 'ProviderController@update')->name('update');
        Route::delete('delete/{id}', 'ProviderController@destroy')->name('delete');
        Route::any('details/{id}', 'ProviderController@details')->name('details');
        Route::any('download', 'ProviderController@download')->name('download');

        Route::group(['prefix' => 'account', 'as' => 'account.'], function () {
            Route::post('update/{id}', 'ProviderController@update_account_info')->name('update');
            Route::get('delete/{id}', 'ProviderController@delete_account_info')->name('delete');
        });

        Route::group(['prefix' => 'sub-category', 'as' => 'sub_category.'], function () {
            Route::get('update-subscription/{id}', 'ProviderController@update_subscription')->name('update_subscription');
        });

        Route::any('onboarding-request', 'ProviderController@onboarding_request')->name('onboarding_request');
        Route::get('update-approval/{id}/{status}', 'ProviderController@update_approval')->name('update-approval');

        Route::group(['prefix' => 'collect-cash', 'as' => 'collect_cash.'], function () {
            Route::get('/{id}', 'CollectCashController@index')->name('list');
            Route::post('/', 'CollectCashController@collect_cash')->name('store');
        });
    });
});


Route::group(['prefix' => 'provider', 'as' => 'provider.', 'namespace' => 'Web\Provider', 'middleware' => ['provider']], function () {
    Route::get('get-updated-data', 'ProviderController@get_updated_data')->name('get_updated_data');
    Route::get('dashboard', 'ProviderController@dashboard')->name('dashboard');
    Route::get('update-dashboard-earning-graph', 'ProviderController@update_dashboard_earning_graph')->name('update-dashboard-earning-graph');

    Route::get('bank-info', 'ProviderController@bank_info')->name('bank_info');
    Route::put('update-bank-info', 'ProviderController@update_bank_info')->name('update_bank_info');

    Route::any('account-info', 'ProviderController@account_info')->name('account_info');
    Route::any('reviews/download', 'ProviderController@reviews_download')->name('reviews.download');

    //profile
    Route::get('profile-update', 'ProviderController@profile_info')->name('profile_update');
    Route::post('profile-update', 'ProviderController@update_profile');

    Route::group(['prefix' => 'chat', 'as' => 'chat.'], function () {
        Route::get('conversation', 'ProviderController@conversation')->name('conversation');
    });

    Route::group(['prefix' => 'sub-category', 'as' => 'sub_category.'], function () {
        Route::get('subscribed', 'ProviderController@subscribed_sub_categories')->name('subscribed');
        Route::get('status-update/{id}', 'ProviderController@status_update')->name('status-update');
        Route::get('available/services', 'ProviderController@available_services')->name('available-services');
        Route::get('download', 'ProviderController@download')->name('download');
    });

    Route::group(['prefix' => 'withdraw', 'as' => 'withdraw.'], function () {
        Route::get('/', 'WithdrawController@index')->name('list');
        Route::post('/', 'WithdrawController@withdraw')->name('store');
        Route::any('download', 'WithdrawController@download')->name('download');
    });
});
