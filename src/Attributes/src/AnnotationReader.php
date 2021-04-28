<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Attributes;

use Doctrine\Common\Annotations\Reader;
use Spiral\Attributes\Internal\Decorator;
use Spiral\Attributes\Internal\DoctrineAnnotationReader;

final class AnnotationReader extends Decorator
{
    /**
     * @param Reader|null $reader
     */
    public function __construct(Reader $reader = null)
    {
        parent::__construct(new DoctrineAnnotationReader($reader));
    }
}
