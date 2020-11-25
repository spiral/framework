<?php

/**
 * This file is part of Attributes package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Doctrine\Common\Annotations {
    if (!\interface_exists(NamedArgumentConstructorAnnotation::class)) {
        interface NamedArgumentConstructorAnnotation
        {
        }
    }
}
