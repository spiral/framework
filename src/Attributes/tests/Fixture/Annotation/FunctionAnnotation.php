<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Tests\Attributes\Fixture\Annotation;

/**
 * @Annotation
 * @Target({ "FUNCTION" })
 */
#[\Attribute(\Attribute::TARGET_FUNCTION)]
class FunctionAnnotation extends Annotation
{
}
