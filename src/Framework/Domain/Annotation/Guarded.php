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
use Doctrine\Common\Annotations\Annotation\Attributes;
use Doctrine\Common\Annotations\Annotation\Enum;
use Spiral\Attributes\NamedArgumentConstructor;

/**
 * @Annotation
 * @NamedArgumentConstructor
 * @Target({"METHOD", "CLASS"})
 * @Attributes({
 *     @Attribute("permission", type="string"),
 *     @Attribute("else", type="string"),
 *     @Attribute("errorMessage", type="string")
 * })
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_CLASS), NamedArgumentConstructor]
final class Guarded
{
    /**
     * @var string|null
     */
    public $permission;

    /**
     * @Enum({"notFound","unauthorized","forbidden","badAction","error"})
     * @var string
     */
    public $else = 'forbidden';

    /**
     * Error message in case of error.
     *
     * @var string|null
     */
    public $errorMessage;

    public function __construct(?string $permission = null, string $else = 'forbidden', ?string $errorMessage = null)
    {
        $this->permission = $permission;
        $this->else = $else;
        $this->errorMessage = $errorMessage;
    }
}
