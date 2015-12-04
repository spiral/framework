<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Models\Inspections;

use Spiral\Core\Component;
use Spiral\Models\Inspector;

/**
 * Representing information about one single field in data entity.
 */
class FieldInspection extends Component
{
    /**
     * Field name.
     *
     * @var string
     */
    private $name = '';

    /**
     * Field type.
     *
     * @var string
     */
    private $type = '';

    /**
     * Is field fillable.
     *
     * @var bool
     */
    private $fillable = true;

    /**
     * Is field hidden from public data.
     *
     * @var bool
     */
    private $hidden = false;

    /**
     * Field has filters or accessors.
     *
     * @var bool
     */
    private $filtered = false;

    /**
     * Field is validated.
     *
     * @var bool
     */
    private $validated = false;

    /**
     * @var Inspector
     */
    protected $inspector = null;

    /**
     * @param Inspector $inspector
     * @param string $name
     * @param string $type
     * @param bool $fillable
     * @param bool $hidden
     * @param bool $filtered
     * @param bool $validated
     */
    public function __construct(
        Inspector $inspector,
        $name,
        $type,
        $fillable,
        $hidden,
        $filtered,
        $validated
    )
    {
        $this->inspector = $inspector;

        $this->name = $name;
        $this->type = $type;
        $this->fillable = $fillable;
        $this->hidden = $hidden;
        $this->filtered = $filtered;
        $this->validated = $validated;
    }

    /**
     * Field name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Field type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * If fillable.
     *
     * @return boolean
     */
    public function isFillable()
    {
        return $this->fillable;
    }

    /**
     * If hidden.
     *
     * @return boolean
     */
    public function isHidden()
    {
        return $this->hidden;
    }

    /**
     * Is filtered.
     *
     * @return boolean
     */
    public function isFiltered()
    {
        return $this->filtered;
    }

    /**
     * Is validated.
     *
     * @return boolean
     */
    public function isValidated()
    {
        return $this->validated;
    }

    /**
     * Is blacklisted.
     *
     * @return boolean
     */
    public function isBlacklisted()
    {
        return $this->inspector->isBlacklisted($this->name) && !$this->hidden;
    }

    /**
     * Get field protection rank. Rank value depends on field options such as being fillable,
     * validated etc. Ranking value measures between 0 and 1.
     *
     * @return float
     */
    public function getRank()
    {
        $rank = 1;

        if (!$this->isHidden() && $this->isBlacklisted()) {
            //Potential blacklisted field
            $rank *= 0.75;
        }

        if (!$this->isFillable()) {
            return $rank;
        }

        if (!$this->isValidated()) {
            //Fillable but not validated
            $rank *= 0.5;
        }

        if (!$this->isFiltered()) {
            //Fillable but not filtered
            $rank *= 0.8;
        }

        return $rank;
    }
}