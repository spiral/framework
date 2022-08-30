<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\DataGrid\Bootloader;

use Psr\Container\ContainerInterface;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Config\Patch\Append;
use Spiral\DataGrid\GridFactoryInterface;
use Cycle\Database\DatabaseInterface;
use Spiral\DataGrid\Compiler;
use Spiral\DataGrid\Config\GridConfig;
use Spiral\DataGrid\Grid;
use Spiral\DataGrid\GridFactory;
use Spiral\DataGrid\GridInput;
use Spiral\DataGrid\GridInterface;
use Spiral\DataGrid\InputInterface;
use Spiral\DataGrid\Response\GridResponse;
use Spiral\DataGrid\Response\GridResponseInterface;
use Spiral\DataGrid\Writer\BetweenWriter;
use Spiral\DataGrid\Writer\QueryWriter;

final class GridBootloader extends Bootloader
{
    protected const SINGLETONS = [
        InputInterface::class        => GridInput::class,
        GridInterface::class         => Grid::class,
        GridFactoryInterface::class  => GridFactory::class,
        GridFactory::class           => GridFactory::class, // Deprecated behaviour
        Compiler::class              => [self::class, 'compiler'],
        GridResponseInterface::class => GridResponse::class,
    ];

    /** @var ConfiguratorInterface */
    private $config;

    /**
     * @param ConfiguratorInterface $config
     */
    public function __construct(ConfiguratorInterface $config)
    {
        $this->config = $config;
    }

    /**
     * Inits default config.
     */
    public function boot(): void
    {
        $this->config->setDefaults(GridConfig::CONFIG, [
            'writers' => [QueryWriter::class, BetweenWriter::class],
        ]);
    }

    /**
     * @param ContainerInterface $container
     * @param Compiler           $compiler
     * @param GridConfig         $config
     * @return Compiler
     */
    public function compiler(ContainerInterface $container, Compiler $compiler, GridConfig $config): Compiler
    {
        if ($container->has(DatabaseInterface::class)) {
            foreach ($config->getWriters() as $writer) {
                $compiler->addWriter($container->get($writer));
            }
        }

        return $compiler;
    }

    /**
     * @psalm-param class-string $writer
     */
    public function addWriter(string $writer): void
    {
        $this->config->modify(GridConfig::CONFIG, new Append('writers', null, $writer));
    }
}
