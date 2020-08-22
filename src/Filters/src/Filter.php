<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Filters;

use Spiral\Models\SchematicEntity;
use Spiral\Translator\Traits\TranslatorTrait;
use Spiral\Translator\Translator;
use Spiral\Validation\ValidatorInterface;

/**
 * Filter is data entity which uses input manager to populate it's fields, model can
 * perform input filtering, value routing (query, data, files) and validation.
 *
 * Attention, you can not inherit one request from another at this moment. You can use generic
 * validation rules for your input fields.
 *
 * Please do not request instance without using container, constructor signature might change over
 * time (or another request filter class can be created with inheritance and composition support).
 *
 * Example schema definition:
 * const SCHEMA = [
 *       //identical to "data:name"
 *      'name'   => 'post:name',
 *
 *       //field name will used as search criteria in query ("query:field")
 *      'field'  => 'query',
 *
 *       //Yep, that's too
 *      'file'   => 'file:images.preview',
 *
 *       //Alias for InputManager->isSecure(),
 *      'secure' => 'isSecure'
 *
 *       //Iterate over file:uploads array with model UploadFilter and isolate it in uploads.*
 *      'uploads' => [UploadFilter::class, "uploads.*", "file:upload"],
 *
 *      //Nested model associated with address subset of data
 *      'address' => AddressRequest::class,
 *
 *       //Identical to previous definition
 *      'address' => [AddressRequest::class, "address"]
 * ];
 *
 * You can declare as source (query, file, post and etc) as source plus origin name (file:files.0).
 * Available sources: uri, path, method, isSecure, isAjax, isJsonExpected, remoteAddress.
 * Plus named sources (bags): header, data, post, query, cookie, file, server, attribute.
 */
abstract class Filter extends SchematicEntity implements FilterInterface
{
    use TranslatorTrait;

    // Defines request data mapping (input => request property)
    protected const SCHEMA    = [];
    protected const VALIDATES = [];
    protected const SETTERS   = [];
    protected const GETTERS   = [];

    /** @var array|null */
    private $errors;

    /** @var ValidatorInterface @internal */
    private $validator;

    /** @var ErrorMapper */
    private $errorMapper;

    /**
     * Filter constructor.
     *
     * @param array              $data
     * @param array              $schema
     * @param ValidatorInterface $validator
     * @param ErrorMapper        $errorMapper
     */
    public function __construct(
        array $data,
        array $schema,
        ValidatorInterface $validator,
        ErrorMapper $errorMapper
    ) {
        parent::__construct($data, $schema);
        $this->validator = $validator;
        $this->errorMapper = $errorMapper;
    }

    /**
     * {@inheritdoc}
     */
    public function __unset($offset): void
    {
        parent::__unset($offset);
        $this->reset();
    }

    /**
     * @return array
     */
    public function __debugInfo()
    {
        return [
            'valid'  => $this->isValid(),
            'fields' => $this->getFields(),
            'errors' => $this->getErrors()
        ];
    }

    /**
     * @inheritdoc
     */
    public function setContext($context): void
    {
        $this->validator = $this->validator->withContext($context);
        $this->reset();
    }

    /**
     * @inheritdoc
     */
    public function getContext()
    {
        return $this->validator->getContext();
    }

    /**
     * {@inheritdoc}
     */
    public function setField(string $name, $value, bool $filter = true): void
    {
        parent::setField($name, $value, $filter);
        $this->reset();
    }


    /**
     * @inheritdoc
     */
    public function isValid(): bool
    {
        return $this->getErrors() === [];
    }

    /**
     * Get all validation messages (including nested models).
     *
     * @return array
     */
    public function getErrors(): array
    {
        if ($this->errors === null) {
            $this->errors = [];
            foreach ($this->validator->withData($this)->getErrors() as $field => $error) {
                if (is_string($error) && Translator::isMessage($error)) {
                    // translate error message
                    $error = $this->say($error);
                }

                $this->errors[$field] = $error;
            }
        }

        $this->errors = $this->validateNested($this->errors);

        // make sure that each error point to proper input origin
        return $this->errorMapper->mapErrors($this->errors);
    }

    /**
     * Force re-validation.
     */
    public function reset(): void
    {
        $this->errors = null;
    }

    /**
     * Validate inner entities.
     *
     * @param array $errors
     *
     * @return array
     */
    protected function validateNested(array $errors): array
    {
        foreach ($this->getFields(false) as $index => $value) {
            if (isset($errors[$index])) {
                //Invalid on parent level
                continue;
            }

            if ($value instanceof FilterInterface && !$value->isValid()) {
                $errors[$index] = $value->getErrors();
                continue;
            }

            //Array of nested entities for validation
            if (is_iterable($value)) {
                foreach ($value as $nIndex => $nValue) {
                    if ($nValue instanceof FilterInterface && !$nValue->isValid()) {
                        $errors[$index][$nIndex] = $nValue->getErrors();
                    }
                }
            }
        }

        return $errors;
    }
}
