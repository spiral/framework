<?php

declare(strict_types=1);

namespace Spiral\Tests\Http\SapiEmitter\Support;

include 'httpFunctionMocks.php';

use PHPUnit\Framework\TestCase;

/**
 * @source https://github.com/yiisoft/yii-web/blob/master/tests/Emitter/Support/HTTPFunctionsTest.php
 * @license MIT
 * @copyright Yii Software LLC (http://www.yiisoft.com) All rights reserved.
 */
class HTTPFunctionsTest extends TestCase
{
    public function setUp(): void
    {
        HTTPFunctions::reset();
    }

    public static function tearDownAfterClass(): void
    {
        HTTPFunctions::reset();
    }

    public function testInitialState(): void
    {
        $this->assertEquals(200, $this->getResponseCode());
        $this->assertEquals([], $this->getHeaders());
        $this->assertFalse(HTTPFunctions::headers_sent());
    }

    public function testHeaderAndHasHeader(): void
    {
        $this->assertFalse(HTTPFunctions::hasHeader('x-test'));

        HTTPFunctions::header('X-Test: 1');

        $this->assertTrue(HTTPFunctions::hasHeader('x-test'));
    }

    public function testReset(): void
    {
        HTTPFunctions::header('X-Test: 1');
        HTTPFunctions::header('X-Test: 2', false, 500);
        HTTPFunctions::set_headers_sent(true, 'test', 123);

        HTTPFunctions::reset();

        $this->assertEquals(200, $this->getResponseCode());
        $this->assertEquals([], $this->getHeaders());
        $this->assertFalse(HTTPFunctions::headers_sent($file, $line));
        $this->assertEquals('', $file);
        $this->assertEquals(0, $line);
    }

    public function testHeadersSent(): void
    {
        HTTPFunctions::set_headers_sent(true, 'path/to/test/file.php', 123);

        $this->assertTrue(HTTPFunctions::headers_sent($file, $line));
        $this->assertEquals('path/to/test/file.php', $file);
        $this->assertEquals(123, $line);
    }

    public function testAddedHeaders(): void
    {
        // first header
        HTTPFunctions::header('X-Test: 1');
        // added header with new status
        HTTPFunctions::header('X-Test: 2', false, 500);
        HTTPFunctions::header('X-Test: 3', false);

        $this->assertContains('X-Test: 1', $this->getHeaders());
        $this->assertContains('X-Test: 2', $this->getHeaders());
        $this->assertContains('X-Test: 3', $this->getHeaders());
        $this->assertEquals(500, $this->getResponseCode());
    }

    public function testReplacingHeaders(): void
    {
        HTTPFunctions::header('X-Test: 1');
        HTTPFunctions::header('X-Test: 2', false, 300);
        HTTPFunctions::header('X-Test: 3', false);

        // replace x-test headers with new status
        HTTPFunctions::header('X-Test: 42', true, 404);

        $this->assertEquals(['X-Test: 42'], $this->getHeaders());
        $this->assertEquals(404, $this->getResponseCode());
    }

    public function testHeaderRemove(): void
    {
        HTTPFunctions::header('X-Test: 1');
        HTTPFunctions::header('Y-Test: 2');
        HTTPFunctions::header('Z-Test: 3', false, 404);

        HTTPFunctions::header_remove('y-test');

        $this->assertEquals(['X-Test: 1', 'Z-Test: 3'], $this->getHeaders());
    }

    public function testHeaderRemoveAll(): void
    {
        HTTPFunctions::header('X-Test: 1');
        HTTPFunctions::header('Y-Test: 2');
        HTTPFunctions::header('Z-Test: 3', false, 404);

        HTTPFunctions::header_remove();

        $this->assertEquals(404, $this->getResponseCode());
        $this->assertEquals([], $this->getHeaders());
    }

    private function getHeaders(): array
    {
        return HTTPFunctions::headers_list();
    }

    private function getResponseCode(): int
    {
        return HTTPFunctions::http_response_code();
    }
}
