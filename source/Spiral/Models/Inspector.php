<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Models;

use Spiral\Core\Component;
use Spiral\Core\ConfiguratorInterface;
use Spiral\Core\Traits\ConfigurableTrait;
use Spiral\Models\Exceptions\InspectorException;
use Spiral\Models\Inspections\EntityInspection;
use Spiral\Models\Reflections\ReflectionEntity;

/**
 * Provides inspection of provided ReflectionEntities to check unhidden fields, missed validations
 * and etc.
 */
class Inspector extends Component
{
    /**
     * Every inspector has configuration.
     */
    use ConfigurableTrait;

    /**
     * Inspections configuration section.
     */
    const CONFIG = 'inspections';

    /**
     * @var EntityInspection[]
     */
    protected $inspections = [];

    /**
     * @param ConfiguratorInterface $configurator
     * @param ReflectionEntity[]    $entities Schemas to be inspected.
     */
    public function __construct(ConfiguratorInterface $configurator, array $entities = [])
    {
        $this->config = $configurator->getConfig(static::CONFIG);

        foreach ($entities as $entity) {
            if (!$entity->isAbstract()) {
                $this->inspections[$entity->getName()] = new EntityInspection($this, $entity);
            }
        }
    }

    /**
     * Check if given field name included into blacklist.
     *
     * @param string $name
     * @return bool
     */
    public function isBlacklisted($name)
    {
        return in_array($name, $this->config['blacklist']);
    }

    /**
     * @return EntityInspection[]
     */
    public function getInspections()
    {
        return $this->inspections;
    }

    /**
     * Get entity inspection by it's class.
     *
     * @param string $class
     * @return EntityInspection
     */
    public function inspection($class)
    {
        if (!isset($this->inspections[$class])) {
            throw new InspectorException("Undefined entity class '{$class}'.");
        }

        return $this->inspections[$class];
    }

    /**
     * @return int
     */
    public function countInspections()
    {
        return count($this->inspections);
    }

    /**
     * Get average inspections rank.
     *
     * @return float
     */
    public function getRank()
    {
        if (empty($this->inspections)) {
            //No entities, all good
            return 1;
        }

        $rank = 0;
        foreach ($this->inspections as $inspection) {
            $rank += $inspection->getRank();
        }

        return $rank / $this->countInspections();
    }
}
