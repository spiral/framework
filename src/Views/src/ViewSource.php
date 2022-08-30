<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Views;

/**
 * Carries information about view.
 */
final class ViewSource
{
    /** @var string */
    private $filename;

    /** @var string */
    private $name;

    /** @var string */
    private $namespace;

    /** @var string|null */
    private $code;

    public function __construct(string $filename, string $namespace, string $name)
    {
        $this->filename = $filename;
        $this->namespace = $namespace;
        $this->name = $name;
    }

    /**
     * Template namespace.
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * Template name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Template filename.
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * Template code.
     */
    public function getCode(): string
    {
        return $this->code ?? file_get_contents($this->getFilename());
    }

    /**
     * Get source copy with redefined code.
     */
    public function withCode(string $code): ViewSource
    {
        $context = clone $this;
        $context->code = $code;

        return $context;
    }
}
