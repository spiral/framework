<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Tests\Attributes\Fixture;

use Spiral\Tests\Attributes\Fixture\Annotation\FunctionAnnotation;
use Spiral\Tests\Attributes\Fixture\Annotation\FunctionParameterAnnotation;

/** @FunctionAnnotation(field="value") */
#[FunctionAnnotation(field: 'value')]
function annotated_function(
    #[FunctionParameterAnnotation(field: 'value')]
    string $parameter
)
{

}
