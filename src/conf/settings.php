<?php

$settings = [
    /**
     * Spotify credentials
     */
    'spotify_client_id' => '',
    'spotify_client_secret' => '',
    'displayErrorDetails' => true,
    'filesystem' => [
        'default' => 'public',
        'disks' => [
            'public' => [
                'driver' => 'local',
                'root' => __DIR__.'/../storage/',
                'url' => $_SERVER['HTTP_HOST'].'/storage',
                'visibility' => 'public',
            ]
        ]
    ]
];