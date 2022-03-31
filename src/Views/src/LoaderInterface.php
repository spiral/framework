<?php

declare(strict_types=1);

namespace Spiral\Views;

use Spiral\Views\Exception\LoaderException;
use Spiral\Views\Exception\PathException;

interface LoaderInterface
{
    // Namespace/viewName separator.
    public const NS_SEPARATOR = ':';

    // Default view namespace
    public const DEFAULT_NAMESPACE = 'default';

    /**
     * Lock loader to specific file extension.
     */
    public function withExtension(string $extension): LoaderInterface;

    public function getExtension(): ?string;

    /**
     * Check if given view path has associated view in a loader. Path might include namespace prefix or extension.
     *
     * @throws PathException
     */
    public function exists(string $path): bool;

    /**
     * Get source for given name. Path might include namespace prefix or extension.
     *
     * @throws LoaderException
     * @throws PathException
     */
    public function load(string $path): ViewSource;

    /**
     * Get names of all available views within this loader. Result will include namespace prefix and view name without
     * extension.
     *
     * @throws LoaderException
     */
    public function list(string $namespace = null): array;
}
