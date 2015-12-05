<?php
/**
 * Spiral Framework, SpiralScout LLC.
 *
 * @package   spiralFramework
 * @author    Anton Titov (Wolfy-J)
 */
if (!defined('SPIRAL_INITIAL_TIME')) {
    define('SPIRAL_INITIAL_TIME', microtime(true));
}

iF (!defined('TEST_CACHE')) {
    define('TEST_CACHE', __DIR__ . '/cache/');
}

/**
 * Error reporting.
 */
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', true);

//Composer
require dirname(__DIR__) . '/vendor/autoload.php';