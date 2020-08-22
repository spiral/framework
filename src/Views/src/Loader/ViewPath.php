<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Views\Loader;

final class ViewPath
{
    /** @var string */
    private $namespace;

    /** @var string */
    private $name;

    /** @var string */
    private $basename;

    /**
     * @param string $namespace
     * @param string $name
     * @param string $basename
     */
    public function __construct(string $namespace, string $name, string $basename)
    {
        $this->namespace = $namespace;
        $this->name = $name;
        $this->basename = $basename;
    }

    /**
     * @return string
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getBasename(): string
    {
        return $this->basename;
    }
}
