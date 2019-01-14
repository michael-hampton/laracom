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

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/**
 * Admin routes
 */
Route::namespace('Admin')->group(function () {
    Route::get('admin/login', 'LoginController@showLoginForm')->name('admin.login');
    Route::post('admin/login', 'LoginController@login')->name('admin.login');
    Route::get('admin/logout', 'LoginController@logout')->name('admin.logout');
});
Route::group(['prefix' => 'admin', 'middleware' => 'admin', 'as' => 'admin.'], function () {
    Route::namespace('Admin')->group(function () {
        Route::get('/', 'DashboardController@index')->name('dashboard');
        Route::namespace('Customers')->group(function () {
            Route::resource('customers', 'CustomerController');
            Route::resource('customers.addresses', 'CustomerAddressController');
        });
        Route::namespace('Products')->group(function () {
            Route::resource('products', 'ProductController');
            Route::get('remove-image-product', 'ProductController@removeImage')->name('product.remove.image');
            Route::get('remove-image-thumb', 'ProductController@removeThumbnail')->name('product.remove.thumb');
           Route::post('getProductAutoComplete', 'ProductController@getProductAutoComplete')->name('product.getProductAutoComplete');
           Route::post('search/{page?}', 'ProductController@search')->name('products.search');
        });
        Route::namespace('Categories')->group(function () {
            Route::resource('categories', 'CategoryController');
            Route::get('remove-image-category', 'CategoryController@removeImage')->name('category.remove.image');
        });
        Route::namespace('Orders')->group(function () {
            Route::resource('orders', 'OrderController');
            Route::post('orderLine/updateLineStatus', 'OrderLineController@updateLineStatus')->name('orders.updateLineStatus');
            Route::post('orderLine/update', 'OrderLineController@update')->name('orderLine.update');
            Route::resource('order-statuses', 'OrderStatusController');
            Route::get('orders/{id}/invoice', 'OrderController@generateInvoice')->name('orders.invoice.generate');
            Route::get('orders/importCsv/get', 'OrderController@importCsv')->name('orders.importCsv');
            Route::post('orders/saveImport', 'OrderController@saveImport')->name('orders.saveImport');
        });

        Route::post('orderLine/processBackorders/', 'Orders\OrderLineController@processBackorders')->name('orderLine.processBackorders');
        Route::post('orderLine/doAllocation/', 'Orders\OrderLineController@doAllocation')->name('orderLine.doAllocation');
        Route::post('warehouse/pickOrder/', 'Orders\WarehouseController@pickOrder')->name('warehouse.pickOrder');
        Route::post('warehouse/packOrder/', 'Orders\WarehouseController@packOrder')->name('warehouse.packOrder');
        Route::get('warehouse/index/', 'Orders\WarehouseController@index')->name('warehouse.index');
        Route::post('warehouse/dispatchOrder/', 'Orders\WarehouseController@dispatchOrder')->name('warehouse.dispatchOrder');
        Route::post('orderLine/search/{page?}', 'Orders\OrderLineController@search')->name('orderLine.search');
        Route::post('orders/search/{page?}', 'Orders\OrderController@search')->name('orders.search');
        Route::post('orders/saveComment/', 'Orders\OrderController@saveComment')->name('orders.saveComment');
        Route::post('refunds/doRefund/', 'Refunds\RefundController@doRefund')->name('refunds.doRefund');
        Route::post('channels/saveChannelAttribute/', 'Channels\ChannelController@saveChannelAttribute')->name('channels.saveChannelAttribute');
        Route::post('orders/cloneOrder/', 'Orders\OrderController@cloneOrder')->name('orders.cloneOrder');
        Route::post('orders/destroy/{id}', 'Orders\OrderController@destroy')->name('orders.destroy');
        Route::post('products/getProductAutoComplete/get/', 'Products\ProductController@getProductAutoComplete')->name('products.getProductAutoComplete');

        Route::resource('employees', 'EmployeeController');
        Route::get('employees/{id}/profile', 'EmployeeController@getProfile')->name('employee.profile');
        Route::get('employees/{employeeId}/profile/detachchannel/{storeId}', 'EmployeeController@detachChannelAssigned')->name('employee.profile.detachchannel');
        Route::put('employees/{id}/profile', 'EmployeeController@updateProfile')->name('employee.profile.update');
        Route::resource('addresses', 'Addresses\AddressController');
        Route::resource('vouchers', 'Vouchers\VoucherController');
        Route::resource('refunds', 'Refunds\RefundController');
        Route::resource('voucher-codes', 'VoucherCodes\VoucherCodeController');
        Route::resource('channels', 'Channels\ChannelController');
        Route::get('voucher-codes/batch/{id?}', 'VoucherCodes\VoucherCodeController@getCodesByBatch')->name('voucher-codes.batch');
        Route::get('voucher-codes/validate/{code}', 'VoucherCodes\VoucherCodeController@validateVoucherCode')->name('voucher-codes.validateVoucherCode');
        Route::get('vouchers/get/{channel}', 'Vouchers\VoucherController@getVouchersByChannel')->name('vouchers.getByChannel');
        Route::get('vouchers/create/{channel?}', 'Vouchers\VoucherController@create')->name('vouchers.create');
        Route::get('orders/create/{channel?}', 'Orders\OrderController@create')->name('orders.create');
        Route::get('orders/backorders/get', 'Orders\OrderController@backorders')->name('orders.backorders');
        Route::get('orders/allocations/get', 'Orders\OrderController@allocations')->name('orders.allocations');
        Route::get('voucher-codes/add/{id}', 'VoucherCodes\VoucherCodeController@create')->name('voucher-codes.add');
        Route::resource('channels', 'Channels\ChannelController');
        Route::get('admin.channels.remove.image', 'ChannelController@removeImage')->name('channel.remove.image');
        Route::resource('countries', 'Countries\CountryController');
        Route::resource('countries.provinces', 'Provinces\ProvinceController');
        Route::resource('countries.provinces.cities', 'Cities\CityController');
        Route::resource('couriers', 'Couriers\CourierController');
        Route::resource('courier-rates', 'Couriers\CourierRateController');
        Route::post('courier-rates/search/{page?}', 'Couriers\CourierRateController@search')->name('courier-rates.search');
        Route::resource('payment-methods', 'PaymentMethods\PaymentMethodController');
        Route::resource('attributes', 'Attributes\AttributeController');
        Route::resource('attributes.values', 'Attributes\AttributeValueController');
        Route::resource('roles', 'Roles\RoleController');
        Route::resource('permissions', 'Permissions\PermissionController');
        Route::resource('brands', 'Brands\BrandController');
	Route::get('remove-image-brand', 'Brands\BrandController@removeImage')->name('brand.remove.image');
        Route::resource('channel-prices', 'ChannelPrices\ChannelPriceController');
        Route::get('channel-prices/get/{channel}', 'ChannelPrices\ChannelPriceController@index')->name('channel-prices.index');
        Route::get('channel-prices/editForm/{product}/{channel}', 'ChannelPrices\ChannelPriceController@editForm')->name('channel-prices.editForm');
        Route::post('channel-prices/search/{page?}', 'ChannelPrices\ChannelPriceController@search')->name('channel-prices.search');
    });
});

/**
 * Frontend routes
 */
Auth::routes();
Route::namespace('Auth')->group(function () {
    Route::get('cart/login', 'CartLoginController@showLoginForm')->name('cart.login');
    Route::post('cart/login', 'CartLoginController@login')->name('cart.login');
    Route::get('logout', 'LoginController@logout');
});

Route::namespace('Front')->group(function () {
    Route::get('/', 'HomeController@index')->name('home');

    Route::namespace('Addresses')->group(function () {
        Route::resource('country.state', 'CountryStateController');
        Route::resource('state.city', 'StateCityController');
    });

    Route::namespace('Payments')->group(function () {
        Route::get('bank-transfer', 'BankTransferController@index')->name('bank-transfer.index');
        Route::post('bank-transfer', 'BankTransferController@store')->name('bank-transfer.store');
    });

    Route::group(['middleware' => ['auth']], function () {
        Route::get('accounts', 'AccountsController@index')->name('accounts');
        Route::get('checkout', 'CheckoutController@index')->name('checkout.index');
        Route::post('checkout', 'CheckoutController@store')->name('checkout.store');
        Route::post('set-courier', 'CheckoutController@setCourier')->name('set.courier');
        Route::post('set-address', 'CheckoutController@setAddress')->name('set.address');
        Route::get('checkout/execute', 'CheckoutController@execute')->name('checkout.execute');
        Route::get('checkout/cancel', 'CheckoutController@cancel')->name('checkout.cancel');
        Route::get('checkout/success', 'CheckoutController@success')->name('checkout.success');
        Route::resource('customer', 'CustomerController');
        Route::resource('customer.address', 'CustomerAddressController');
    });
    Route::resource('cart', 'CartController');
    Route::get("category/{slug}", 'CategoryController@getCategory')->name('front.category.slug');
    Route::get("search", 'ProductController@search')->name('search.product');
    Route::get("{product}", 'ProductController@show')->name('front.get.product');
});
