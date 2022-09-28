<?php

declare(strict_types=1);

namespace Framework\Filter;

use Spiral\Filter\JsonErrorsRenderer;
use Spiral\Http\ResponseWrapper;
use Spiral\Tests\Framework\BaseTest;

final class JsonErrorsRendererTest extends BaseTest
{
    public function testRender(): void
    {
        $renderer = new JsonErrorsRenderer(
            $this->getContainer()->get(ResponseWrapper::class)
        );

        $response = $renderer->render(
            ['foo' => 'bar',],
            'foo_context'
        );

        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame('The given data was invalid.', $response->getReasonPhrase());
        $this->assertSame(
            '{"errors":{"foo":"bar"}}',
            (string) $response->getBody()
        );
    }
}
