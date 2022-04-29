<?php

declare(strict_types=1);

namespace Spiral\Views\Loader;

final class ViewPath
{
    public function __construct(
        private readonly string $namespace,
        private readonly string $name,
        private readonly string $basename
    ) {
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
