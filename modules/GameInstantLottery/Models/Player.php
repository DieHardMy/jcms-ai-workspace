<?php

/**
 * Player Model
 *
 * This class represents a player in the Instant Lottery game.
 */

namespace GameInstantLottery\Models;

use Johncms\Registry;

class Player
{
    /**
     * @var int The user ID
     */
    private int $userId;
    /**
     * @var int The user's total balance
     */
    private int $totalBalance;

    /**
     * Player constructor.
     *
     * @param int $userId The user ID
     * @param int $totalBalance The user's total balance
     */
    public function __construct(int $userId, int $totalBalance = 0)
    {
        $this->userId = $userId;
        $this->totalBalance = $totalBalance;
    }

    /**
     * Get the user ID.
     *
     * @return int The user ID
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * Get the user's total balance.
     *
     * @return int The user's total balance
     */
    public function getTotalBalance(): int
    {
        return $this->totalBalance;
    }

    /**
     * Update the user's total balance.
     *
     * @param int $amount The amount to add to the balance (can be negative)
     */
    public function updateBalance(int $amount): void
    {
        $this->totalBalance += $amount;
        $db = Registry::get('db');
        $db->update('game_players', ['total_balance' => $this->totalBalance], ['user_id' => $this->userId]);
    }

    /**
     * Create player in database
     */
    public function create()
    {
        $db = Registry::get('db');
        $db->insert('game_players', ['user_id' => $this->userId, 'total_balance' => $this->totalBalance]);
    }
}