<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Support\Validation\Checkers;

use Spiral\Support\Validation\Checker;
use Spiral\Support\Validation\Validator;

class RequiredChecker extends Checker
{
    /**
     * Set of default error messages associated with their check methods organized by method name.
     * Will be returned by the checker to replace the default validator message. Can have placeholders
     * for interpolation.
     *
     * @var array
     */
    protected $messages = [
        "with"       => "[[Field '{field}' should not be empty.]]",
        "withAll"    => "[[Field '{field}' should not be empty.]]",
        "without"    => "[[Field '{field}' should not be empty.]]",
        "withoutAll" => "[[Field '{field}' should not be empty.]]",
    ];

    /**
     * Check if field not empty but only if any of listed fields presented or not empty.
     *
     * @param mixed $value Value to be validated.
     * @param array $with  Related field.
     * @return bool
     */
    public function with($value, array $with)
    {
        if (!empty($value))
        {
            return true;
        }

        foreach ($with as $field)
        {
            if ($this->validator->getField($field))
            {
                //Some value presented
                return false;
            }
        }

        return Validator::STOP_VALIDATION;
    }

    /**
     * Check if field not empty but only if all of listed fields presented and not empty.
     *
     * @param mixed $value Value to be validated.
     * @param array $with  Related field.
     * @return bool
     */
    public function withAll($value, array $with)
    {
        if (!empty($value))
        {
            return true;
        }

        foreach ($with as $field)
        {
            if (!$this->validator->getField($field))
            {
                return Validator::STOP_VALIDATION;
            }
        }

        return false;
    }

    /**
     * Check if field not empty but only if any of listed fields missing or empty.
     *
     * @param mixed $value   Value to be validated.
     * @param array $without Related field.
     * @return bool
     */
    public function without($value, array $without)
    {
        if (!empty($value))
        {
            return true;
        }

        foreach ($without as $field)
        {
            if (!$this->validator->getField($field))
            {
                //Some value presented
                return false;
            }
        }

        return Validator::STOP_VALIDATION;
    }

    /**
     * Check if field not empty but only if all of listed fields missing or empty.
     *
     * @param mixed $value   Value to be validated.
     * @param array $without Related field.
     * @return bool
     */
    public function withoutAll($value, array $without)
    {
        if (!empty($value))
        {
            return true;
        }

        foreach ($without as $field)
        {
            if ($this->validator->getField($field))
            {
                return Validator::STOP_VALIDATION;
            }
        }

        return false;
    }
}