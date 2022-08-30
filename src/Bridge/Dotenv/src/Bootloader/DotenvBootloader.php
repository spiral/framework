<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\DotEnv\Bootloader;

use Dotenv\Dotenv;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\DirectoriesInterface;
use Spiral\Boot\EnvironmentInterface;

final class DotenvBootloader extends Bootloader
{
    /**
     * @param DirectoriesInterface $dirs
     * @param EnvironmentInterface $env
     */
    public function boot(DirectoriesInterface $dirs, EnvironmentInterface $env)
    {
        $dotenvPath = $env->get('DOTENV_PATH', $dirs->get('root') . '.env');

        if (!file_exists($dotenvPath)) {
            return;
        }

        $path = dirname($dotenvPath);
        $file = basename($dotenvPath);

        foreach (Dotenv::createImmutable($path, $file)->load() as $key => $value) {
            $env->set($key, $value);
        }
    }
}
