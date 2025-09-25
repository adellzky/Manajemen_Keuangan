<?php

use App\Admin\Controllers\CashController;
use App\Admin\Controllers\GajiController;
use App\Admin\Controllers\PendapatanController;
use App\Admin\Controllers\ProjectController;
use App\Admin\Controllers\KasController;
use App\Admin\Controllers\KeuanganController;
use App\Admin\Controllers\MitraController;
use App\Admin\Controllers\PengeluaranController;
use App\Admin\Controllers\TimController;
use Illuminate\Routing\Router;
use Dcat\Admin\Admin;
use Illuminate\Support\Facades\Route;


Admin::routes();

Route::group([
    'prefix'     => config('admin.route.prefix'),
    'namespace'  => config('admin.route.namespace'),
    'middleware' => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'HomeController@index');

    $router->resource('/mitra', MitraController::class);
    $router->resource('/project', ProjectController::class);
    $router->get('pendapatan/pdf/{id?}', [PendapatanController::class, 'exportPdf'])->name('pendapatan.pdf');
    $router->resource('/pendapatan', PendapatanController::class);
    $router->get('keuangan/pdf', [KasController::class, 'exportPdf']);
    $router->resource('/keuangan', KasController::class);
    $router->get('pengeluaran/pdf', [PengeluaranController::class, 'exportPdf']);
    $router->resource('/pengeluaran', PengeluaranController::class);
    $router->resource('/tim', TimController::class);
    $router->resource('/gaji', GajiController::class);
    $router->resource('/keuangan-project', KeuanganController::class);
    $router->get('tim/{id}/slip', [TimController::class, 'slip']);

});

