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
use Spiral\Attributes\NamedArgumentConstructor;

/**
 * @Annotation
 * @NamedArgumentConstructor
 * @Target({"METHOD", "CLASS"})
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_CLASS), NamedArgumentConstructor]
final class Guarded
{
    /**
     * @Attribute(name="permission", type="string")
     * @type string|null
     */
    public $permission;

    /**
     * @Enum({"notFound","unauthorized","forbidden","badAction","error"})
     * @type string
     */
    public $else = 'forbidden';

    /**
     * Error message in case of error.
     *
     * @Attribute(name="errorMessage", type="string")
     * @type string
     */
    public $errorMessage;

    public function __construct(?string $permission = null, string $else = 'forbidden', ?string $errorMessage = null)
    {
        $this->permission = $permission;
        $this->else = $else;
        $this->errorMessage = $errorMessage;
    }
}
