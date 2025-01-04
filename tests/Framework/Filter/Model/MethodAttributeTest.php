<?php

declare(strict_types=1);

namespace Framework\Filter\Model;

use Spiral\App\Request\TestRequest;
use Spiral\Framework\Spiral;
use Spiral\Testing\Attribute\TestScope;
use Spiral\Tests\Framework\Filter\FilterTestCase;

final class MethodAttributeTest extends FilterTestCase
{
    #[TestScope(Spiral::HttpRequest)]
    public function testGetMethodValue(): void
    {
        $filter = $this->getFilter(TestRequest::class, method: 'GET');

        self::assertSame('GET', $filter->method);
    }
}
