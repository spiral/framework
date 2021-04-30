<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Tests\Attributes\Reader\Fixture;

use Spiral\Tests\Attributes\Reader\Fixture\Annotation\FunctionAnnotation;
use Spiral\Tests\Attributes\Reader\Fixture\Annotation\FunctionParameterAnnotation;

/** @FunctionAnnotation(field="value") */
#[FunctionAnnotation(field: 'value')]
function annotated_function(
    #[FunctionParameterAnnotation(field: 'value')]
    string $parameter
)
{

}
