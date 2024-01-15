<?php

declare(strict_types=1);

namespace Spiral\Core\Container;

use Spiral\Core\Attribute\Singleton;

/**
 * Class treated as singleton will only be constructed once in spiral IoC.
 * @deprecated Use {@see Singleton} attribute instead. Will be removed in v4.0.
 */
interface SingletonInterface
{
}
