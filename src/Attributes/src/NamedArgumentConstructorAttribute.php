<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Attributes;

use Doctrine\Common\Annotations\NamedArgumentConstructorAnnotation;

/**
 * Marker interface for PHP7/PHP8 compatible support for named arguments
 * (and constructor property promotion).
 */
interface NamedArgumentConstructorAttribute extends NamedArgumentConstructorAnnotation
{
}
