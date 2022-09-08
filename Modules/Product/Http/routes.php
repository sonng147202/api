<?php

Route::group(['middleware' => ['web', 'auth', 'verify.role'], 'prefix' => 'product', 'namespace' => 'Modules\Product\Http\Controllers'], function()
{
    Route::get('/', 'ProductController@index')->name('product.index');
    Route::resource('/sp', 'ProductController', ['except' => [''], 'as' => 'product']);
    Route::resource('/categories', 'ProductCategoryController', ['except' => ['show'], 'as' => 'product']);
    Route::resource('{id}/category_attributes', 'ProductCategoryAttributeController', ['except' => ['show'], 'as' => 'product']);

    Route::get('/{id}/attribute', 'ProductController@attribute')->name('product.attribute');
    Route::put('/{id}/attribute', 'ProductController@updateAttribute')->name('product.attribute.update');

    Route::resource('/{productId}/prices', 'ProductPriceController', ['except' => ['show'], 'as' => 'product']);

    Route::resource('/commissions', 'CommissionController', ['except' => ['show'], 'as' => 'product']);
    Route::resource('/agency_commissions', 'ProductAgencyCommissionController', ['except' => ['show'], 'as' => 'product']);

    Route::resource('/coupons', 'CouponController', ['except' => ['show'], 'as' => 'product']);

    Route::get('/category_class', 'CategoryClassController@listCategory')->name('product.category_class.list_category');
    Route::resource('/{id}/category_class', 'CategoryClassController', ['as' => 'product']);

    Route::get('/load-price-attribute-inputs', 'ProductPriceController@getPriceAttributeInputs')->name('load_price_attribute_input');

    Route::get('/{id}/update-price-type', 'ProductController@updatePriceType')->name('product.update_price_type');
    Route::post('/{id}/update-price-type', 'ProductController@updatePriceTypePost')->name('product.update_price_type');

    Route::get('/ajax-list-product', 'ProductController@ajaxGetListProduct')->name('product.ajax_get_list_product');
    Route::post('/get-product-price', 'ProductController@getProductPrice')->name('product.get_product_price');
    Route::post('/check-unit-custom-price-type-health-insurance','ProductController@checkUnitPriceTypeHealthInsurance')->name('product.check_unit');
    Route::post('/get-extra-fee-price', 'ProductController@getExtraFeePrice')->name('product.get_extra_fee_price');
    Route::post('/get-extra-product', 'ProductController@getExtraProduct')->name('product.get_extra_product');
    Route::post('/get-extra-product-for-product', 'ProductController@getExtraProductForProduct')->name('product.get_extra_product_for_product');
    Route::post('/get-extra-fee', 'ProductController@getExtraFee')->name('product.get_extra_fee');
    Route::post('/bhtn-get-price', 'ProductController@bhtnGetPrice')->name('product.bhtn_get_price');


    // thêm hoa hồng cho sản phẩm
    Route::get('create-product-level/{id}', 'ProductController@CreateProductLevel')->name('create.product.level');
    Route::post('store-product-level/{id}', 'ProductController@storeProductLevel')->name('store.product.level');
});

Route::get('/get_classes', 'Modules\Product\Http\Controllers\ProductController@getClasses')->name('product.get_classes');

/* API route */
Route::group(['prefix' => 'api', 'middleware' => ['api.tracking'], 'namespace' => 'Modules\Product\Http\Controllers\Api'], function () {
    Route::group(['prefix' => 'v1', 'namespace' => 'V1'], function () {
        Route::post('/products', 'ProductController@getList')->name('api.v1.products');
        Route::post('/product-categories', 'ProductController@getListCategories')->name('api.v1.products.categories');
        Route::get('/product/detail/{id}', 'ProductController@getDetail')->name('api.v1.products.detail');
        Route::post('/product/get_popup_by_product', 'ProductController@getPopupByProduct')->name('api.v1.products.get_popup_by_product');
        Route::post('/product/price', 'ProductController@getProductPrice')->name('api.v1.product.price');
        
        Route::post('/get-product-price', 'ProductController@getProductPriceData')->name('product.api.get_product_price');
        Route::post('/get-extra-fee-price', 'ProductController@getExtraFeePrice')->name('product.api.get_extra_fee_price');
        Route::post('/get-extra-product', 'ProductController@getExtraProduct')->name('product.api.get_extra_product');
        Route::post('/bhtn-get-price', 'ProductController@bhtnGetPrice')->name('product.api.bhtn_get_price');
        Route::post('/get-extra-product-for-product', 'ProductController@getExtraProductForProduct')->name('product.api.get_extra_product_for_product');
        Route::post('/get-extra-fee', 'ProductController@getExtraFee')->name('product.api.get_extra_fee');
        Route::post('/bhtn-get-price', 'ProductController@bhtnGetPrice')->name('product.api.bhtn_get_price');
        Route::get('/{id}/attribute', 'ProductController@attribute')->name('product.api.attribute');

        Route::post('/search-product', 'ProductController@searchProduct');
    });
});
