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
     * List of blacklisted keywords indicates that field has to be hidden from publicFields() response.
     * Keywords will not change protection rate but will raise warning for every occurrence.
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
     * filters to ensure that clients
     *
     * @param ModelSchema[] $schemas
     */
    public function __construct(array $schemas)
    {
        foreach ($schemas as $schema)
        {
            $this->inspections[] = new ModelInspection($schema);
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
     * Run inspections against model fields, validations, hidden/secure/fillable rules, filters
     * and etc.
     */
    public function inspect()
    {
    }
}