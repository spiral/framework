<?php

declare(strict_types=1);

namespace Framework\Filter\Model;

use Spiral\App\Request\FilterWithSetters;
use Spiral\Tests\Framework\Filter\FilterTestCase;

final class FilterWithSettersTest extends FilterTestCase
{
    public function testSetters(): void
    {
        $filter = $this->getFilter(FilterWithSetters::class, [
            'integer' => '1',
            'string' => new class implements \Stringable {
                public function __toString()
                {
                    return '--<b>"test"</b>  ';
                }
            },
        ]);

        $this->assertInstanceOf(FilterWithSetters::class, $filter);

        $this->assertSame(1, $filter->integer);
        $this->assertSame('&lt;b&gt;&quot;test&quot;&lt;/b&gt;', $filter->string);
    }
}
