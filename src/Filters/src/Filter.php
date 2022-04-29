<?php

declare(strict_types=1);

namespace Spiral\Filters;

use Spiral\Models\Exception\EntityExceptionInterface;
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

    private ?array $errors = null;
    private array $mappings = [];

    /**
     * Filter constructor.
     */
    public function __construct(
        array $data,
        array $schema,
        /** @internal */
        private ValidatorInterface $validator,
        private ErrorMapper $errorMapper
    ) {
        parent::__construct($data, $schema);

        $this->mappings = $schema[FilterProvider::MAPPING] ?? [];
    }

    public function __unset(string $offset): void
    {
        parent::__unset($offset);
        $this->reset();
    }

    /**
     * @return array<string, bool|array>
     *
     * @psalm-return array{valid: bool, fields: array, errors: array}
     */
    public function __debugInfo(): array
    {
        return [
            'valid'  => $this->isValid(),
            'fields' => $this->getFields(),
            'errors' => $this->getErrors(),
        ];
    }

    /**
     * Force re-validation.
     */
    public function reset(): void
    {
        $this->errors = null;
    }

    public function setField(string $name, mixed $value, bool $filter = true): self
    {
        parent::setField($name, $value, $filter);
        $this->reset();

        return $this;
    }

    public function isValid(): bool
    {
        return $this->getErrors() === [];
    }

    /**
     * Get all validation messages (including nested models).
     */
    public function getErrors(): array
    {
        if ($this->errors === null) {
            $this->errors = [];
            foreach ($this->validator->withData($this)->getErrors() as $field => $error) {
                if (\is_string($error) && Translator::isMessage($error)) {
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

    public function setContext(mixed $context): void
    {
        $this->validator = $this->validator->withContext($context);
        $this->reset();
    }

    public function getContext(): mixed
    {
        return $this->validator->getContext();
    }

    /**
     * Validate inner entities.
     */
    protected function validateNested(array $errors): array
    {
        foreach ($this->getFields(false) as $index => $value) {
            if (isset($errors[$index])) {
                //Invalid on parent level
                continue;
            }

            if ($value instanceof FilterInterface) {
                if ($this->isOptional($index) && !$this->hasBeenPassed($index)) {
                    continue;
                }

                if (!$value->isValid()) {
                    $errors[$index] = $value->getErrors();
                    continue;
                }
            }

            //Array of nested entities for validation
            if (\is_iterable($value)) {
                foreach ($value as $nIndex => $nValue) {
                    if ($nValue instanceof FilterInterface) {
                        if ($this->isOptional($nIndex) && !$this->hasBeenPassed($nIndex)) {
                            continue;
                        }

                        if (!$nValue->isValid()) {
                            $errors[$index][$nIndex] = $nValue->getErrors();
                        }
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * Returns {@see true} in case that children filter is optional
     * or {@see false} instead.
     */
    private function isOptional(int|string $field): bool
    {
        return $this->mappings[$field][FilterProvider::OPTIONAL] ?? false;
    }

    /**
     * Returns {@see true} in case that value has been passed.
     *
     * @throws EntityExceptionInterface
     */
    private function hasBeenPassed(int|string $field): bool
    {
        $value = $this->getField((string)$field);

        return match (true) {
            $value === null => false,
            $value instanceof FilterInterface => $value->getValue() !== [],
            default => true
        };
    }
}
