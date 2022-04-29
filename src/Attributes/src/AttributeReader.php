<?php

declare(strict_types=1);

namespace Spiral\Attributes;

use Spiral\Attributes\Internal\Decorator;
use Spiral\Attributes\Internal\Instantiator\InstantiatorInterface;
use Spiral\Attributes\Internal\NativeAttributeReader;

final class AttributeReader extends Decorator
{
    public function __construct(InstantiatorInterface $instantiator = null)
    {
        parent::__construct(new NativeAttributeReader($instantiator));
    }
}
