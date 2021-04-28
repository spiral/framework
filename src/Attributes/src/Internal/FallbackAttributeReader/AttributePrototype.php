<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Attributes\Internal\FallbackAttributeReader;

/**
 * @internal AttributePrototype is an internal library class, please do not use it in your code.
 * @psalm-internal Spiral\Attributes
 */
final class AttributePrototype
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var array
     */
    public $params;

    /**
     * @param string $attribute
     * @param array $arguments
     */
    public function __construct(string $attribute, array $arguments = [])
    {
        $this->name = $attribute;
        $this->params = $arguments;
    }
}
