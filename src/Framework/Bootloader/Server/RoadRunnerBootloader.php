<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Bootloader\Server;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\EnvironmentInterface as GlobalEnvironmentInterface;
use Spiral\Core\Container;
use Spiral\Goridge\RPC\RPC;
use Spiral\Goridge\RPC\RPCInterface;
use Spiral\Http\Diactoros\ServerRequestFactory;
use Spiral\Http\Diactoros\StreamFactory;
use Spiral\Http\Diactoros\UploadedFileFactory;
use Spiral\RoadRunner\Environment;
use Spiral\RoadRunner\EnvironmentInterface;
use Spiral\RoadRunner\Http\PSR7Worker;
use Spiral\RoadRunner\Http\PSR7WorkerInterface;
use Spiral\RoadRunner\Worker;
use Spiral\RoadRunner\WorkerInterface;

class RoadRunnerBootloader extends Bootloader
{
    /**
     * @param Container $container
     */
    public function boot(Container $container)
    {
        //
        // Register RoadRunner Environment
        //
        $registrar = static function (GlobalEnvironmentInterface $env): EnvironmentInterface {
            return new Environment($env->getAll());
        };

        $container->bindSingleton(EnvironmentInterface::class, $registrar);
        $container->bindSingleton(Environment::class, $registrar);

        //
        // Register RPC
        //
        $registrar = static function (EnvironmentInterface $env): RPCInterface {
            return RPC::create($env->getRPCAddress());
        };

        $container->bindSingleton(RPCInterface::class, $registrar);
        $container->bindSingleton(RPC::class, $registrar);

        //
        // Register Worker
        //
        $registrar = static function (EnvironmentInterface $env): WorkerInterface {
            return Worker::createFromEnvironment($env);
        };

        $container->bindSingleton(WorkerInterface::class, $registrar);
        $container->bindSingleton(Worker::class, $registrar);

        //
        // Register PSR Worker
        //
        $registrar = static function (
            WorkerInterface $worker,
            ServerRequestFactory $requests,
            StreamFactory $streams,
            UploadedFileFactory $uploads
        ): PSR7WorkerInterface {
            return new PSR7Worker(
                $worker,
                $requests,
                $streams,
                $uploads
            );
        };

        $container->bindSingleton(PSR7WorkerInterface::class, $registrar);
        $container->bindSingleton(PSR7Worker::class, $registrar);
    }
}
