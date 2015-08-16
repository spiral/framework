<?php
/**
 * Spiral Framework, SpiralScout LLC.
 *
 * @package   spiralFramework
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
if (!defined('SPIRAL_INITIAL_TIME')) {
    define('SPIRAL_INITIAL_TIME', microtime(true));
}

iF (!defined('TEST_ROOT')) {
    define('TEST_ROOT', __DIR__);
}

/**
 * Error reporting.
 */
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', true);

//Composer
require dirname(__DIR__) . '/vendor/autoload.php';