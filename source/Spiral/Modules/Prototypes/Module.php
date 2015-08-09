<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Modules\Prototypes;

use Spiral\Core\Component;
use Spiral\Core\ContainerInterface;
use Spiral\Modules\DefinitionInterface;
use Spiral\Modules\ModuleInterface;

/**
 * Abstract module implementation, uses default implementations of Definition and Installer.
 * Definition information will be resolved using local composer.json file.
 *
 * Define COMPOSER constant in your module with relative path to composer.json file.
 */
abstract class Module extends Component implements ModuleInterface
{
    /**
     * Location of composer.json relatively to module class location.
     */
    const COMPOSER = '../../composer.json';

    /**
     * {@inheritdoc}
     */
    public function bootstrap()
    {
        //Module specific
    }

    /**
     * {@inheritdoc}
     */
    public static function getDefinition(ContainerInterface $container)
    {
        return Definition::fromComposer($container, static::class, static::COMPOSER);
    }

    /**
     * {@inheritdoc}
     *
     * @return Installer
     */
    public static function getInstaller(
        ContainerInterface $container,
        DefinitionInterface $definition
    ) {
        //Let's create default Installer
        return $container->get(Installer::class, [
            'moduleDirectory' => $definition->getLocation()
        ]);
    }
}