<?php

namespace JoyBox\Core\Tests\Unit\Controllers;

use PHPUnit\Framework\TestCase;
use Mockery;

// Подключаем классы вручную
require_once MODULES_PATH . '/JoyBox/Core/Controllers/BaseGameController.php';
require_once MODULES_PATH . '/JoyBox/Core/Controllers/GameController.php';
require_once MODULES_PATH . '/JoyBox/Core/Models/Game.php';
require_once MODULES_PATH . '/JoyBox/Core/Services/GameService.php';

class GameControllerTest extends TestCase
{
    public function testIndex()
    {
        // Мок модели Game
        $mockGame = Mockery::mock('Game'); // Используем строковое имя класса
        $mockGame->shouldReceive('all')->andReturn([
                                                       ['id' => 1, 'name' => 'Game 1'],
                                                       ['id' => 2, 'name' => 'Game 2'],
                                                   ]);

        // Создаем экземпляр контроллера с моком
        $controller = new \JoyBox\Core\Controllers\GameController($mockGame);

        // Вызываем метод index
        $result = $controller->index();

        // Проверяем, что результат содержит ожидаемые данные
        $this->assertStringContainsString('Game 1', $result);
        $this->assertStringContainsString('Game 2', $result);
    }

    public function testShow()
    {
        // Мок сервиса GameService
        $mockGameService = Mockery::mock('GameService'); // Используем строковое имя класса
        $mockGameService->shouldReceive('getGameById')
            ->with(1)
            ->andReturn(['id' => 1, 'name' => 'Test Game']);

        // Создаем экземпляр контроллера
        $controller = new \JoyBox\Core\Controllers\GameController();

        // Вызываем метод show с ID игры
        $result = $controller->show(1, $mockGameService);

        // Проверяем, что результат содержит ожидаемые данные
        $this->assertStringContainsString('Test Game', $result);
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }
}
