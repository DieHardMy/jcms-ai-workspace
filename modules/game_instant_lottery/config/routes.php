<?php

return [
    'routes' => [
        'game' => [
            'route' => '^game$',
            'file'  => 'modules/game_instant_lottery/Controllers/GameController.php',
        ],
        'api'  => [
            'route' => '^api$',
            'file'  => 'modules/game_instant_lottery/Controllers/ApiController.php',
        ],
    ],
];