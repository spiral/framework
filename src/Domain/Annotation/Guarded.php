<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Domain\Annotation;

use Doctrine\Common\Annotations\Annotation\Attribute;

/**
 * @Annotation
 * @Target({"METHOD", "CLASS"})
 */
final class Guarded
{
    /**
     * @Attribute(name="permission", type="string")
     * @type string|null
     */
    public $permission;

    /**
     * @Enum({"notFound","forbidden","badAction","error"})
     * @type string
     */
    public $else = 'forbidden';
}
