<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Tests\Core;

use Spiral\Core\Containers\SpiralContainer;
use Spiral\Core\ScoperInterface;
use Spiral\Tests\Core\Fixtures\SampleClass;

class ScopingTest extends \PHPUnit_Framework_TestCase
{
    public function testScoping()
    {
        $container = new SpiralContainer();
        $this->assertInstanceOf(ScoperInterface::class, $container);

        $container->bind(SampleClass::class, $sample = new SampleClass());
        $this->assertSame($sample, $container->get(SampleClass::class));

        $scope = $container->replace(SampleClass::class, $otherSample = new SampleClass());
        $this->assertSame($otherSample, $container->get(SampleClass::class));

        $container->restore($scope);
        $this->assertSame($sample, $container->get(SampleClass::class));
    }

    public function testBindingScoping()
    {
        $container = new SpiralContainer();

        $container->bind('a', function () {
            return 'A';
        });

        $container->bind('b', function () {
            return 'B';
        });

        $container->bind('c', 'a');

        $this->assertSame('A', $container->get('c'));

        $scope = $container->replace('c', 'b');
        $this->assertSame('B', $container->get('c'));

        $container->restore($scope);
        $this->assertSame('A', $container->get('c'));
    }
}
