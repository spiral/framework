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
    private $validator;

    /**
     * Indicates that entity have been validated.
     *
     * @var bool
     */
    private $validated = false;

    /**
     * Error cache holds last of errors unless entity value changed.
     *
     * @var array
     */
    private $errorsCache = [];

    /**
     * @param array              $data
     * @param ValidatorInterface $validator
     *
     * @throws ScopeException
     */
    public function __construct(array $data, ValidatorInterface $validator = null)
    {
        parent::__construct($data);

        //We always need validator instance, if not provided - resolve via global scope
        $this->setValidator($this->saturate($validator, ValidatorInterface::class));
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
     * @param bool               $setRules When true entity specific rules to be assigned.
     *
     * @return ValidatesEntity
     */
    public function setValidator(
        ValidatorInterface $validator,
        bool $setRules = true
    ): ValidatesEntity {
        $this->validator = $validator;

        if ($setRules) {
            $this->validator->setRules(static::VALIDATES);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setField(string $name, $value, bool $filter = true)
    {
        $this->validated = false;

        return parent::setField($name, $value, $filter);
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
        $errors = $this->errorsCache;

        if (!$this->validated) {
            $this->validate();

            $errors = $this->validator->getErrors();
            foreach ($errors as &$error) {
                if (is_string($error) && Translator::isMessage($error)) {
                    //Localizing error message
                    $error = $this->say($error);
                }

                unset($error);
            }

            $this->errorsCache = $errors;
            $this->validated = true;
        }

        return $this->validateInner($errors);
    }

    /**
     * Perform data validation. Method might include custom validations and errors
     */
    protected function validate()
    {
        //Configuring validator
        $this->validator->setData($this->getFields());

        //Drop all validation errors set by user
        $this->validator->flushRegistered();
    }

    /**
     * Validate inner entities.
     *
     * @param array $errors
     *
     * @return array
     */
    private function validateInner(array $errors): array
    {
        foreach ($this->getFields(false) as $index => $value) {
            if (isset($errors[$index])) {
                //Invalid on parent level
                continue;
            }

            if ($value instanceof ValidatesEntity) {
                if (!$value->isValid()) {
                    //Nested entities must deal with cache internally
                    $errors[$index] = $value->getErrors();
                }
                continue;
            }

            //Array of nested entities for validation
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
}
