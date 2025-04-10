<?php

/**
 * Api Controller for Instant Lottery module.
 */

namespace Modules\GameInstantLottery\Controllers;

use Johncms\Http\JsonResponse;
use Johncms\Http\Request;
use Johncms\Http\Response;
use Modules\GameInstantLottery\Models\Player;
use Modules\GameInstantLottery\Models\GameSession;
use Modules\GameInstantLottery\Services\GameService;
use Johncms\Users\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;

/**
 * Class ApiController
 * @package Modules\GameInstantLottery\Controllers
 */
class ApiController
{
    private Request $request;
    private GameService $gameService;
    private User $user;
    private array $config;

    /**
     * ApiController constructor.
     * @param Request $request
     * @param GameService $gameService
     * @param User $user
     */
    public function __construct(Request $request, GameService $gameService, User $user)
    {
        $this->request = $request;
        $this->gameService = $gameService;
        $this->user = $user;
        $this->config = $this->loadConfig();
    }

    /**
     * Load module config
     * @return array
     */
    private function loadConfig(): array
    {
        return Config::get('game_instant_lottery');
    }

    /**
     * Get the current game state.
     * @return Response
     */
    public function getGameState(): Response
    {
        $gameSession = GameSession::where('user_id', $this->user->id)->first();

        if ($gameSession) {
            $gameState = $this->gameService->getGameState($gameSession);
            return new JsonResponse($gameState);
        }

        return new JsonResponse(['error' => 'Game not started'], 400);
    }

    /**
     * Handle a cell click event.
     * @return Response
     */
    public function handleCellClick(): Response
    {
        $gameSession = GameSession::where('user_id', $this->user->id)->first();
        $player = Player::where('user_id', $this->user->id)->first();
        if (!$player) {
            $player = Player::create(['user_id' => $this->user->id, 'total_balance' => 0]);
        }

        if (!$gameSession) {
            return Response::json(['error' => 'Game not started'], 400);
        }

        $row = $this->request->input('row');
        $col = $this->request->input('col');
        $result = $this->gameService->handleCellClick($gameSession, $row, $col);
        $gameSession->sessionBank = $result['sessionBank'];
        $gameSession->currentRow = $result['currentRow'];
        $gameSession->extraTurns = $result['extraTurns'];
        if ($result['gameFinished']) {
            $gameSession->gameFinished = true;
        }
        $gameSession->save();
        if ($gameSession->gameFinished) {
            if($result['win']) {
                $player->total_balance += $result['sessionBank'];
                $player->save();
            }
            $gameSession->delete();
        }

        return new JsonResponse($result);
    }

    /**
     * Handle a cash out event.
     * @return Response
     */
    public function handleCashOut(): Response
    {
        $gameSession = GameSession::where('user_id', $this->user->id)->first();
        $player = Player::where('user_id', $this->user->id)->first();
        if (!$player) {
            $player = Player::create(['user_id' => $this->user->id, 'total_balance' => 0]);
        }
        if ($gameSession) {
            $result = $this->gameService->cashOut($gameSession);
            $player->total_balance += $result['sessionBank'];
            $player->save();
            $gameSession->delete();
        } else {
            return new JsonResponse(['error' => 'Game not started'], 400);
        }

        if (!$gameSession) {
            return Response::json(['error' => 'Game not started'], 400);
        }

        $result = $this->gameService->handleCashOut($gameSession);

        return new JsonResponse($result);
    }
}