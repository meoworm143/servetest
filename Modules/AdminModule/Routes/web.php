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

//employee management
Route::group(['prefix' => 'admin', 'as' => 'admin.', 'namespace' => 'Web\Admin', 'middleware' => ['admin','mpc:employee_management']], function () {
    Route::get('dashboard', 'AdminController@dashboard')->name('dashboard')->withoutMiddleware(['mpc:employee_management']);
    Route::get('update-dashboard-earning-graph', 'AdminController@update_dashboard_earning_graph')->name('update-dashboard-earning-graph');

    //profile
    Route::get('profile-update', 'AdminController@profile_info')->name('profile_update');
    Route::post('profile-update', 'AdminController@update_profile');
    Route::get('get-updated-data', 'AdminController@get_updated_data')->name('get_updated_data');

    Route::group(['prefix' => 'role', 'as' => 'role.'], function () {
        Route::any('create', 'RoleController@create')->name('create');
        Route::post('store', 'RoleController@store')->name('store');
        Route::get('edit/{id}', 'RoleController@edit')->name('edit');
        Route::put('update/{id}', 'RoleController@update')->name('update');
        Route::any('status-update/{id}', 'RoleController@status_update')->name('status-update');
        Route::delete('delete/{id}', 'RoleController@destroy')->name('delete');
    });

    Route::group(['prefix' => 'employee', 'as' => 'employee.'], function () {
        Route::any('list', 'EmployeeController@index')->name('index');
        Route::any('create', 'EmployeeController@create')->name('create');
        Route::post('store', 'EmployeeController@store')->name('store');
        Route::get('edit/{id}', 'EmployeeController@edit')->name('edit');
        Route::put('update/{id}', 'EmployeeController@update')->name('update');
        Route::any('status-update/{id}', 'EmployeeController@status_update')->name('status-update');
        Route::delete('delete/{id}', 'EmployeeController@destroy')->name('delete');
        Route::any('download', 'EmployeeController@download')->name('download');
    });
});
