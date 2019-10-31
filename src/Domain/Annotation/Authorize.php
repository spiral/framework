<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Domain\Annotation;

/**
 * @Annotation
 * @Target("METHOD")
 * @Attributes({
 *      @Attribute("permission", type="string", required=true),
 *      @Attribute("error", type="string"),
 * })
 */
final class Authorize
{
    /** @var string */
    public $permission;

    /** @var string */
    public $error;
}