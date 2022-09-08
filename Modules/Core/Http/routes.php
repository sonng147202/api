<?php

/*Route::group(['middleware' => ['web', 'auth'], 'namespace' => 'Modules\Core\Http\Controllers'], function()
{
   Route::get('/', 'AdminController@index');
});*/

Route::group(['middleware' => ['web'], 'prefix' => 'admin', 'namespace' => 'Modules\Core\Http\Controllers'], function()
{
    // Route::get('/login', 'AdminController@login')->name('login');
    Route::get('/login', 'AdminController@login');
    Route::get('/forgot', 'AdminController@forgot')->name('forgot');
    Route::post('/forgot-password', 'AdminController@forgotPassword')->name('login.forgot_password');
    Route::post('/login', 'AdminController@loginPost')->name('login');

    Route::group(['middleware' => ['auth', 'verify.role']], function () {
        Route::get('/', 'DashboardController@index')->name('admin_home');

        Route::get('/logout', 'AdminController@logout')->name('admin_logout');

        Route::get('/settings', 'SettingController@index')->name('core.settings.index');
        Route::post('/settings', 'SettingController@updateSetting')->name('core.settings.update');

        Route::resource('/menu', 'MenuController', ['as' => 'core']);
        Route::resource('/menu_type', 'MenuTypeController', ['as' => 'core']);

        Route::get('/dashboard', 'DashboardController@index')->name('core.dashboard');
        Route::get('/filler_customer', 'DashboardController@filler_customer')->name('core.filler_customer');
        Route::any('/user/move', 'UserController@move')->name('insurance.customer.move');


        Route::resource('user', 'UserController', ['as' => 'core']);
        Route::post('/user/{id}/restore', 'UserController@restore')->name('core.user.restore');
        Route::post('/user/{id}/resset_password', 'UserController@resetPassword')->name('core.user.reset_password');

        Route::resource('role', 'RoleController', ['as' => 'core']);
        Route::post('/role/{id}/restore', 'RoleController@restore')->name('core.role.restore');

        Route::resource('group', 'GroupController', ['as' => 'core']);
        Route::post('/group/{id}/restore', 'GroupController@restore')->name('core.group.restore');
        Route::post('/dashboard/get-article-detail', 'DashboardController@getArticleDetail');
        Route::get('/dashboard/modal-article-detail', 'DashboardController@modalArticleDetail');


        Route::get('/notification','NotificationController@index')->name('core.notification.index');
        Route::get('/notification/create','NotificationController@create')->name('core.notification.create');
        Route::post('/notification/save','NotificationController@store')->name('core.notification.save');
        Route::post('/notification/edit','NotificationController@edit')->name('core.notification.edit');
        Route::any('/notification/{id}/destroy','NotificationController@notificationDestroy')->name('core.notification.destroy');

        Route::post('/notification/update','NotificationController@update')->name('core.notification.update');
        Route::any('/sendEmail','NotificationController@sendEmail')->name('core.sendEmail.index');

    });
});

/* API route */
Route::group(['prefix' => 'api', 'middleware' => ['api.tracking'], 'namespace' => 'Modules\Core\Http\Controllers\Api'], function () {
    Route::group(['prefix' => 'v1', 'namespace' => 'V1'], function () {
        Route::post('/upload-image', 'ImageController@upload')->name('api.v1.upload_image');
        Route::post('/upload-image-support', 'ImageController@uploadImageSupport')->name('api.v1.upload_image_support');
        Route::post('/upload-image-help-request', 'ImageController@upload2')->name('api.v1.upload_image2');
        Route::post('/dashboard/index', 'DashboardController@index');
        Route::post('/dashboard/get-article-detail', 'DashboardController@getArticleDetail');
        Route::post('/dashboard/get-list-article', 'DashboardController@getListArticle');
        Route::post('/dashboard/get-list-article-pag', 'DashboardController@getListArticlePag');
        Route::post('/dashboard/get-customer-agency-quotation', 'DashboardController@getCustomerAgencyQuotation');
        Route::post('/dashboard/read-notification', 'DashboardController@readNotification');
        Route::post('/dashboard/get-notification', 'DashboardController@getNotification');
    });
});

