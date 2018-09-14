<?php
/**
 * Spiral Framework, SpiralScout LLC.
 *
 * @author    Anton Titov (Wolfy-J)
 */

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', true);

//Composer
require dirname(__DIR__) . '/vendor/autoload.php';

class RealCore extends \Spiral\Framework\Core
{
    protected function bootstrap()
    {
        //   echo $aa;

        // TODO: Implement bootstrap() method.
    }
}

$c = RealCore::init(['root' => __DIR__]);

echo memory_get_peak_usage(true) / 1024;