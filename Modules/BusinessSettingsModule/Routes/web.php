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
use Modules\BusinessSettingsModule\Http\Controllers\Web\Admin\ConfigurationController;

Route::group(['namespace' => 'Api\V1\Admin'], function () {
    Route::get('file-manager', 'FileManagerController@index');
});


Route::group(['prefix' => 'admin', 'as' => 'admin.', 'namespace' => 'Web\Admin', 'middleware' => ['admin','mpc:system_management']], function () {
    Route::group(['prefix' => 'business-settings', 'as' => 'business-settings.'], function () {
        Route::get('get-business-information', 'BusinessInformationController@business_information_get')->name('get-business-information');
        Route::put('set-business-information', 'BusinessInformationController@business_information_set')->name('set-business-information');

        Route::put('set-service-setup', 'BusinessInformationController@service_setup_set')->name('set-service-setup');

        Route::get('get-pages-setup', 'BusinessInformationController@pages_setup_get')->name('get-pages-setup');
        Route::post('set-pages-setup', 'BusinessInformationController@pages_setup_set')->name('set-pages-setup');

        Route::get('get-landing-information', 'LandingPageController@landing_information_get')->name('get-landing-information');
        Route::put('set-landing-information', 'LandingPageController@landing_information_set')->name('set-landing-information');
        Route::delete('delete-landing-information/{page}/{id}', 'LandingPageController@landing_information_delete')->name('delete-landing-information');
    });

    Route::group(['prefix' => 'configuration', 'as' => 'configuration.'], function () {
        Route::get('get-notification-setting', 'ConfigurationController@notification_settings_get')->name('get-notification-setting');
        Route::put('set-notification-setting', 'ConfigurationController@notification_settings_set')->name('set-notification-setting');
        Route::any('set-message-setting', 'ConfigurationController@message_settings_set')->name('set-message-setting');

        Route::get('get-email-config', 'ConfigurationController@email_config_get')->name('get-email-config');
        Route::put('set-email-config', 'ConfigurationController@email_config_set')->name('set-email-config');

        Route::get('get-third-party-config', 'ConfigurationController@third_party_config_get')->name('get-third-party-config');
        Route::put('set-third-party-config', 'ConfigurationController@third_party_config_set')->name('set-third-party-config');

        Route::get('get-app-settings', 'ConfigurationController@app_settings_config_get')->name('get-app-settings');
        Route::put('set-app-settings', 'ConfigurationController@app_settings_config_set')->name('set-app-settings');

        Route::put('social-login-config-set', [ConfigurationController::class, 'social_login_config_set'])->name('social-login-config-set');
    });
});
