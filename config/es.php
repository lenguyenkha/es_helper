<?php

return [
    'enabled' => env('SEARCH_ENABLED', false),
    'hosts' => [
        env('SEARCH_HOSTS', 'elasticsearch')
    ],
    'index' => [

    ]
];