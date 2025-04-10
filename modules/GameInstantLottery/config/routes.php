<?php

namespace Modules\GameInstantLottery\Config;

declare(strict_types=1);

use Modules\GameInstantLottery\Controllers\GameController;use Modules\GameInstantLottery\Controllers\ApiController;

// Страница игры
$map->addRoute(['GET'], '/lottery', [GameController::class, 'index']);

// API эндпоинты
$map->addRoute(['POST'], '/api/lottery/start', [ApiController::class, 'startGame']);
$map->addRoute(['POST'], '/api/lottery/reveal', [ApiController::class, 'revealCell']);
$map->addRoute(['POST'], '/api/lottery/cashout', [ApiController::class, 'cashOut']);