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
 * @Annotation\Target({"METHOD", "CLASS"})
 * @NamedArgumentConstructor()
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
#[NamedArgumentConstructor()]
final class Guarded
{
    /**
     * @Annotation\Attribute(name="permission", type="string")
     * @type null|string
     */
    public $permission;

    /**
     * @Enum({"notFound", "unauthorized", "forbidden", "badAction", "error"})
     * @type string
     */
    public $else;

    /**
     * Error message in case of error.
     *
     * @Annotation\Attribute(name="errorMessage", type="string")
     * @type null|string
     */
    public $errorMessage;

    public function __construct(
        ?string $permission = null,
        string $else = 'forbidden',
        ?string $errorMessage = null
    ) {
        $this->permission = $permission;
        $this->else = $else;
        $this->errorMessage = $errorMessage;
    }
}
