<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */
namespace Spiral\Validation;

use Spiral\Core\Exceptions\ScopeException;
use Spiral\Core\Traits\SaturateTrait;
use Spiral\Models\DataEntity;
use Spiral\Translator\Traits\TranslatorTrait;
use Spiral\Translator\Translator;

/**
 * Provides ability to validate mocked data. Model provides ability to localize error messages.
 */
class ValidatesEntity extends DataEntity
{
    use SaturateTrait, TranslatorTrait;

    /**
     * Validation rules compatible with ValidatorInterface.
     */
    const VALIDATES = [];

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @param array              $fields
     * @param ValidatorInterface $validator
     *
     * @throws ScopeException
     */
    public function __construct(array $fields, ValidatorInterface $validator = null)
    {
        parent::__construct($fields);
        $this->validator = $this->saturate($validator, ValidatorInterface::class);
    }

    /**
     * Get entity validator.
     *
     * @return ValidatorInterface
     */
    public function getValidator(): ValidatorInterface
    {
        return $this->validator;
    }

    /**
     * Change associated entity validator.
     *
     * @param ValidatorInterface $validator
     *
     * @return ValidatesEntity
     */
    public function setValidator(ValidatorInterface $validator): ValidatesEntity
    {
        $this->validator = $validator;

        return $this;
    }

    /**
     * Check if entity data is valid.
     *
     * @param string $field
     *
     * @return bool
     */
    public function isValid(string $field = null): bool
    {
        return !$this->hasErrors($field);
    }

    /**
     * Check if any of data fields has errors.
     *
     * @param string $field
     *
     * @return bool
     */
    public function hasErrors(string $field = null): bool
    {
        if (empty($field)) {
            return !empty($this->getErrors());
        }

        //Looking for specific error
        return !empty($this->getErrors()[$field]);
    }

    /**
     * Get validation errors.
     *
     * @return array
     */
    public function getErrors(): array
    {
        //Configuring validator (validation will be performed on next step)
        $this->validate();

        /*
         * Primary model errors.
         */
        $errors = $this->validator->getErrors();
        foreach ($errors as &$error) {
            if (is_string($error) && Translator::isMessage($error)) {
                //Localizing error message
                $error = $this->say($error);
            }

            unset($error);
        }

        /*
         * Nested models validation (if any).
         */
        foreach ($this->getFields(false) as $index => $value) {
            if (isset($errors[$index])) {
                //Invalid on parent level
                continue;
            }

            if ($value instanceof ValidatesEntity) {
                if (!$value->isValid()) {
                    $errors[$index] = $value->getErrors();
                }
                continue;
            }

            //We also support array of nested entities for validation
            if (is_array($value) || $value instanceof \Traversable) {
                foreach ($value as $nIndex => $nValue) {
                    if ($nValue instanceof ValidatesEntity && !$nValue->isValid()) {
                        $errors[$index][$nIndex] = $nValue->getErrors();
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * Perform data validation.
     */
    protected function validate()
    {
        //Configuring validator
        $this->validator->setRules(static::VALIDATES)->setData($this->getFields());

        //Dropping all validation errors set by user
        $this->validator->flushRegistered();
    }
}