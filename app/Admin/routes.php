<?php

use App\Admin\Controllers\PendapatanController;
use App\Admin\Controllers\ProjectController;
use App\Admin\Controllers\KasController; // ✅ tambahin controller kas
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
    $router->resource('/project', ProjectController::class);
    $router->resource('/pendapatan', PendapatanController::class);
    $router->resource('/kas', KasController::class); 
    $router->resource('/pengeluaran', \App\Admin\Controllers\PengeluaranController::class);

});
