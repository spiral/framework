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
    private $code = null;

    /**
     * @param string $filename
     * @param string $name
     * @param string $namespace
     */
    public function __construct(string $filename, string $namespace, string $name)
    {
        $this->filename = $filename;
        $this->namespace = $namespace;
        $this->name = $name;
    }

    /**
     * Template namespace.
     *
     * @return string
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * Template name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Template filename.
     *
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * Template code.
     *
     * @return string
     */
    public function getCode(): string
    {
        return $this->code ?? file_get_contents($this->getFilename());
    }

    /**
     * Get source copy with redefined code.
     *
     * @param string $code
     * @return self
     */
    public function withCode(string $code): ViewSource
    {
        $context = clone $this;
        $context->code = $code;

        return $context;
    }
}
