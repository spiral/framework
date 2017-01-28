<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Validation\Checkers;

use Spiral\Core\Container\SingletonInterface;
use Spiral\Validation\Checkers\Traits\NotEmptyTrait;
use Spiral\Validation\Prototypes\AbstractChecker;
use Spiral\Validation\Validator;

/**
 * Validations based dependencies between fields. External values checked using notEmpty method.
 *
 * See tests.
 */
class RequiredChecker extends AbstractChecker implements SingletonInterface
{
    use NotEmptyTrait;

    /**
     * {@inheritdoc}
     */
    const MESSAGES = [
        'notEmpty'   => '[[This value is required.]]',
        'with'       => '[[This value is required.]]',
        'withAll'    => '[[This value is required.]]',
        'without'    => '[[This value is required.]]',
        'withoutAll' => '[[This value is required.]]',
    ];

    /**
     * Check if field not empty but only if any of listed fields presented or not empty.
     * Also: requiredWhenAnyExternalSet
     *
     * @param mixed        $value
     * @param array|string $with
     * @param bool         $asString Automatically trim external values before non empty
     *                               comparision.
     *
     * @return bool|int
     */
    public function with($value, $with, bool $asString = true)
    {
        if ($this->notEmpty($value, $asString)) {
            return true;
        }

        $with = (array)$with;
        foreach ($with as $field) {
            if (!$this->isEmpty($field, $asString)) {
                //External field presented, BUT value must not be empty!
                return false;
            }
        }

        //Value is empty, but no external fields are set = value not required
        return Validator::STOP_VALIDATION;
    }

    /**
     * Check if field not empty but only if all of listed fields presented and not empty.
     * Also: requiredWhenAllExternalSet
     *
     * @param mixed $value
     * @param array $withAll
     * @param bool  $asString Automatically trim external values before non empty comparision.
     *
     * @return bool|int
     */
    public function withAll($value, $withAll, bool $asString = true)
    {
        if ($this->notEmpty($value, $asString)) {
            return true;
        }

        $withAll = (array)$withAll;
        foreach ($withAll as $field) {
            if ($this->isEmpty($field, $asString)) {
                //External field is missing, value becomes non required
                return Validator::STOP_VALIDATION;
            }
        }

        //Value is empty but all external field not empty
        return false;
    }

    /**
     * Check if field not empty but only if one of listed fields missing or empty.
     * Also: requiredWhenNoExternalsSet
     *
     * @param mixed        $value
     * @param array|string $without
     * @param bool         $asString Automatically trim external values before non empty
     *                               comparision.
     *
     * @return bool|int
     */
    public function without($value, $without, bool $asString = true)
    {
        if ($this->notEmpty($value, $asString)) {
            return true;
        }

        $without = (array)$without;
        foreach ($without as $field) {
            if (!$this->isEmpty($field, $asString)) {
                //External field set, field becomes non required
                return Validator::STOP_VALIDATION;
            }
        }

        //Value is empty and no external fields are set
        return false;
    }

    /**
     * Check if field not empty but only if all of listed fields missing or empty.
     * Also: requiredWhenAllExternalNotSet
     *
     * @param mixed        $value
     * @param array|string $withoutAll
     * @param bool         $asString Automatically trim external values before non empty
     *                               comparision.
     *
     * @return bool|int
     */
    public function withoutAll($value, $withoutAll, bool $asString = true)
    {
        if ($this->notEmpty($value, $asString)) {
            return true;
        }

        $withoutAll = (array)$withoutAll;

        $allNotSet = true;
        foreach ($withoutAll as $field) {
            $allNotSet = $allNotSet && $this->isEmpty($field, $asString);
        }

        if ($allNotSet) {
            //No external values set, value not required
            return false;
        }

        return Validator::STOP_VALIDATION;
    }

    /**
     * Check if external validation value is empty.
     *
     * @param string $field
     * @param bool   $string
     *
     * @return bool
     */
    private function isEmpty(string $field, bool $string)
    {
        return !$this->notEmpty($this->getValidator()->getValue($field, null), $string);
    }
}
