<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Annotations\Fixtures;

use Spiral\Tests\Annotations\Fixtures\Annotation\Another;
use Spiral\Tests\Annotations\Fixtures\Annotation\Route;
use Spiral\Tests\Annotations\Fixtures\Annotation\Value;

/**
 * @Value(value="abc")
 */
class TestClass
{
    /** @Another(id="123") */
    public $name;

    /**
     * @Route(path="/")
     */
    public function testMethod()
    {
    }
}
