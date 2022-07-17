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
    private string $namespace;

    private string $name;

    private string $basename;

    public function __construct(string $namespace, string $name, string $basename)
    {
        $this->namespace = $namespace;
        $this->name = $name;
        $this->basename = $basename;
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getBasename(): string
    {
        return $this->basename;
    }
}
