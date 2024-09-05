<?php

declare(strict_types=1);

namespace Spiral\Core\Exception\Container;

/**
 * Recursion can occur due to improper container configuration or
 * an unplanned exit from the scope by the execution thread.
 */
class RecursiveProxyException extends ContainerException
{
}
