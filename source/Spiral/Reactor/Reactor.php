<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Reactor;

use Doctrine\Common\Inflector\Inflector;
use Spiral\Core\ConfiguratorInterface;
use Spiral\Core\ContainerInterface;
use Spiral\Core\Singleton;
use Spiral\Core\Traits\ConfigurableTrait;
use Spiral\Reactor\Exceptions\ReactorException;

/**
 * Only holds configurations for class generators.
 */
class Reactor extends Singleton
{
    /**
     * Configuration located in "reactor" section.
     */
    use ConfigurableTrait;

    /**
     * Declares to Spiral IoC that component instance should be treated as singleton.
     */
    const SINGLETON = self::class;

    /**
     * Configuration section.
     */
    const CONFIG = 'reactor';

    /**
     * @invisible
     * @var ContainerInterface
     */
    protected $container = null;

    /**
     * @param ConfiguratorInterface $configurator
     * @param ContainerInterface    $container
     */
    public function __construct(ConfiguratorInterface $configurator, ContainerInterface $container)
    {
        $this->config = $configurator->getConfig(static::CONFIG);
        $this->container = $container;
    }

    /**
     * Find full class name using it's configured type and short name. Reaction will automatically
     * add namespace and postfix. Method will return null if class can not be found.
     *
     * @param string $type
     * @param string $name
     * @return string|null
     * @throws ReactorException
     */
    public function findClass($type, $name)
    {
        if (!isset($this->config['generators'][$type])) {
            throw new ReactorException("Undefined class type '{$type}'.");
        }

        $definition = $this->config['generators'][$type];

        //TODO: Issue with class name including slashes to be converted into namespace
        $class = $definition['namespace'] . '\\' . Inflector::classify($name) . $definition['postfix'];

        if (!class_exists($class)) {
            return null;
        }

        return $class;
    }
}