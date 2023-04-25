<?php

declare(strict_types=1);

namespace Framework\Filter\Model;

use Spiral\App\Request\TestRequest;
use Spiral\Tests\Framework\Filter\FilterTestCase;

final class MethodAttributeTest extends FilterTestCase
{
    public function testGetMethodValue(): void
    {
        $filter = $this->getFilter(TestRequest::class, method: 'GET');

        $this->assertSame('GET', $filter->method);
    }
}
