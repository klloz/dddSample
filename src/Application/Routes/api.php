<?php

use Illuminate\Routing\Router;

/** @var Router $router */

# Notifications
$router->group([
    'prefix' => 'notifications',
    'middleware' => ['api.auth'],
], function () use ($router) {
    $router->get('/', [
        'uses' => 'Notifications\NotificationController@list'
    ]);
    $router->get('/non-displayed', [
        'uses' => 'Notifications\NotificationController@nonDisplayedCount'
    ]);
    $router->put('/display', [
        'uses' => 'Notifications\NotificationController@display'
    ]);
    $router->put('/read', [
        'uses' => 'Notifications\NotificationController@read'
    ]);
});
