<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Controllers\SpotifyController;
use App\Services\SpotifyHttpClient;
use App\Services\HttpClient;
use Slim\App;
use Slim\Container;

require '../vendor/autoload.php';
require '../src/routes/api.php';
require '../src/conf/settings.php';

$configuration = [ 'settings' => $settings ];

$container = new Container($configuration);

$container['storage'] = function($container) {
    return new \Ilhamarrouf\Filesystem\FilesystemManager($container);
};

$container['App\Controllers\SpotifyController'] = function ($container) {
    $fileManager = $container->storage->disk('public');
    $spotifyClient = new SpotifyHttpClient($container, $fileManager);

    return new SpotifyController($container, $spotifyClient);
};

$app = new App($container);

/**
 * Check the routes/api.php file
 */
createRoutes($app);

$app->run();