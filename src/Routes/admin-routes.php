<?php

use Illuminate\Support\Facades\Route;

use Webkul\UpsShipping\Http\Controllers\Admin\ShipmentController;

Route::group(['middleware' => ['web', 'admin'], 'prefix' => config('app.admin_url').'/ups-shipping'], function () {
    /**
     * Order Shippment
     */
    Route::controller(ShipmentController::class)->prefix('order')->group(function () {
        Route::get('track/{tracking_id}', 'orderTracking')->name('admin.ups_shipping.order.track');
    });
});