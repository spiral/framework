<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

use Spiral\Debug\Dumper;

if (!function_exists('dump')) {
    /**
     * Dump value.
     *
     * @param mixed $value Value to be dumped.
     * @param int $output
     *
     * @return string
     */
    function dump($value, int $output = Dumper::OUTPUT): ?string
    {
        if (!class_exists(\Spiral\Core\ContainerScope::class)) {
            return (new Dumper())->dump($value, $output);
        }

        $container = \Spiral\Core\ContainerScope::getContainer();
        if (is_null($container) || !$container->has(Dumper::class)) {
            $dumper = new Dumper();

            return $dumper->dump($value, $output);
        }

        return $container->get(Dumper::class)->dump($value, $output);
    }
}
