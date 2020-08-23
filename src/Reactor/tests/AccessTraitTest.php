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
use Spiral\Reactor\AbstractDeclaration;
use Spiral\Reactor\Exception\ReactorException;
use Spiral\Reactor\Traits\AccessTrait;

class AccessTraitTest extends TestCase
{
    use AccessTrait;

    public function testProtected(): void
    {
        $this->setProtected();
        $this->assertSame(AbstractDeclaration::ACCESS_PROTECTED, $this->getAccess());
    }


    public function testPrivate(): void
    {
        $this->setPrivate();
        $this->assertSame(AbstractDeclaration::ACCESS_PRIVATE, $this->getAccess());
    }


    public function testPublic(): void
    {
        $this->setPublic();
        $this->assertSame(AbstractDeclaration::ACCESS_PUBLIC, $this->getAccess());
    }

    public function testBad(): void
    {
        $this->expectException(ReactorException::class);

        $this->setAccess('wrong');
    }
}
