<?php

declare(strict_types=1);

namespace Spiral\Views;

/**
 * Carries information about view.
 */
final class ViewSource
{
    private ?string $code = null;

    public function __construct(
        private readonly string $filename,
        private readonly string $namespace,
        private readonly string $name
    ) {
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
        return $this->code ?? \file_get_contents($this->getFilename());
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
