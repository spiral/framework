<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Views;

/**
 * Carries information about view.
 */
final class ViewSource
{
    /**
     * @var string
     */
    private $filename;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $namespace;

    /**
     * Cached or manually set source.
     *
     * @var string|null
     */
    private $code = null;

    /**
     * @param string $filename
     * @param string $name
     * @param string $namespace
     */
    public function __construct(string $filename, string $name, string $namespace)
    {
        $this->filename = $filename;
        $this->name = $name;
        $this->namespace = $namespace;
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
     * Template namespace.
     *
     * @return string
     */
    public function getNamespace(): string
    {
        return $this->namespace;
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
        //Expecting local stream
        return $this->code ?? file_get_contents($this->getFilename());
    }

    /**
     * Get source copy with redefined code.
     *
     * @param string $code
     *
     * @return \Spiral\Views\ViewSource
     */
    public function withCode(string $code): ViewSource
    {
        $context = clone $this;
        $context->code = $code;

        return $context;
    }

    /**
     * Must return true if template have not changed since given timestamp.
     *
     * @param int $timestamp
     *
     * @return bool
     */
    public function isFresh(int $timestamp): bool
    {
        return filemtime($this->getFilename()) < $timestamp;
    }
}