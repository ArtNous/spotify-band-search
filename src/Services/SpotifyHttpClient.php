<?php

namespace App\Services;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Client;
use App\Models\Album;

class SpotifyHttpClient {

    private const BASE_URL = 'https://api.spotify.com/v1/';
    
    private const LOGIN_URL = 'https://accounts.spotify.com/api/';
    
    public function __construct($container, $fileManager) {
        $this->fileManager = $fileManager;
        $this->container = $container;
    }
    
    /**
     * Get the band data from
     * the Spotify API endpoint
     * @param string $band Spotify resource ID
     * @param string $uri Spotify endpoint
     * @return array $responseArray
     */
    public function getData(string $band, $limit = null, $offset = null, string $uri = 'search') {
        $tokenExists = $this->fileManager->has('token.txt');
        
        if($tokenExists) {
            $token = $this->fileManager->read('token.txt');
        } else {
            return [[
                'error' => 'Make a POST request to the next url to login',
                'loginUrl' => 'http://' . $_SERVER['HTTP_HOST'] . '/api/v1/token'
            ], 401];
        }

        $queryParams['q'] = 'artist:' . urlencode($band);
        $queryParams['type'] = 'album';
        if($limit) $queryParams['limit'] = $limit;
        if($offset) $queryParams['offset'] = $offset;

        try {
            
            $response = static::createClient()->request('GET', $uri, [
                'headers' => [
                    'Authorization' => "Bearer $token",
                    'Content-Type' => 'application/json'
                ],
                'query' => $queryParams
            ]);
            
        } catch (\Throwable $th) {
            $code = null;
            if($th instanceof ConnectException) {
                return [[ 'error' => 'Connection refused. Try again please.' . $th->getMessage() ], 408];
            } else {
                $code = $th->getResponse()->getStatusCode();
                if($code === 401) {
                    $responseArray['msg'] = 'Make a POST request to the next url to login';
                    $responseArray['loginUrl'] = 'http://' . $_SERVER['HTTP_HOST'] . '/api/v1/token';
                }
                $responseArray['error'] = $th->getMessage();
            }

            return [$responseArray, $code];
        }

        $data = json_decode($response->getBody());
        if($data->albums->total === 0) {
            return [[ 'msg' => 'No data. Try another band.' ], 200];
        }

        $albums = [];
        foreach($data->albums->items as $item) {
            if($item->album_type === 'album') {
                $albums[] = (new Album($item))->toArray();
            }
        }

        return [$albums, $response->getStatusCode()];
    }

    /**
     * Make login on spotify
     * and store the token locally
     * to access the data
     * @param string $clientId
     * @param string $clientSecret
     * @return array
     */
    public function getAccessToken(string $clientId, string $clientSecret) {
        $auth = 'Basic '. base64_encode("$clientId:$clientSecret");
        try {
            $response = static::createClient(self::LOGIN_URL)->post('token', [
                'form_params' => [
                    'grant_type' => 'client_credentials',
                ],
                'headers' => [
                    'Authorization' => $auth,
                    'Accept' => 'application/json',
                ],
            ]);

            $token = json_decode($response->getBody())->access_token;
            $fileResponse = $this->fileManager->put('token.txt', $token);
            
            if(!$fileResponse) {
                return [[ 'msg' => 'Permission error. Could not write to the path.' ], 403];
            }

            return [[
                'msg' => 'Authenticated! Make a GET request to the next url to search something.',
                'search_url' => 'http://' . $_SERVER['HTTP_HOST'] . '/api/v1/albums?q=leonardo'
            ], 200];

        } catch (\Throwable $th) {
            return [[
                'error' => $th instanceof ConnectException ? 'Connection refused. Try again please.' . $th->getMessage() : $th->getMessage()
            ], $th instanceof ConnectException ? 408 : $th->getResponse()->getStatusCode()];
        }
    }

    /**
     * Set up the http client
     * @param string $url
     * @return Client $client
     */
    protected static function createClient($url = null) {
        $client = new Client([
            'base_uri' => $url ?? self::BASE_URL,
            'timeout'  => 2,
        ]);

        return $client;
    }

}