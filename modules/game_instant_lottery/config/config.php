<?php declare(strict_types=1);

/**
 * @package     JohnCMS
 * @copyright   Copyright (C) 2008-2023 JohnCMS Community
 * @license     https://johncms.com/license
 */

namespace GameInstantLottery\Config;

/**
 * Configuration file for the Instant Lottery module.
 */

return [
    // Module basic information
    'module_name'    => 'Instant Lottery',
    'module_version' => '1.0.0',

    // Board parameters
    'min_rows'     => 5, // Minimum number of rows
    'max_rows'     => 50, // Maximum number of rows
    'default_rows' => 10, // Default number of rows
    'min_cols'     => 3, // Minimum number of columns
    'max_cols'     => 5, // Maximum number of columns
    'default_cols' => 4, // Default number of columns

    // Coins parameters
    'coins_range_min' => 1, // Minimum coin value
    'coins_range_max' => 10, // Maximum coin value

    // Multiplier parameters
    'multipliers' => [2, 3], // Array of available multipliers

    // Cell types and their probability percentage
    'cell_types' => [
        'skull'       => 10,  // Skull - Game Over
        'coins'       => 30,  // Coins - Adds to session bank
        'multiplier'  => 10, // Multiplier - Multiplies session bank
        'extra_turn'  => 5,  // Extra Turn - Gives an extra turn
        'empty'       => 35, // Empty - Nothing happens
        'bomb'        => 10,  // Bomb - Opens adjacent cells
    ],
];