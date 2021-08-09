<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Vladislav Gorenkin (vladgor)
 */

declare(strict_types=1);

namespace Spiral\Tests\Annotations\Fixtures;

use Spiral\Tests\Annotations\Fixtures\Annotation\PropertyAnnotation;
use Spiral\Tests\Annotations\Fixtures\Annotation\MethodAnnotation;
use Spiral\Tests\Annotations\Fixtures\Annotation\ClassAnnotation;

#[ClassAnnotation(value: 'abc')]
class AttributeTestClass
{
    #[PropertyAnnotation(id: '123')]
    public $name;

    #[MethodAnnotation(path: '/')]
    public function testMethod()
    {
    }
}
