<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Prototype\Annotation;

use Attribute;
use Doctrine\Common\Annotations\Annotation;
use Spiral\Attributes\NamedArgumentConstructor;

/**
 * @Annotation()
 * @Annotation\Target("CLASS")
 * @Annotation\Attributes({
 *      @Annotation\Attribute("property", type="string", required=true),
 * })
 * @NamedArgumentConstructor()
 */
#[Attribute(Attribute::TARGET_CLASS)]
#[NamedArgumentConstructor()]
class Prototyped
{
    /** @var string */
    public $property;

    public function __construct(string $property)
    {
        $this->property = $property;
    }
}
