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
        $limit = $request->getParam('limit');
        $offset = $request->getParam('offset');

        if($bandToSearch === '') {
            return $response->withJson([ 'error' => 'Band name is empty. Please search something.' ], 400);
        }

        $res = $this->client->getData($bandToSearch, $limit, $offset);
        
        return $response->withJson($res[0], $res[1]);
    }

    public function login(Request $request, Response $response, array $args) {
        $clientId = $request->getParam('client_id');
        $clientSecret = $request->getParam('client_secret');

        $invalidClientId = empty($clientId) || is_null($clientId);
        $invalidClientSecret = empty($clientSecret) || is_null($clientSecret);
        if($invalidClientId || $invalidClientSecret) {
            return $response->withJson([
                'error' => 'Invalid credentials. Try again.'
            ], 401);
        }
        
        $res = $this->client->getAccessToken($clientId, $clientSecret);

        return $response->withJson($res[0], $res[1]);
    }
}