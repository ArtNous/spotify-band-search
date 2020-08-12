<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Controllers\SpotifyController;
use App\Services\SpotifyHttpClient;
use Slim\App;

function createRoutes(App $app) {
    $app->group('/api/v1', function($app) {

        $app->get('/token', SpotifyController::class . ':login');
        $app->get('/albums', SpotifyController::class . ':getSpotifyData');

    });
}