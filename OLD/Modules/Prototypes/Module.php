<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Modules\Prototypes;

use Spiral\Core\Component;
use Spiral\Core\ContainerInterface;
use Spiral\Modules\DefinitionInterface;
use Spiral\Modules\BootloadableInterface;

/**
 * Abstract module implementation, uses default implementations of Definition and Installer.
 * Definition information will be resolved using local composer.json file.
 *
 * Module location (root) directory will be resolved as composer.json location.
 *
 * Example:
 * Module class:    vendor/package/scr/Namespace/Class.php
 * Module location: vendor/package
 */
abstract class Bootloadable extends Component implements BootloadableInterface
{
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
        return Definition::fromComposer($container, static::class);
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
        return $container->construct(Installer::class, [
            'moduleDirectory' => $definition->getLocation()
        ]);
    }
}