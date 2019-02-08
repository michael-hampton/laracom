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

        Route::post('products/export', 'Products\ProductController@export')->name('products.export');
        Route::post('products/updateProduct', 'Products\ProductController@updateProduct')->name('products.updateProduct');
        Route::post('products/saveImport', 'Products\ProductController@saveImport')->name('products.saveImport');
        Route::get('products/importCsv/get', 'Products\ProductController@importCsv')->name('products.importCsv');
        Route::post('products/getProductAutoComplete/get/', 'Products\ProductController@getProductAutoComplete')->name('products.getProductAutoComplete');

        Route::namespace('Categories')->group(function () {
            Route::resource('categories', 'CategoryController');
            Route::get('remove-image-category', 'CategoryController@removeImage')->name('category.remove.image');
        });
        Route::namespace('Orders')->group(function () {
            Route::post('export', 'OrderController@export')->name('orders.export');
            Route::resource('orders', 'OrderController');
            Route::post('orderLine/updateLineStatus', 'OrderLineController@updateLineStatus')->name('orders.updateLineStatus');
            Route::post('orderLine/update', 'OrderLineController@update')->name('orderLine.update');
            Route::resource('order-statuses', 'OrderStatusController');
            Route::get('orders/{id}/invoice', 'OrderController@generateInvoice')->name('orders.invoice.generate');
            Route::get('orders/importCsv/get', 'OrderController@importCsv')->name('orders.importCsv');
            Route::post('orders/saveImport', 'OrderController@saveImport')->name('orders.saveImport');
        });

        /** Messages * */
        Route::get('message/index/', 'Messages\MessageController@index')->name('messages.index');
        Route::get('message/get/{orderId}', 'Messages\MessageController@get')->name('messages.get');
        Route::get('message/create/', 'Messages\MessageController@create')->name('messages.create');
        Route::get('message/show/', 'Messages\MessageController@show')->name('messages.show');
        Route::post('message/store/', 'Messages\MessageController@store')->name('messages.store');

        /** Returns * */
        Route::get('returns/index/', 'Returns\ReturnController@index')->name('returns.index');
        Route::get('returns/create/{orderId}', 'Returns\ReturnController@create')->name('returns.create');
        Route::get('returns/show/', 'Returns\ReturnController@show')->name('returns.show');
        Route::post('returns/store/', 'Returns\ReturnController@store')->name('returns.store');
        Route::put('returns/update/{id}', 'Returns\ReturnController@update')->name('returns.update');
        Route::delete('returns/destroy/{id}', 'Returns\ReturnController@destroy')->name('returns.destroy');
        Route::get('returns/edit/{id}', 'Returns\ReturnController@edit')->name('returns.edit');


        /* invoice */
        Route::post('invoice/invoiceOrder/', 'Invoices\InvoiceController@invoiceOrder')->name('invoice.invoiceOrder');
        Route::get('invoice/index/{channel?}', 'Invoices\InvoiceController@index')->name('invoice.index');

        /* order line */
        Route::post('orderLine/processBackorders/', 'Orders\OrderLineController@processBackorders')->name('orderLine.processBackorders');
        Route::post('orderLine/doAllocation/', 'Orders\OrderLineController@doAllocation')->name('orderLine.doAllocation');
        Route::post('orderLine/search/{page?}', 'Orders\OrderLineController@search')->name('orderLine.search');

        /* warehouse */
        Route::post('warehouse/pickOrder/', 'Orders\WarehouseController@pickOrder')->name('warehouse.pickOrder');
        Route::post('warehouse/packOrder/', 'Orders\WarehouseController@packOrder')->name('warehouse.packOrder');
        Route::get('warehouse/index/', 'Orders\WarehouseController@index')->name('warehouse.index');
        Route::get('warehouse/getPicklist/{picklist}', 'Orders\WarehouseController@getPicklist')->name('warehouse.index');
        Route::post('warehouse/dispatchOrder/', 'Orders\WarehouseController@dispatchOrder')->name('warehouse.getPicklist');
        Route::get('warehouse/generateDispatchNote/{orderId}', 'Orders\WarehouseController@generateDispatchNote')->name('warehouse.generateDispatchNote');
        Route::get('warehouse/generatePicklist/{picklistRef}', 'Orders\WarehouseController@generatePicklist')->name('warehouse.generatePicklist');

        /* orders */
        Route::get('orders/create/{channel?}', 'Orders\OrderController@create')->name('orders.create');
        Route::get('orders/backorders/get', 'Orders\OrderController@backorders')->name('orders.backorders');
        Route::get('orders/allocations/get', 'Orders\OrderController@allocations')->name('orders.allocations');
        Route::post('orders/search/{page?}', 'Orders\OrderController@search')->name('orders.search');
        Route::post('orders/saveComment/', 'Orders\OrderController@saveComment')->name('orders.saveComment');
        Route::post('orders/cloneOrder/', 'Orders\OrderController@cloneOrder')->name('orders.cloneOrder');
        Route::post('orders/destroy/{id}', 'Orders\OrderController@destroy')->name('orders.destroy');

        /* Refunds */
        Route::post('refunds/doRefund/', 'Refunds\RefundController@doRefund')->name('refunds.doRefund');
        Route::resource('refunds', 'Refunds\RefundController');

        /* channels */
        Route::post('channels/addChannelToWarehouse', 'Channels\ChannelController@addChannelToWarehouse')->name('channels.addChannelToWarehouse');
        Route::delete('channels/deleteWarehouse/{id}', 'Channels\ChannelController@deleteWarehouse')->name('channels.deleteWarehouse');
        Route::delete('channels/deleteProvider/{id}', 'Channels\ChannelController@deleteProvider')->name('channels.deleteProvider');
        Route::delete('channels/deleteProduct/{product_id}/{channel_id}', 'Channels\ChannelController@deleteProduct')->name('channels.deleteProduct');
        Route::post('channels/saveChannelAttribute/', 'Channels\ChannelController@saveChannelAttribute')->name('channels.saveChannelAttribute');

        Route::post('channels/saveChannel/{channel}', 'Channels\ChannelController@saveChannel')->name('channels.saveChannel');
        Route::post('channels/addProductToChannel/', 'Channels\ChannelController@addProductToChannel')->name('channels.addProductToChannel');
        Route::post('channels/saveChannelTemplate/', 'Channels\ChannelController@saveChannelTemplate')->name('channels.saveChannelTemplate');
        Route::post('channels/addChannelProvider/', 'Channels\ChannelController@addChannelProvider')->name('channels.addChannelProvider');
        Route::post('channels/updateChannel', 'Channels\ChannelController@updateChannel')->name('channels.updateChannel');
        Route::resource('channels', 'Channels\ChannelController');
        Route::resource('channels', 'Channels\ChannelController');
        Route::get('admin.channels.remove.image', 'ChannelController@removeImage')->name('channel.remove.image');


        /* employees */
        Route::resource('employees', 'EmployeeController');
        Route::get('employees/{id}/profile', 'EmployeeController@getProfile')->name('employee.profile');
        Route::get('employees/{employeeId}/profile/detachchannel/{storeId}', 'EmployeeController@detachChannelAssigned')->name('employee.profile.detachchannel');
        Route::put('employees/{id}/profile', 'EmployeeController@updateProfile')->name('employee.profile.update');

        Route::resource('addresses', 'Addresses\AddressController');

        /* vouchers */
        Route::delete('voucher-codes/destroy/{id}', 'VoucherCodes\VoucherCodeController@destroy')->name('voucher-codes.destroy');
        Route::resource('voucher-codes', 'VoucherCodes\VoucherCodeController');
        Route::post('vouchers/updateVoucher', 'Vouchers\VoucherController@updateVoucher')->name('vouchers.updateVoucher');
        Route::resource('vouchers', 'Vouchers\VoucherController');
        Route::get('voucher-codes/batch/{id?}', 'VoucherCodes\VoucherCodeController@getCodesByBatch')->name('voucher-codes.batch');
        Route::get('voucher-codes/validate/{code}', 'VoucherCodes\VoucherCodeController@validateVoucherCode')->name('voucher-codes.validateVoucherCode');
        Route::get('vouchers/get/{channel}', 'Vouchers\VoucherController@getVouchersByChannel')->name('vouchers.getByChannel');
        Route::get('vouchers/create/{channel?}', 'Vouchers\VoucherController@create')->name('vouchers.create');
        Route::get('voucher-codes/add/{id}', 'VoucherCodes\VoucherCodeController@create')->name('voucher-codes.add');

        /* countries */
        Route::resource('countries', 'Countries\CountryController');
        Route::resource('countries.provinces', 'Provinces\ProvinceController');
        Route::resource('countries.provinces.cities', 'Cities\CityController');

        /* shipping */
        Route::resource('couriers', 'Couriers\CourierController');
        Route::resource('courier-rates', 'Couriers\CourierRateController');
        Route::post('courier-rates/search/{page?}', 'Couriers\CourierRateController@search')->name('courier-rates.search');
        Route::post('courier-rates/update/', 'Couriers\CourierRateController@update')->name('courier-rates.update');

        Route::resource('payment-methods', 'PaymentMethods\PaymentMethodController');
        Route::resource('attributes', 'Attributes\AttributeController');
        Route::resource('attributes.values', 'Attributes\AttributeValueController');
        Route::resource('roles', 'Roles\RoleController');
        Route::resource('permissions', 'Permissions\PermissionController');

        /* brands */
        Route::resource('brands', 'Brands\BrandController');
        Route::get('remove-image-brand', 'Brands\BrandController@removeImage')->name('brand.remove.image');

        /* channel prices */
        Route::post('channel-prices/getAvailiableProducts', 'ChannelPrices\ChannelPriceController@getAvailiableProducts')->name('channel-prices.getAvailiableProducts');
        Route::post('channel-prices/getProductsForSwap', 'ChannelPrices\ChannelPriceController@getProductsForSwap')->name('channel-prices.getProductsForSwap');
        Route::get('channel-prices/import', 'ChannelPrices\ChannelPriceController@import')->name('channel-prices.import');
        Route::post('channel-prices/saveImport', 'ChannelPrices\ChannelPriceController@saveImport')->name('channel-prices.saveImport');
        Route::delete('channel-prices/deleteAttribute/{id}', 'ChannelPrices\ChannelPriceController@deleteAttribute')->name('channel-prices.deleteAttribute');
        Route::resource('channel-prices', 'ChannelPrices\ChannelPriceController');
        Route::get('channel-prices/get/{channel}', 'ChannelPrices\ChannelPriceController@index')->name('channel-prices.index');
        Route::get('channel-prices/editForm/{product}/{channel}', 'ChannelPrices\ChannelPriceController@editForm')->name('channel-prices.editForm');
        Route::post('channel-prices/search/{page?}', 'ChannelPrices\ChannelPriceController@search')->name('channel-prices.search');
        Route::post('channel-prices/export', 'ChannelPrices\ChannelPriceController@export')->name('channel-prices.export');
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


    Route::get('customer-returns/index/', 'CustomerReturnController@index')->name('customer-returns.index');
    Route::get('customer-returns/create/{orderId}', 'CustomerReturnController@create')->name('customer-returns.create');
    Route::get('customer-returns/show/', 'CustomerReturnController@show')->name('customer-returns.show');
    Route::post('customer-returns/store/', 'CustomerReturnController@store')->name('customer-returns.store');
    Route::get('customer-returns/edit/{id}', 'CustomerReturnController@edit')->name('customer-returns.edit');
    Route::get('accounts', 'AccountsController@index')->name('accounts');


    Route::namespace('Payments')->group(function () {
        Route::get('bank-transfer', 'BankTransferController@index')->name('bank-transfer.index');
        Route::post('bank-transfer', 'BankTransferController@store')->name('bank-transfer.store');
    });
    
     Route::post('checkout.getShippingFee', 'CheckoutController@getShippingFee')->name('checkout.getShippingFee');

    Route::group(['middleware' => ['auth']], function () {
        Route::get('accounts', 'AccountsController@index')->name('accounts');
       
        Route::get('checkout', 'CheckoutController@index')->name('checkout.index');
        Route::post('checkout', 'CheckoutController@store')->name('checkout.store');
        Route::post('set-courier', 'CheckoutController@setCourier')->name('set.courier');
        Route::post('set-address', 'CheckoutController@setAddress')->name('set.address');
        Route::get('checkout/execute', 'CheckoutController@executePayPalPayment')->name('checkout.execute');
        Route::post('checkout/execute', 'CheckoutController@charge')->name('checkout.execute');
        Route::get('checkout/cancel', 'CheckoutController@cancel')->name('checkout.cancel');
        Route::get('checkout/success', 'CheckoutController@success')->name('checkout.success');
        Route::resource('customer', 'CustomerController');
        Route::resource('customer.address', 'CustomerAddressController');
    });
    Route::get('cart/validate/{code}', 'CartController@validateVoucherCode')->name('cart.validateVoucherCode');
    Route::resource('cart', 'CartController');
    Route::get("category/{slug}", 'CategoryController@getCategory')->name('front.category.slug');
    Route::post("filter", 'ProductController@filter')->name('filter.product');
    Route::get("search", 'ProductController@search')->name('search.product');
    Route::get("{product}", 'ProductController@show')->name('front.get.product');
});
