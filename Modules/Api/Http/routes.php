<?php

Route::get('/get-user-detail', 'V1\AgencyController@getUserDetail');
Route::post('/login', 'V1\AccessTokenController@login');
Route::prefix('dashboard')->group(function () {
	Route::get('/', 'V1\DashboardController@getDashboard');
	Route::get('/home', 'V1\DashboardController@getHome');
});
Route::get('/get-insurance-detail', 'V1\InsuranceProductController@getInsuranceDetail');
Route::get('/get-insurance-life-company', 'V1\InsuranceCompanyController@getInsuranceLifeCompany');
Route::get('/get-insurance-non-life-company', 'V1\InsuranceCompanyController@getInsuranceNonLifeCompany');
Route::get('/get-list-bank', 'V1\BankController@getListBank');
Route::get('/get-list-province', 'V1\CustomerController@getListProvince');

Route::prefix('agency')->group(function () {
	Route::get('/get-list-agency-branch', 'V1\AgencyController@getListAgencyBranch');
	Route::get('/get-list-level', 'V1\AgencyController@getListLevel');
	Route::post('/add-register-online', 'V1\AgencyController@addRegisterOnline')->middleware('api_token');
	Route::get('/get-payrolls', 'V1\AgencyController@getPayrolls');
	Route::get('/get-info-bank', 'V1\AgencyController@getInfoBank');
	Route::post('/update-info-bank', 'V1\AgencyController@updateInfoBank')->middleware('api_token');
	Route::get('/get-profile', 'V1\AgencyController@getProfile');
	Route::post('/update-profile', 'V1\AgencyController@updateProfile')->middleware('api_token');
	Route::post('/system-tree', 'V1\AgencyController@systemTree');
	Route::get('/get-agency-child-list', 'V1\AgencyController@getAgencyChildList');
	Route::get('/view-system-tree', 'V1\AgencyController@viewSystemTree');
	Route::post('/post-agency-child-list', 'V1\AgencyController@postAgencyChildList');
	Route::get('/get-agency-detail-by-code', 'V1\AgencyController@getAgencyDetailByCode');
	// Route::post('/test', 'V1\AgencyController@test');
	Route::get('/get-insurance-agency-detail-by-code', 'V1\AgencyController@getInsuranceAgencyDetailByCode');
});

Route::prefix('revenue')->group(function () {
	Route::get('/get-revenue-life', 'V1\RevenueController@getRevenueLife');
	Route::get('/get-revenue-non-life', 'V1\RevenueController@getRevenueNonLife');
});

Route::prefix('product')->group(function () {
	Route::get('/get-list-type-insurance', 'V1\ProductController@getListTypeInsurance');
	Route::get('/get-list-insurance-by-type-insurance', 'V1\ProductController@getListInsuranceByTypeInsurance');
	Route::get('/get-list-vbi-insurance-package-by-product', 'V1\ProductController@getListVBIInsurancePackageByProduct');
	Route::get('/get-list-vbi-addition-condition-by-insurance-package', 'V1\ProductController@getListVBIAdditionConditionByInsurancePackage');
	Route::get('/get-list-insurance-by-company', 'V1\ProductController@getListInsuranceByCompany');
	Route::get('/get-list-insurance-life', 'V1\ProductController@getListInsuranceLife');
	Route::get('/get-list-insurance-non-life', 'V1\ProductController@getListInsuranceNonLife');
});

Route::prefix('contract')->group(function () {
	Route::post('/add-life-contract-report', 'V1\ContractController@addLifeContractReport')->middleware('api_token');
	Route::post('/add-non-life-contract-report', 'V1\ContractController@addNonLifeContractReport')->middleware('api_token');
	Route::get('/get-contract-detail', 'V1\ContractController@getContractDetail');
	Route::get('/get-list-contract-life-agency', 'V1\ContractController@getListContractLifeAgency');
	Route::get('/get-list-contract-non-life-agency', 'V1\ContractController@getListContractNonLifeAgency');
	Route::post('/store-contract-non-life', 'V1\ContractController@storeContractNonLife')->middleware('api_token');
	Route::post('/active-certification', 'V1\ContractController@activeCertification')->middleware('api_token');
	Route::get('/get-list-contract-life-branch', 'V1\ContractController@getListContractLifeBranch');
	Route::get('/get-list-contract-non-life-branch', 'V1\ContractController@getListContractNonLifeBranch');
	Route::get('/get-list-gcn-contract', 'V1\ContractController@getListGCNContract');
	// Route::get('/test', 'V1\ContractController@test');
	Route::get('/payment-vnpay-return-app', 'V1\ContractController@paymentVnpayReturn')->name('paymentVnpayReturn');
	Route::post('/contract-payment', 'V1\ContractController@contractPayment');
});

Route::prefix('notification')->group(function () {
	Route::get('/get-list-news', 'V1\NotificationController@getListNews');
	Route::get('/get-news-detail', 'V1\NotificationController@getNewsDetail');
	Route::get('/get-list-notify', 'V1\NotificationController@getListNotify');
	Route::get('/get-list-promotion', 'V1\NotificationController@getListPromotion');
});

Route::prefix('customer')->group(function () {
	Route::get('/get-info-customer-by-id-card-number', 'V1\CustomerController@getInfoCustomerByIdCardNumber');
	Route::get('/get-list-customer', 'V1\CustomerController@getListCustomer');
	Route::get('/get-list-contract-by-customer', 'V1\CustomerController@getListContractByCustomer');
	Route::post('/store-customer', 'V1\CustomerController@storeCustomer');
	Route::post('/update-customer', 'V1\CustomerController@updateCustomer');
});

Route::prefix('document')->group(function () {
	Route::get('/get-list-document-categories', 'V1\DocumentController@getListDocumentCategories');
	Route::get('/get-list-document-categories-children', 'V1\DocumentController@getListDocumentCategoriesChildren');
	Route::get('/get-list-documents-by-category', 'V1\DocumentController@getListDocumentsByCategory');
});

Route::prefix('image')->group(function () {
	Route::get('/get-list-images', 'V1\ImageController@getListImages');
	Route::get('/get-list-images-by-category', 'V1\ImageController@getListImagesByCategory');
});

Route::prefix('forgot-password')->group(function () {
	Route::post('/send-otp', 'V1\ForgotPasswordController@sendOTP');
	Route::post('/confirm-otp', 'V1\ForgotPasswordController@confirmOTP');
	Route::post('/reset-password', 'V1\ForgotPasswordController@resetPassword');
});

Route::prefix('video')->group(function () {
	Route::get('/get-video-detail-by-id', 'V1\VideoController@getVideoDetailById');
});

Route::post('/change-password', 'V1\ChangePasswordController@changePassword');