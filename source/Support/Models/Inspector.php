<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Support\Models;

use Spiral\Core\Component;
use Spiral\Support\Models\Inspector\InspectorException;
use Spiral\Support\Models\Inspector\ModelInspection;
use Spiral\Support\Models\Schemas\ModelSchema;

class Inspector extends Component
{
    /**
     * Model inspections.
     *
     * @var ModelInspection[]
     */
    protected $inspections = array();

    /**
     * List of blacklisted keywords indicates that field has to be hidden from publicFields() result.
     *
     * @var array
     */
    protected $blacklist = array(
        'password',
        'hidden',
        'private',
        'protected',
        'email',
        'card',
        'internal'
    );

    /**
     * New DataEntities inspector. Inspector will check secured and hidden fields, validations and
     * filters to ensure that client will always see what he has to see.
     *
     * @param ModelSchema[] $schemas
     */
    public function __construct(array $schemas)
    {
        foreach ($schemas as $schema)
        {
            if (!$schema->isAbstract())
            {
                $this->inspections[$schema->getClass()] = new ModelInspection($schema);
            }
        }
    }

    /**
     * Get all model inspections.
     *
     * @return Inspector\ModelInspection[]
     */
    public function getInspections()
    {
        return $this->inspections;
    }

    /**
     * Get model inspection or return null.
     *
     * @param string $class
     * @return ModelInspection
     * @throws InspectorException
     */
    public function getInspection($class)
    {
        if (!isset($this->inspections[$class]))
        {
            throw new InspectorException("Unable to get inspection for '{$class}' no such model.");
        }

        return $this->inspections[$class];
    }

    /**
     * Total analyzed models.
     *
     * @return int
     */
    public function countModels()
    {
        return count($this->inspections);
    }

    /**
     * Get average safety level.
     *
     * @return float
     */
    public function getSafetyLevel()
    {
        $safetyLevel = 0;
        foreach ($this->inspections as $inspection)
        {
            $safetyLevel += $inspection->safetyLevel();
        }

        if (!$this->countModels())
        {
            return 5;
        }

        return $safetyLevel / $this->countModels();
    }

    /**
     * Percent of passed/total fields.
     *
     * @param int $level
     * @return float
     */
    public function getProtectionRate($level = 4)
    {
        $totalFields = 0;
        $passedFields = 0;
        foreach ($this->inspections as $inspection)
        {
            $totalFields += $inspection->countFields();
            $passedFields += $inspection->countPassed($level);
        }

        if ($totalFields == 0)
        {
            return 1;
        }

        return $passedFields / $totalFields;
    }

    /**
     * Run inspections against model fields, validations, hidden/secure/fillable rules, filters
     * and etc.
     */
    public function inspect()
    {
        foreach ($this->inspections as $inspection)
        {
            $inspection->inspect($this->blacklist);
        }
    }
}