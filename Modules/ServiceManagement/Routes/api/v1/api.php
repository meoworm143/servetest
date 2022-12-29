<?php

use Illuminate\Http\Request;
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

Route::group(['prefix' => 'admin', 'as' => 'admin.', 'namespace' => 'Api\V1\Admin', 'middleware' => ['auth:api']], function () {
    Route::resource('service', 'ServiceController', ['only' => ['index', 'store', 'edit', 'update', 'show']]);
    Route::put('service/status/update', 'ServiceController@status_update');
    Route::delete('service/delete', 'ServiceController@destroy');

    Route::resource('faq', 'FAQController', ['only' => ['index', 'store', 'edit', 'update', 'show']]);
    Route::put('faq/status/update', 'FAQController@status_update');
    Route::delete('faq/delete', 'FAQController@destroy');
});

Route::group(['prefix' => 'provider', 'as' => 'provider.', 'namespace' => 'Api\V1\Provider', 'middleware' => ['auth:api']], function () {
    Route::resource('service', 'ServiceController', ['only' => ['index', 'show']]);
    Route::put('service/status/update', 'ServiceController@status_update');
    Route::get('service/data/search', 'ServiceController@search');
    Route::get('service/review/{service_id}', 'ServiceController@review');

    Route::resource('faq', 'FAQController', ['only' => ['index', 'show']]);
});

Route::group(['prefix' => 'customer', 'as' => 'customer.', 'namespace' => 'Api\V1\Customer'], function () {
    Route::group(['prefix' => 'service'], function () {
        Route::get('/', 'ServiceController@index');
        Route::get('search', 'ServiceController@search');
        Route::get('popular', 'ServiceController@popular');
        Route::get('recommended', 'ServiceController@recommended');
        Route::get('offers', 'ServiceController@offers');
        Route::get('detail/{id}', 'ServiceController@show');
        Route::get('review/{service_id}', 'ServiceController@review');
        Route::get('sub-category/{sub_category_id}', 'ServiceController@categories_by_subcategory');
    });
});
