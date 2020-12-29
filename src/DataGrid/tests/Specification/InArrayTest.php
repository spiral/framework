<?php

declare(strict_types=1);

namespace Spiral\Tests\DataGrid\Specification;

use PHPUnit\Framework\TestCase;
use Spiral\DataGrid\Specification\Filter\InArray;
use Spiral\DataGrid\Specification\Value\Accessor\Split;
use Spiral\DataGrid\Specification\Value\ArrayValue;
use Spiral\DataGrid\Specification\Value\IntValue;
use Spiral\DataGrid\SpecificationInterface;

class InArrayTest extends TestCase
{
    public function testWithSplit(): void
    {
        $str = '1|2|3';

        $split = new Split(new ArrayValue(new IntValue()), '|');
        $this->assertTrue($split->accepts($str));
        $this->assertSame([1, 2, 3], $split->convert($str));

        $inArray = new InArray('field', $split);
        $this->assertNotInstanceOf(SpecificationInterface::class, $inArray->withValue($str));

        $inArray = new InArray('field', $split, false);
        $this->assertInstanceOf(SpecificationInterface::class, $inArray->withValue($str));
        $this->assertSame([1, 2, 3], $inArray->withValue($str)->getValue());
    }
}
