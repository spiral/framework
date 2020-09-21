<?php

/**
 * Spiral Framework. PHP Data Grid
 *
 * @author Valentin Vintsukevich (vvval)
 */

declare(strict_types=1);

namespace Spiral\Tests\DataGrid;

use ArrayIterator;
use PHPUnit\Framework\TestCase;
use Spiral\DataGrid\Compiler;
use Spiral\DataGrid\Exception\CompilerException;
use Spiral\DataGrid\Specification\Filter;
use Spiral\DataGrid\SpecificationInterface;
use Spiral\DataGrid\WriterInterface;
use Spiral\Tests\DataGrid\Fixture;

class CompilerTest extends TestCase
{
    /**
     * @dataProvider sourceProvider
     * @param $source
     */
    public function testNoSpecifications($source): void
    {
        $compiler = new Compiler();
        $compiler->compile($source);

        $this->assertTrue(true);
    }

    /**
     * @dataProvider sourceProvider
     * @param $source
     */
    public function testNoWriter($source): void
    {
        $this->expectException(CompilerException::class);

        $compiler = new Compiler();
        $compiler->compile($source, new Filter\Equals('', ''));
    }

    /**
     * @dataProvider sourceProvider
     * @param $source
     */
    public function testHasWriter($source): void
    {
        $compiler = new Compiler();
        $compiler->addWriter(new Fixture\WriterOne());
        $compiler->compile($source, new Filter\Equals('', ''));

        $this->assertTrue(true);
    }

    /**
     * @return iterable
     */
    public function sourceProvider(): iterable
    {
        return [
            [['some', 'iterable', 'source']],
            ['some non-iterable source'],
            [new ArrayIterator()]
        ];
    }

    /**
     * @dataProvider writersProvider
     * @param                 $source
     * @param                 $expected
     * @param WriterInterface ...$writers
     */
    public function testWriters($source, $expected, WriterInterface ...$writers): void
    {
        $compiler = new Compiler();
        foreach ($writers as $writer) {
            $compiler->addWriter($writer);
        }
        $this->assertSame($expected, $compiler->compile($source, new Filter\Equals('', '')));
    }

    /**
     * @return iterable
     */
    public function writersProvider(): iterable
    {
        return [
            [
                ['some', 'iterable', 'source'],
                ['some', 'iterable', 'source', Fixture\WriterOne::OUTPUT],
                new Fixture\WriterOne()
            ],
            [
                ['some', 'iterable', 'source'],
                ['some', 'iterable', 'source', Fixture\WriterTwo::OUTPUT, Fixture\WriterOne::OUTPUT],
                new Fixture\WriterTwo(),
                new Fixture\WriterOne()
            ],
            [
                ['some', 'iterable', 'source'],
                ['some', 'iterable', 'source', Fixture\WriterOne::OUTPUT, Fixture\WriterTwo::OUTPUT],
                new Fixture\WriterOne(),
                new Fixture\WriterTwo()
            ],
        ];
    }

    /**
     * @dataProvider sequenceProvider
     * @param                        $expected
     * @param SpecificationInterface ...$specifications
     */
    public function testSequence($expected, SpecificationInterface ...$specifications): void
    {
        $compiler = new Compiler();
        $compiler->addWriter(new Fixture\SequenceWriter());
        $this->assertEquals($expected, $compiler->compile([], new Fixture\Sequence([], ...$specifications)));
    }

    /**
     * @return iterable
     */
    public function sequenceProvider(): iterable
    {
        return [
            [[Filter\Equals::class], new Filter\Equals('', '')],
            [[Filter\Equals::class, Filter\Lt::class], new Filter\Equals('', ''), new Filter\Lt('', '')],
            [[Filter\Lt::class, Filter\Equals::class], new Filter\Lt('', ''), new Filter\Equals('', '')],
        ];
    }
}
