<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Reactor;

use PHPUnit\Framework\TestCase;
use Spiral\Reactor\Aggregator;
use Spiral\Reactor\Aggregator\Methods;
use Spiral\Reactor\Aggregator\Parameters;
use Spiral\Reactor\Exception\ReactorException;
use Spiral\Reactor\Partial\Method;
use Spiral\Reactor\Partial\Property;

class AggregatorsTest extends TestCase
{
    public function testMethods(): void
    {
        $aggr = new Methods([]);
        $m = $aggr->get('method');
        $this->assertNotNull($m);

        $this->assertSame($m, $aggr->get('method'));
    }

    public function testParameters(): void
    {
        $aggr = new Parameters([]);
        $m = $aggr->get('param');
        $this->assertNotNull($m);

        $this->assertSame($m, $aggr->get('param'));
    }

    public function testAggregator(): void
    {
        $this->expectException(ReactorException::class);

        $a = new Aggregator([
            Method::class,
        ]);

        $a->add(new Property('method'));
    }

    public function testAggregatorNoElement(): void
    {
        $this->expectException(ReactorException::class);

        $a = new Aggregator([
            Method::class,
        ]);

        $a->get('method');
    }


    public function testAggregatorRemove(): void
    {
        $a = new Aggregator([
            Method::class,
        ]);

        $a->add(new Method('method'));
        $this->assertInstanceOf(Method::class, $a->method);
        $this->assertTrue(isset($a['method']));
        $this->assertInstanceOf(Method::class, $a['method']);

        $this->assertTrue($a->has('method'));
        $a->remove('method');
        $this->assertFalse($a->has('method'));

        $a['method'] = new Method('method');
        $this->assertTrue($a->has('method'));
        unset($a['method']);
        $this->assertFalse($a->has('method'));
    }
}
