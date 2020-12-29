<?php

declare(strict_types=1);

namespace Spiral\Tests\DataGrid\Specification;

use PHPUnit\Framework\TestCase;
use Spiral\DataGrid\Specification\Filter\InArray;
use Spiral\DataGrid\Specification\Filter\NotInArray;
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

        //failure
        $inArray = new InArray('field', $split);
        $this->assertNotInstanceOf(SpecificationInterface::class, $inArray->withValue($str));

        $notInArray = new NotInArray('field', $split);
        $this->assertNotInstanceOf(SpecificationInterface::class, $notInArray->withValue($str));

        //success
        $inArray = new InArray('field', $split, false);
        $this->assertInstanceOf(SpecificationInterface::class, $inArray->withValue($str));
        $this->assertSame([1, 2, 3], $inArray->withValue($str)->getValue());

        $notInArray = new NotInArray('field', $split, false);
        $this->assertInstanceOf(SpecificationInterface::class, $notInArray->withValue($str));
        $this->assertSame([1, 2, 3], $notInArray->withValue($str)->getValue());
    }

    public function testWithoutSplit(): void
    {
        $arr = ['1', 2];
        $value = new IntValue();

        $inArray = new InArray('field', $value);
        $this->assertInstanceOf(SpecificationInterface::class, $inArray->withValue($arr));
        $this->assertSame([1, 2], $inArray->withValue($arr)->getValue());

        $notInArray = new NotInArray('field', $value);
        $this->assertInstanceOf(SpecificationInterface::class, $notInArray->withValue($arr));
        $this->assertSame([1, 2], $notInArray->withValue($arr)->getValue());

        $inArray = new InArray('field', $value, false);
        $this->assertNotInstanceOf(SpecificationInterface::class, $inArray->withValue($arr));

        $notInArray = new NotInArray('field', $value, false);
        $this->assertNotInstanceOf(SpecificationInterface::class, $notInArray->withValue($arr));
    }

    public function testWithOwnWrapping(): void
    {
        $arr = ['1', 2];
        $value = new ArrayValue(new IntValue());

        $inArray = new InArray('field', $value);
        $this->assertInstanceOf(SpecificationInterface::class, $inArray->withValue($arr));
        $this->assertSame([1, 2], $inArray->withValue($arr)->getValue());

        $notInArray = new NotInArray('field', $value);
        $this->assertInstanceOf(SpecificationInterface::class, $notInArray->withValue($arr));
        $this->assertSame([1, 2], $notInArray->withValue($arr)->getValue());

        $inArray = new InArray('field', $value, false);
        $this->assertInstanceOf(SpecificationInterface::class, $inArray->withValue($arr));
        $this->assertSame([1, 2], $inArray->withValue($arr)->getValue());

        $notInArray = new NotInArray('field', $value, false);
        $this->assertInstanceOf(SpecificationInterface::class, $notInArray->withValue($arr));
        $this->assertSame([1, 2], $notInArray->withValue($arr)->getValue());
    }
}
