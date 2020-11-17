<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Tests\Attributes\Fixture;

use Spiral\Tests\Attributes\Fixture\Annotation\ClassAnnotation;
use Spiral\Tests\Attributes\Fixture\Annotation\ConstantAnnotation;
use Spiral\Tests\Attributes\Fixture\Annotation\MethodAnnotation;
use Spiral\Tests\Attributes\Fixture\Annotation\MethodParameterAnnotation;
use Spiral\Tests\Attributes\Fixture\Annotation\PropertyAnnotation;

/** @ClassAnnotation(field="value") */
#[ClassAnnotation(field: 'value')]
class AnnotatedClass
{
    /** @ConstantAnnotation(field="value") */
    #[ConstantAnnotation(field: 'value')]
    public const CONSTANT = 23;

    /** @PropertyAnnotation(field="value") */
    #[PropertyAnnotation(field: 'value')]
    public $property;

    /** @MethodAnnotation(field="value") */
    #[MethodAnnotation(field: 'value')]
    public function method(
        #[MethodParameterAnnotation(field: 'value')]
        string $parameter
    ): void
    {
    }
}