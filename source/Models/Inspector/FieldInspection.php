<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Models\Inspector;

use Spiral\Core\Component;
use Psr\Log\LogLevel;

class FieldInspection extends Component
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
     * Pre-defined messages.
     *
     * @var array
     */
    protected $messages = [
        'blacklisted'   => 'Field is blacklisted but visible in publicFields().',
        'unsafe'        => 'Field is fillable but no validations or filters provided.',
        'noValidations' => 'Field is fillable but no validations provided (has filters).',
    ];

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

    /**
     * Get field safety level.
     *
     * @return int
     */
    public function safetyLevel()
    {
        if (!$this->isFillable())
        {
            return 5 - ($this->isBlacklisted() ? 2 : 0);
        }

        $level = 5;

        if (!$this->isFiltered())
        {
            $level--;

            if (!$this->isValidated())
            {
                /**
                 * Very bad situation.
                 */
                $level -= 3;
            }
        }
        elseif (!$this->isValidated())
        {
            $level -= 2;
        }

        if ($this->isBlacklisted())
        {
            $level--;
        }

        return max($level, 1);
    }

    /**
     * Get detailed explanations of detected problems.
     *
     * @return array
     */
    public function getWarnings()
    {
        $errors = [];
        if ($this->isBlacklisted())
        {
            $errors[] = [LogLevel::WARNING, $this->messages['blacklisted']];
        }

        if ($this->isFillable())
        {
            if (!$this->isFiltered() && !$this->isValidated())
            {
                $errors[] = [LogLevel::CRITICAL, $this->messages['unsafe']];
            }
            elseif (!$this->isValidated())
            {
                $errors[] = [LogLevel::WARNING, $this->messages['noValidations']];
            }
        }

        return $errors;
    }
}