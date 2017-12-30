<?php

use Illuminate\Routing\Router;

Admin::registerHelpersRoutes();

Route::group([
    'prefix'        => config('admin.prefix'),
    'namespace'     => Admin::controllerNamespace(),
    'middleware'    => ['web', 'admin'],
], function (Router $router) {

    $router->get('/', 'HomeController@index');
    $router->resource('advertisements', AdvertisementController::class);
    $router->get('report/index', 'ReportController@index');
//    $router->resource('channels', ChannelController::class);
    $router->get('channels', 'ChannelController@index');
});
