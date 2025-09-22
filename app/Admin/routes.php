<?php

use App\Admin\Controllers\GajiController;
use App\Admin\Controllers\PendapatanController;
use App\Admin\Controllers\ProjectController;
use App\Admin\Controllers\KasController;
use App\Admin\Controllers\KeuanganController;
use App\Admin\Controllers\MitraController;
use App\Admin\Controllers\PengeluaranController;
use App\Admin\Controllers\TimController;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Dcat\Admin\Admin;

Admin::routes();

Route::group([
    'prefix'     => config('admin.route.prefix'),
    'namespace'  => config('admin.route.namespace'),
    'middleware' => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'HomeController@index');
    $router->resource('/mitra', MitraController::class);
    $router->resource('/project', ProjectController::class);
    $router->resource('/pendapatan', PendapatanController::class);
    $router->resource('/kas', KasController::class);
    $router->resource('/pengeluaran', PengeluaranController::class);
    $router->resource('/tim', TimController::class);
    $router->resource('/gaji', GajiController::class);
    $router->resource('/keuangan', KeuanganController::class);
    Route::get('tim/{id}/slip', [TimController::class, 'slip']);

});
