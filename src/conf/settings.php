<?php

$settings = [
    'displayErrorDetails' => true,
    'filesystem' => [
        'default' => 'public',
        'disks' => [
            'public' => [
                'driver' => 'local',
                'root' => __DIR__,
                'url' => $_SERVER['HTTP_HOST'].'/storage',
                'visibility' => 'public',
                'permissions' => [
                    'file' => [
                        'public' => 0777,
                    ],
                    'dir' => [
                        'public' => 0777,
                    ],
                ],
            ]
        ]
    ]
];