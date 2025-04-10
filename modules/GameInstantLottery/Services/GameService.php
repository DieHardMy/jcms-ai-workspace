<?php 

namespace GameInstantLottery\Models;

use GameInstantLottery\Models\GameSession;
use GameInstantLottery\Models\Player;
use GameInstantLottery\Config\Config;

class GameService
{
     /**
     * @var array List of all available cell types
     */private array $cellTypes = [
        'SKULL',
        'COINS',
        'MULTIPLIER',
        'EXTRA_TURN',
        'EMPTY',
        'BOMB',
    ];

    /**
     * Generates a new game board and creates a new GameSession.
     *
     * @param int $rows The number of rows in the board.
     * @param int $cols The number of columns in the board.
     * @param int $userId The ID of the user creating the game.
     * @return GameSession The new GameSession.
     */
    public function createNewBoard(int $rows, int $cols, int $userId): GameSession
    {
        $board = [];
        // Generate each row
        for ($i = 0; $i < $rows; $i++) {
            $row = $this->generateRow($i, $cols);
             $board[] = $row;
        }
        $board = $this->ensureNonLosingRow($board);
        // Create a new GameSession
        $gameSession = new GameSession(uniqid(), $userId, $board, $rows - 1, 0, 0, false);
        return $gameSession;
    }

    /**
     * Create one game row with random cells types
     * @param int $rowNumber - number of current row
     * @param int $cols - count of colums
     * @return array
     */
    public function generateRow(int $rowNumber, int $cols): array
    {
         $row = [];
         for ($j = 0; $j < $cols; $j++) {
                $cellType = $this->generateCellType($rowNumber);
                $row[] = [
                    'type' => $cellType,
                    'opened' => false,
                    'value' => $this->generateCellValue($cellType),
                ];
            }
        return $row;
    }

    /**
     * Ensures that each row has at least one non-skull cell.
     *
     * @param array $board The game board.
     * @return array The modified game board.
     */
    private function ensureNonLosingRow(array $board): array
    {
        foreach ($board as &$row) {
            $hasNonSkull = false;
            foreach ($row as $cell) {
                if ($cell['type'] !== 'SKULL') {
                    $hasNonSkull = true;
                    break;
                }
            }
            if (!$hasNonSkull) {
                $randomIndex = array_rand($row);
                $row[$randomIndex]['type'] = $this->generateNonSkullCellType();
            }
        }
        return $board;
    }

    /**
     * Handles a cell click event in the game.
     *
     * @param GameSession $gameSession The current game session.
     * @param int $row The row of the clicked cell.
     * @param int $col The column of the clicked cell.
     * @return GameSession The updated game session.
     */
    public function handleCellClick(GameSession $gameSession, int $row, int $col): GameSession
    {
        if ($gameSession->isGameFinished()) {
            return $gameSession;
        }

        if ($row !== $gameSession->getCurrentRow()) {
            return $gameSession;
        }

         // Get a reference to the clicked cell
        $cell = &$gameSession->getBoard()[$row][$col];

        // Check if cell is opened
        if ($cell['opened']) {
            return $gameSession;
        }

        // Open cell
        $cell['opened'] = true;

        // Handle cell type action
        switch ($cell['type']) {
            case 'SKULL':
                $gameSession->setGameFinished(true);
                break;
            case 'COINS':
                $gameSession->setSessionBank($gameSession->getSessionBank() + $cell['value']);
                break;
            case 'MULTIPLIER':
                $gameSession->setSessionBank($gameSession->getSessionBank() * $cell['value']);
                break;
            case 'EXTRA_TURN':
                $gameSession->setExtraTurns($gameSession->getExtraTurns() + 1);
                break;
            case 'BOMB':
                $this->openBombCells($gameSession, $row, $col);
                break;
            case 'EMPTY':
                break;
        }
        // Check extra turn
        if($gameSession->getExtraTurns() > 0 && !$gameSession->isGameFinished() && $gameSession->getCurrentRow() >=0){
             $gameSession->setExtraTurns($gameSession->getExtraTurns() - 1);
        }else {
            $gameSession = $this->nextRow($gameSession);
        }

        // Check win
         if ($gameSession->getCurrentRow() < 0 && !$gameSession->isGameFinished()) {
                $gameSession->setGameFinished(true);
            }
        return $gameSession;
    }

    /**
     * Cash out the current game session and update the player's balance.
     *
     * @param GameSession $gameSession The current game session.
     * @param Player $player The player whose balance should be updated.
     * @return Player The updated player.
     */
    public function cashOut(GameSession $gameSession, Player $player): Player
    {
        $player->setBalance($player->getBalance() + $gameSession->getSessionBank());
        $gameSession->setGameFinished(true);
        return $player;
    }
    /**
     * Get game result.
     * @param GameSession $gameSession
     * @return array
     */
    public function getGameResult(GameSession $gameSession): array
    {
        // Check if the game is finished
        if ($gameSession->isGameFinished()) {
            if ($gameSession->getCurrentRow() < 0) {
                return ['result' => 'win', 'bank' => $gameSession->getSessionBank()];
            } else {
                return ['result' => 'lose', 'bank' => 0];
            }
        }
        return ['result' => 'playing', 'bank' => $gameSession->getSessionBank()];
    }
     /**
     * Generates a cell type based on the row number and predefined probabilities.
     *
     * @param int $row The row number.
     * @return string The generated cell type.
     */
    private function generateCellType(int $row): string
    {
        $config = Config::getConfig();
        $cellTypesConfig = $config['cell_types'];
        $rand = mt_rand(0, 99);
        $currentChance = 0;
        // Dynamic skull chance by row
        $skullChance = $cellTypesConfig['skull']['chance'] + ($row * $cellTypesConfig['skull']['increase_per_row']);
       
         // If random number < skull chance, return SKULL
        if($rand < $skullChance){
            return 'SKULL';
        }

         // Exclude skull from the chance
        $availableTypesChance = 100 - $skullChance;
         // Set each chance to relative value
         $cellTypesConfig['coins']['chance'] = $cellTypesConfig['coins']['chance'] /100 * $availableTypesChance;
         $cellTypesConfig['multiplier']['chance'] = $cellTypesConfig['multiplier']['chance'] /100 * $availableTypesChance;
         $cellTypesConfig['extra_turn']['chance'] = $cellTypesConfig['extra_turn']['chance'] /100 * $availableTypesChance;
         $cellTypesConfig['bomb']['chance'] = $cellTypesConfig['bomb']['chance'] /100 * $availableTypesChance;
         $cellTypesConfig['empty']['chance'] = $cellTypesConfig['empty']['chance'] /100 * $availableTypesChance;

         // Iterate available types to check which type is now
        foreach ($cellTypesConfig as $type => $data) {
             if($type == 'skull'){
                 continue;
            }
            $currentChance += $data['chance'];
            if ($rand <= $currentChance) {
                 return strtoupper($type);
            }
        }
        return 'EMPTY';
    }

    /**
     * Generates a non-skull cell type.
     *
     * @return string The generated cell type.
     */
    private function generateNonSkullCellType(): string
    {   
        $config = Config::getConfig();
        $cellTypesConfig = $config['cell_types'];
        $cellTypes = array_diff_key($cellTypesConfig, ['skull' => '']); // Remove skull

        $rand = mt_rand(0, 100);
        $currentChance = 0;
        foreach ($cellTypes as $type => $data) {
            $currentChance += $data['chance'];
            if ($rand <= $currentChance) {
                return strtoupper($type);
            }
        }
        return 'EMPTY';
    }

    /**
     * Generates a cell value based on the cell type.
     *
     * @param string $cellType The cell type.
     * @return int The cell value.
     */
    private function generateCellValue(string $cellType)
    {
        switch ($cellType) {
            case 'COINS':
                return rand(1, 10);
            case 'MULTIPLIER':
                return rand(2, 3);
            default:
                return 0;
        }
    }
    /**
     * Decrements the current row of the game session.
     *
     * @param GameSession $gameSession The game session.
     * @return GameSession The updated game session.
     */
    private function nextRow(GameSession $gameSession): GameSession
    {
        $gameSession->setCurrentRow($gameSession->getCurrentRow() - 1);
        return $gameSession;
    }

    /**
     * Open one random cells if cell type is BOMB
     * @param GameSession &$gameSession
     * @param int $row
     * @param int $col
     */
    
    private function openBombCells(GameSession &$gameSession, int $row, int $col): void {
        $board = &$gameSession->getBoard();
        $rowsCount = count($board);
        $colsCount = count($board[0]);
        $affectedCells = [];

        $directions = [
            [-1, 0], // Up
            [1, 0],  // Down
            [0, -1], // Left
            [0, 1],  // Right
        ];

        foreach ($directions as [$rowOffset, $colOffset]) {
            $newRow = $row + $rowOffset;
            $newCol = $col + $colOffset;

            if ($newRow >= 0 && $newRow < $rowsCount && $newCol >= 0 && $newCol < $colsCount) {
                if (!$board[$newRow][$newCol]['opened']) {
                   $affectedCells[] = [$newRow,$newCol];
                }
            }
        }
        
        if(!empty($affectedCells)){
             $randomCellKey = array_rand($affectedCells);
            list($randomRow, $randomCol) = $affectedCells[$randomCellKey];
           $board[$randomRow][$randomCol]['opened'] = true;
        }
    }
}