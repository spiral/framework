<?php
/**
 * Spiral Framework, SpiralScout LLC.
 *
 * @package   spiralFramework
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
define('SPIRAL_INITIAL_TIME', microtime(true));

/**
 * Error reporting.
 */
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', true);

/**
 * Composer.
 */
require dirname(__DIR__) . '/vendor/autoload.php';

\Spiral\Tests\MemoryCore::init(array(
    'root'        => __DIR__,
    'libraries'   => dirname(__DIR__) . '/vendor',
    'application' => __DIR__
))->setEnvironment('testing');