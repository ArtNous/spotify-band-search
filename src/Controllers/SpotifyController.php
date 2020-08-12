<?php

namespace App\Controllers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Services\SpotifyHttpClient;

class SpotifyController {

    public function __construct($container, $client) {
        $this->client = $client;
        $this->container = $container;
    }
    
    public function getSpotifyData(Request $request, Response $response, array $args) {
        $bandToSearch = $request->getParam('q');

        if($bandToSearch === '') {
            return $response->withJson([ 'error' => 'Band name is empty. Please search something.' ], 400);
        }

        $res = $this->client->getData($bandToSearch);
        
        return $response->withJson($res);
    }

    public function login(Request $request, Response $response, array $args) {
        $res = $this->client->getAccessToken();

        return $response->withJson($res);
    }
}