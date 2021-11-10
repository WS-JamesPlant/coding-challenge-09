<?php

/**
 * Scrabble bot document
 * 
 * PHP Version 8.0.6
 * 
 * @category WhiteStores
 * @package  WhiteStores_CodingChallenge
 * @author   James Plant <james.plant@whitestores.co.uk>
 * @license  https://whitestores.co.uk UNLICENSED
 * @link     https://github.com/WS-JamesPlant/coding-challenge-09/
 */

namespace App;

/**
 * Scrabble bot class
 *
 * @category WhiteStores
 * @package  WhiteStores_CodingChallenge
 * @author   James Plant <james.plant@whitestores.co.uk>
 * @license  https://whitestores.co.uk UNLICENSED
 * @link     https://github.com/WS-JamesPlant/coding-challenge-09/
 */
class Scrabble
{
    // The number of tiles in a game of scrabble
    private static $_tiles = [
        '*' => 2, 'A' => 9, 'B' => 2, 'C' => 2, 'D' => 4, 'E' => 12,
        'F' => 2, 'G' => 3, 'H' => 2, 'I' => 9, 'J' => 1, 'K' => 1,
        'L' => 4, 'M' => 2, 'N' => 6, 'O' => 8, 'P' => 2, 'Q' => 1,
        'R' => 6, 'S' => 4, 'T' => 6, 'U' => 4, 'V' => 2, 'W' => 2,
        'X' => 1, 'Y' => 2, 'Z' => 1,
    ];

    // Each tiles score
    private static $_tileScores = [
        '*' => 0, 'A' => 1, 'B' => 3, 'C' => 3, 'D' => 2, 'E' => 1,
        'F' => 4, 'G' => 2, 'H' => 4, 'I' => 1, 'J' => 8, 'K' => 5,
        'L' => 1, 'M' => 3, 'N' => 1, 'O' => 1, 'P' => 3, 'Q' => 10,
        'R' => 1, 'S' => 1, 'T' => 1, 'U' => 1, 'V' => 4, 'W' => 4,
        'X' => 8, 'Y' => 4, 'Z' => 10,
    ];

    // What's left in the scrabble bag
    private static $_bag = [];

    // The full board
    private static $_board = [];

    // All special spaces are listed in the following objects
    private static $_doubleLetter = [
        [3, 0], [11, 0],
        [6, 2], [8, 2],
        [0, 3], [7, 3], [15, 3],
        [2, 6], [6, 6], [8, 6], [13, 6],
        [3, 7], [12, 7],
        [2, 8], [6, 8], [8, 8], [13, 8],
        [0, 12], [7, 12], [15, 12],
        [6, 13], [8, 13],
        [3, 15], [11, 15],
    ];
    private static $_doubleWord = [
        [7, 7],
        [1, 1], [2, 2], [3, 3], [4, 4],
        [14, 1], [13, 2], [12, 3], [11, 4],
        [1, 14], [2, 13], [3, 12], [4, 11],
        [14, 14], [13, 13], [12, 12], [11, 11],
    ];
    private static $_tripleLetter = [
        [5, 1], [9, 1],
        [1, 5], [5, 5], [9, 5], [14, 5],
        [1, 9], [5, 9], [9, 9], [14, 9],
        [5, 14], [9, 14],
    ];
    private static $_tripleWord = [
        [0, 0], [7, 0], [15, 0],
        [0, 7], [15, 7],
        [0, 15], [7, 15], [15, 15],
    ];


    /**
     * Checks if the position appears in the list supplied
     * 
     * @param array $position An X, Y position
     * @param array $list     The list of positions to check
     * 
     * @return void
     */
    private static function _positionInList($position, $list)
    {
        foreach ($list as $comparePosition) {
            if (($comparePosition[0] === $position[0])
                && ($comparePosition[1] === $position[1])
            ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Fills the bag with all available tiles
     * 
     * @return void
     */
    public static function fillBag()
    {
        foreach (self::$_tiles as $tile => $tileCount) {
            for ($i = 0; $i < $tileCount; $i++) {
                self::$_bag[] = $tile;
            }
        }
    }

    /**
     * Clears the board of all tiles
     * 
     * @return void
     */
    public static function clearBoard()
    {
        for ($x = 0; $x <= 15; $x++) {
            self::$_board[$x] = [];
            for ($y = 0; $y < 15; $y++) {
                self::$_board[$x][$y] = '';
            }
        }
    }

    /**
     * Draw a number of tiles from the bag
     * 
     * @param integer $amount The number of tiles to draw, defaults to 7
     * 
     * @return array
     */
    public static function draw($amount = 7)
    {
        $result = [];

        for ($i = 0; $i < $amount; $i++) {
            // If we have run out of tiles, we better break the loop!
            if (count(self::$_bag) <= 0) {
                break;
            }

            $tileSelected = rand(0, count(self::$_bag) - 1);
            $result[] = array_splice(self::$_bag, $tileSelected, 1)[0];
        }

        return $result;
    }

    /**
     * Returns the score for a placed letter tile, returns -1 if invalid
     * 
     * @param string $tile     The tile to be placed
     * @param array  $position X, Y coordinates of the board position
     * 
     * @return int
     */
    public static function letterScore($tile, $position)
    {
        if ((count($position) !== 2) || ($position[0] < 0) || ($position[0] > 15)
            || ($position[1] < 0) || ($position[1] > 15)
        ) {
            return -1;
        }

        // Is there already a tile at this position?
        $existingTile = self::$_board[$position[0]][$position[1]];
        if ($existingTile !== '') {
            return -1;
        }

        // Get the tile score
        $tileScore = 0;
        foreach (self::$_tileScores as $tileKey => $tileScores) {
            if ($tileKey === $tile) {
                $tileScore = $tileScores;
            }
        }

        // Check if we need to apply a multiplier

        // Check for double letter
        if (self::_positionInList($position, self::$_doubleLetter)) {
            $tileScore = $tileScore * 2;
        }

        // Check for triple letter
        if (self::_positionInList($position, self::$_tripleLetter)) {
            $tileScore = $tileScore * 3;
        }

        // Check for double word
        if (self::_positionInList($position, self::$_doubleWord)) {
            $tileScore = $tileScore * 2;
        }

        // Check for triple word
        if (self::_positionInList($position, self::$_tripleWord)) {
            $tileScore = $tileScore * 3;
        }

        // Place the tile and return the score
        self::$_board[$position[0]][$position[1]] = $tile;
        return $tileScore;
    }
}


// Run through a couple of tests
Scrabble::clearBoard();
Scrabble::fillBag();

$player1 =  Scrabble::draw();
print_r($player1);

// Place a random tile in the center of the board
$placedTiles = array_splice($player1, rand(0, 6), 1);
$placedScore = Scrabble::letterScore($placedTiles[0], [8, 7]);
print_r([$placedTiles[0], [8, 7], $placedScore]);

// Output our remaining tiles
print_r($player1);
