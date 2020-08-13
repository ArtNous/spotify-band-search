<?php

use App\Controllers\SpotifyController;
use Slim\App;

function createRoutes(App $app) {
    $app->group('/api/v1', function($app) {

        $app->post('/token', SpotifyController::class . ':login');
        $app->get('/albums', SpotifyController::class . ':getSpotifyData');

    });
}