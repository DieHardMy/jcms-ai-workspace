<?php

namespace GameInstantLottery\Models;

/**
 * Class GameSession
 * Represents a single game session, storing game state information.
 */
class GameSession
{
    private const WINDOW_SIZE = 5;
    private int $sessionId;
    private int $userId;
    private array $board;
    private $sessionBank;
    private $extraTurns;
    private $gameFinished;

    /**
     * GameSession constructor.
     *
     * @param int $sessionId
     * @param int $userId
     * @param array $board
     * @param int $currentRow
     * @param int $sessionBank
     * @param int $extraTurns
     * @param bool $gameFinished
     */
    public function __construct($sessionId, $userId, $board = [], $currentRow = 0, $sessionBank = 0, $extraTurns = 0, $gameFinished = false)
    {
        $this->sessionId = $sessionId;
        $this->userId = $userId;
        $this->board = $board;
        $this->currentRow = $currentRow;
        $this->sessionBank = $sessionBank;
        $this->extraTurns = $extraTurns;
        $this->gameFinished = $gameFinished;
    }

    // Getters and setters for the class properties.

    /**
     * @return int
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }

    /**
     * @param int $sessionId
     * @return void
     */
    public function setSessionId(int $sessionId): void
    {
        $this->sessionId = $sessionId;
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }


    /**
     * @param int $userId
     * @return void
     */
    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }

    /**
     * @return array
     */
    public function getBoard()
    {
        return $this->board;
    }

    /**
     * @param array $board
     * @return void
     */
    public function setBoard(array $board): void
    {
        $this->board = $board;
    }

    /**
     * @return int
     */
    public function getCurrentRow()
    {
        return $this->currentRow;
    }

    /**
     * @param int $currentRow
     * @return void
     */
    public function setCurrentRow(int $currentRow): void
    {
        $this->currentRow = $currentRow;
    }

    /**
     * @return int
     */
    public function getSessionBank()
    {
        return $this->sessionBank;
    }

    /**
     * @param int $sessionBank
     * @return void
     */
    public function setSessionBank(int $sessionBank): void
    {
        $this->sessionBank = $sessionBank;
    }

    /**
     * @return int
     */
    public function getExtraTurns()
    {
        return $this->extraTurns;
    }

    /**
     * @param int $extraTurns
     * @return void
     */
    public function setExtraTurns(int $extraTurns): void
    {
        $this->extraTurns = $extraTurns;
    }

    /**
     * Adds an extra turn to the game session.
     */
    public function addExtraTurn()
    {
        $this->extraTurns++;
    }

    /**
     * Gets a subset of the board that is visible to the user, based on the current row and window size.
     *
     * @return array The visible board rows.
     */
    public function getVisibleBoard(): array
    {
        $boardRows = count($this->board);
        $startRow = max(0, $this->currentRow - (int)floor(self::WINDOW_SIZE / 2));
        $endRow = min($boardRows - 1, $startRow + self::WINDOW_SIZE - 1);

        if ($endRow - $startRow + 1 < self::WINDOW_SIZE) {
            $startRow = max(0, $endRow - self::WINDOW_SIZE + 1);
        }

        return array_slice($this->board, $startRow, self::WINDOW_SIZE);
    }

    // Methods to manage the game state.

    /**
     * @return bool
     */
    public function isGameFinished()
    {
        return $this->gameFinished;
    }

    /**
     * @param bool $gameFinished
     * @return void
     */
    public function setGameFinished(bool $gameFinished): void
    {
        $this->gameFinished = $gameFinished;
    }
}