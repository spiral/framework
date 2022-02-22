<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Tests\Attributes\Reader\Fixture;

use Spiral\Tests\Attributes\Reader\Fixture\Annotation\ClassAnnotation;

/** @ClassAnnotation(field="value") */
#[ClassAnnotation(field: 'value')]
trait AnnotatedTrait {

}
