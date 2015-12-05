<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Models;

use Spiral\Core\Component;
use Spiral\Models\Configs\InspectionsConfig;
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
     * @var InspectionsConfig
     */
    protected $config = null;

    /**
     * @var EntityInspection[]
     */
    protected $inspections = [];

    /**
     * @param InspectionsConfig  $config
     * @param ReflectionEntity[] $reflections Schemas to be inspected.
     */
    public function __construct(InspectionsConfig $config, array $reflections = [])
    {
        $this->config = $config;

        foreach ($reflections as $entity) {
            if ($entity->isAbstract()) {
                continue;
            }

            $name = $this->normalizeClass($entity->getName());
            $this->inspections[$name] = new EntityInspection($this, $entity);
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
        return in_array($name, $this->config->blacklistKeywords());
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
        $class = $this->normalizeClass($class);
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

    /**
     * @param string $class
     * @return string
     */
    private function normalizeClass($class)
    {
        return str_replace('/', '\\', strtolower($class));
    }
}
