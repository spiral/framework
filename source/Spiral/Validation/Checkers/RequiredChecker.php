<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Validation\Checkers;

use Spiral\Core\Container\SingletonInterface;
use Spiral\Validation\Prototypes\AbstractChecker;
use Spiral\Validation\Validator;

/**
 * Validations based dependencies between fields.
 */
class RequiredChecker extends AbstractChecker implements SingletonInterface
{
    /**
     * {@inheritdoc}
     */
    const MESSAGES = [
        'with'       => '[[This value is required.]]',
        'withAll'    => '[[This value is required.]]',
        'without'    => '[[This value is required.]]',
        'withoutAll' => '[[This value is required.]]',
    ];

    /**
     * Check if field not empty but only if any of listed fields presented or not empty.
     *
     * @param mixed $value
     * @param array $with
     *
     * @return bool|int
     */
    public function with($value, array $with)
    {
        if (!empty($value)) {
            return true;
        }

        foreach ($with as $field) {
            if ($this->getValidator()->getValue($field)) {
                //Some value presented
                return false;
            }
        }

        return Validator::STOP_VALIDATION;
    }

    /**
     * Check if field not empty but only if all of listed fields presented and not empty.
     *
     * @param mixed $value
     * @param array $with
     *
     * @return bool|int
     */
    public function withAll($value, array $with)
    {
        if (!empty($value)) {
            return true;
        }

        foreach ($with as $field) {
            if (!$this->getValidator()->getValue($field)) {
                return Validator::STOP_VALIDATION;
            }
        }

        return false;
    }

    /**
     * Check if field not empty but only if one of listed fields missing or empty.
     *
     * @param mixed $value
     * @param array $without
     *
     * @return bool|int
     */
    public function without($value, array $without)
    {
        if (!empty($value)) {
            return true;
        }

        foreach ($without as $field) {
            if (empty($this->getValidator()->getValue($field))) {
                //Some value presented
                return false;
            }
        }

        return Validator::STOP_VALIDATION;
    }

    /**
     * Check if field not empty but only if all of listed fields missing or empty.
     *
     * @param mixed $value
     * @param array $without
     *
     * @return bool|int
     */
    public function withoutAll($value, array $without)
    {
        if (!empty($value)) {
            return true;
        }

        foreach ($without as $field) {
            if ($this->getValidator()->getValue($field)) {
                return Validator::STOP_VALIDATION;
            }
        }

        return false;
    }
}
