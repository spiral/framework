<?php

declare(strict_types=1);

namespace Spiral\Filters;

use Spiral\Auth\AuthContextInterface;
use Spiral\Filters\Exception\AuthorizationException;
use Spiral\Filters\Exception\FilterException;
use Spiral\Filters\Exception\ValidationException;
use Spiral\Models\AbstractEntity;
use Spiral\Models\ModelSchema;
use Spiral\Validation\ValidationInterface;
use Spiral\Validation\ValidatorInterface;

/**
 * Filter is data entity which uses input manager to populate its fields, model can
 * perform input filtering, value routing (query, data, files) and validation.
 *
 * Attention, you can not inherit one request from another at this moment. You can use generic
 * validation rules for your input fields.
 *
 * Please do not request instance without using container, constructor signature might change over
 * time (or another request filter class can be created with inheritance and composition support).
 */
abstract class Filter extends AbstractEntity implements
    FilterInterface,
                                                        ShouldBeValidated,
                                                        ShouldBeAuthorized
{
    protected ?ValidationInterface $validation = null;
    private ?ErrorMapper $errorMapper = null;

    /**
     * The core of any filter object is schema; ce:origin or field => source. The source is the subset of data
     * from user input. In the HTTP scope, the sources can be cookie, data, query, input (data+query), header, file,
     * server. The origin is the name of the external field (dot notation is supported).
     *
     * Example schema definition:this method defines mapping between fields and values provided by
     * input. Every key pair is defined as field => sour
     *
     * return [
     *       // identical to "data:name"
     *      'name'   => 'post:name',
     *
     *       // field name will be used as search criteria in a query ("query:field")
     *      'field'  => 'query',
     *
     *       // Yep, that's too
     *      'file'   => 'file:images.preview',
     *
     *       // Alias for InputManager->isSecure(),
     *      'secure' => 'isSecure'
     *
     *       // Iterate over file:uploads array with model UploadFilter and isolate it in uploads.*
     *      'uploads' => [UploadFilter::class, "uploads.*", "file:upload"],
     *
     *      // Nested model associated with address subset of data
     *      'address' => AddressRequest::class,
     *
     *       // Identical to previous definition
     *      'address' => [AddressRequest::class, "address"]
     * ];
     *
     * You can declare as source (query, file, post and e.t.c) as source plus origin name (file:files.0).
     * Available sources: uri, path, method, isSecure, isAjax, isJsonExpected, remoteAddress.
     * Plus named sources (bags): header, data, post, query, cookie, file, server, attribute.
     */
    abstract public function mappingSchema(): array;

    public function isAuthorized(?AuthContextInterface $auth): bool
    {
        return true;
    }

    /**
     * Handle a failed authorization attempt.
     *
     * @throws AuthorizationException
     */
    public function failedAuthorization(): void
    {
        throw new AuthorizationException();
    }

    public function filteredData(): array
    {
        return $this->toArray();
    }

    public function withErrorMapper(ErrorMapper $errorMapper): void
    {
        $this->errorMapper = $errorMapper;
    }

    public function withValidation(
        ValidationInterface $validation
    ): static {
        $this->validation = $validation;

        return $this;
    }

    public function validate(): ValidatorInterface
    {
        if (!$this->validation) {
            throw new FilterException('Validation is not set.');
        }

        return $this->createValidator();
    }

    /**
     * Handle a failed validation attempt.
     */
    public function failedValidation(ValidatorInterface $validator): void
    {
        throw new ValidationException(
            $this->errorMapper
                ? $this->errorMapper->mapErrors($validator->getErrors())
                : $validator->getErrors(),
            $validator->getContext()
        );
    }

    /**
     * The fields that are mass assignable.
     *
     * @return string[]|string
     */
    protected function fillableFields(): string|array
    {
        return '*';
    }

    /**
     * The fields that aren't mass assignable.
     *
     * @return string[]|string
     */
    protected function securedFields(): string|array
    {
        return [];
    }

    /**
     * @return array<non-empty-string, non-empty-string>
     */
    protected function getters(): array
    {
        return [];
    }

    /**
     * Setters to typecast the incoming value before passing it to the validator.
     * The Filter will assign null to the value in case of typecast error.
     *
     * @return array<non-empty-string, non-empty-string>
     */
    protected function setters(): array
    {
        return [];
    }

    /**
     * @return array<non-empty-string, non-empty-string>
     */
    protected function accessors(): array
    {
        return [];
    }

    /**
     * Create the default validator instance.
     */
    protected function createValidator(): ValidatorInterface
    {
        $context = method_exists($this, 'getValidationContext')
            ? $this->getValidationContext()
            : null;

        return $this->validation->validate($this, $this->validationRules(), $context);
    }

    protected function isFillable(string $field): bool
    {
        $fillable = $this->fillableFields();
        $secured = $this->securedFields();

        return match (true) {
            !empty($fillable) && $fillable === '*' => true,
            !empty($fillable) => \in_array($field, $fillable, true),
            !empty($secured) && $secured === '*' => false,
            default => !\in_array($field, $secured, true)
        };
    }

    protected function getMutator(string $field, string $type): mixed
    {
        return match ($type) {
            ModelSchema::MUTATOR_GETTER => $this->getters()[$field] ?? null,
            ModelSchema::MUTATOR_SETTER => $this->setters()[$field] ?? null,
            ModelSchema::MUTATOR_ACCESSOR => $this->accessors()[$field] ?? null,
            default => null
        };
    }
}
