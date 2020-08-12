<?php

namespace App\Services;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Database\Query\Builder;
use GuzzleHttp\Client;
use App\Models\Album;
use App\Models\Token;

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
    public function getData(string $band, string $uri = 'search') {
        $tokenExists = $this->fileManager->has('token.txt');
        
        if($tokenExists) {
            $token = $this->fileManager->read('token.txt');
        } else {
            $token = $this->getAccessToken();
        }

        try {
            
            $response = static::createClient()->request('GET', $uri, [
                'headers' => [
                    'Authorization' => "Bearer $token",
                    'Content-Type' => 'application/json'
                ],
                'query' => [
                    'q' => 'artist:' . urlencode($band),
                    'type' => 'album'
                ]
            ]);
            
        } catch (\Throwable $th) {
            $code = $th->getResponse()->getStatusCode();
            if($th instanceof ConnectException) {
                return [ 'msg' => 'Connection refused. Try again please.' . $th->getMessage() ];
            } else {
                $responseArray['error'] = $th->getResponse()->getReasonPhrase();
            }

            if($code === 401 || $code === 400) {
                $responseArray['loginUrl'] = 'http://' . $_SERVER['HTTP_HOST'] . '/api/v1/token';
            }

            return $responseArray;
        }

        $code = $response->getStatusCode();
        if($code !== 200) return false;

        $albums = [];

        $data = json_decode($response->getBody());

        if($data->albums->total === 0) {
            return [ 'msg' => 'No data. Try another band.' ];
        }

        foreach($data->albums->items as $item) {
            if($item->album_type === 'album') {
                $albums[] = (new Album($item))->toArray();
            }
        }

        return $albums;
    }

    /**
     * Make login on spotify
     * and set the token locally
     * to get the data
     * @return array
     */
    public function getAccessToken() {
        $auth = 'Basic '. base64_encode($this->container['settings']['spotify_client_id'] . ':' . $this->container['settings']['spotify_client_secret']);
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
            $this->fileManager->put('token.txt', $token);

            return [
                'msg' => 'Authenticated!',
                'search_url' => 'http://' . $_SERVER['HTTP_HOST'] . '/api/v1/albums?q=leonardo'
            ];;

        } catch (\Throwable $th) {
            if($th instanceof ConnectException) {
                return [ 'error' => 'Connection refused. Try again please.' . $th->getMessage() ];
            } else if ($th instanceof ClientConnection) {
                return [
                    'error' => $th->getResponse()->getReasonPhrase(),
                    'msg' => $th->getMessage()
                ];
            } else {
                return [
                    'error' => $th->getMessage()
                ];
            }
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
            'timeout'  => 5.0,
        ]);

        return $client;
    }

}