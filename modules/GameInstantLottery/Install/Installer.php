<?php 
declare(strict_types=1);

/**
 * @package     JohnCMS
 * @link        http://johncms.com
 * @copyright   Copyright (C) 2008-2023 JohnCMS Community
 * @license     https://johncms.com/license
 */

namespace GameInstantLottery\Install;

use PDO;
use PDOException;
use Johncms\System\Database\PdoFactory;

/**
 * Class Installer
 * @package GameInstantLottery\Install
 */
class Installer
{
    /**
     * @var PDO PDO Database connection
     */
    private $pdo;

    /**
     * Installer constructor.
     *
     * @param PdoFactory $pdoFactory PDO Factory instance
     */
    public function __construct(PdoFactory $pdoFactory)
    {
        $this->pdo = $pdoFactory->create();
    }

    /**
     * Install the module.
     */
    public function install()
    {
        // Create the game_players table
        $this->createGamePlayersTable();
    }

    /**
     * Creates the game_players table if it doesn't exist.
     */
    private function createGamePlayersTable()
    {
        $query = "
            CREATE TABLE IF NOT EXISTS `game_players` (
                `user_id` INT(11) UNSIGNED NOT NULL PRIMARY KEY COMMENT 'User ID',
                `total_balance` INT(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Total balance'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Game Players';
        ";

        try {
            // Execute the query to create the table
            $this->pdo->exec($query);
            // You can add logs or more actions here if needed
        } catch (PDOException $e) {
            // Log the error message
            error_log('Error creating game_players table: ' . $e->getMessage());
            // Optionally, throw an exception or handle the error in another way
        }
    }
}