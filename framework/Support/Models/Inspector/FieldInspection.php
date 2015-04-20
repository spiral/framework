<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Support\Models\Inspector;

class FieldInspection
{
    /**
     * Field name.
     *
     * @var string
     */
    protected $name = '';

    /**
     * Field type.
     *
     * @var string
     */
    protected $type = '';

    /**
     * Is field fillable.
     *
     * @var bool
     */
    protected $fillable = true;

    /**
     * Is field hidden from public data.
     *
     * @var bool
     */
    protected $hidden = false;

    /**
     * Field has filters or accessors.
     *
     * @var bool
     */
    protected $filtered = false;

    /**
     * Field is validated.
     *
     * @var bool
     */
    protected $validated = false;

    /**
     * Field is blacklisted and has to be hidden.
     *
     * @var bool
     */
    protected $blacklisted = false;

    /**
     * New field inspection.
     *
     * @param string $name
     * @param string $type
     * @param bool   $fillable
     * @param bool   $hidden
     * @param bool   $filtered
     * @param bool   $validated
     * @param bool   $blacklisted
     */
    public function __construct($name, $type, $fillable, $hidden, $filtered, $validated, $blacklisted)
    {
        $this->name = $name;
        $this->type = $type;
        $this->fillable = $fillable;
        $this->hidden = $hidden;
        $this->filtered = $filtered;
        $this->validated = $validated;
        $this->blacklisted = $blacklisted;
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
     * Us blacklisted.
     *
     * @return boolean
     */
    public function isBlacklisted()
    {
        return $this->blacklisted && !$this->hidden;
    }
}