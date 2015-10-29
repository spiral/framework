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

        $class = $this->className($type, $name);
        if (!class_exists($class)) {
            return null;
        }

        return $class;
    }

    /**
     * Generate valid class name based on type and user name.
     *
     * @param string $type
     * @param string $name
     * @return string
     */
    private function className($type, $name)
    {
        $definition = $this->config['generators'][$type];

        $namespace = $definition['namespace'];
        if (strpos($name, '/') !== false || strpos($name, '\\') !== false) {
            $name = str_replace('/', '\\', $name);

            //Let's split and filter namespace
            $namespace = substr($name, 0, strrpos($name, '\\'));

            //We always have to include prefix (namespace)
            $chunks = [$definition['namespace']];
            foreach (explode('\\', $namespace) as $chunk) {
                $chunks[] = ucfirst($chunk);
            }

            $namespace = join('\\', $chunks);
            $name = substr($name, strrpos($name, '\\') + 1);
        }

        return $namespace . '\\' . Inflector::classify($name) . $definition['postfix'];
    }
}