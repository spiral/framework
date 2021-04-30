<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Attributes;

use Spiral\Attributes\Internal\Decorator;
use Spiral\Attributes\Internal\FallbackAttributeReader;
use Spiral\Attributes\Internal\Instantiator\InstantiatorInterface;
use Spiral\Attributes\Internal\NativeAttributeReader;

final class AttributeReader extends Decorator
{
    /**
     * @param InstantiatorInterface|null $instantiator
     */
    public function __construct(InstantiatorInterface $instantiator = null)
    {
        $reader = NativeAttributeReader::isAvailable()
            ? new NativeAttributeReader($instantiator)
            : new FallbackAttributeReader($instantiator)
        ;

        parent::__construct($reader);
    }
}
