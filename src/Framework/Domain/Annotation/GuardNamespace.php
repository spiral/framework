<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Domain\Annotation;

use Attribute;
use Doctrine\Common\Annotations\Annotation;
use Spiral\Attributes\NamedArgumentConstructor;

/**
 * @Annotation()
 * @Annotation\Target({"CLASS"})
 * @NamedArgumentConstructor()
 */
#[Attribute(Attribute::TARGET_CLASS)]
#[NamedArgumentConstructor()]
final class GuardNamespace
{
    /**
     * @Annotation\Attribute(name="namespace", type="string", required=true)
     * @type string
     */
    public $namespace;

    public function __construct(string $namespace)
    {
        $this->namespace = $namespace;
    }
}
