<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Debug;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Spiral\Debug\Dumper;
use Spiral\Debug\Exception\DumperException;
use Spiral\Debug\Renderer\ConsoleRenderer;
use Spiral\Debug\Renderer\HtmlRenderer;
use Spiral\Debug\Renderer\PlainRenderer;

class DumperTest extends TestCase
{
    public function testOutput(): void
    {
        $d = $this->makeDumper();

        ob_start();
        $d->dump(1);
        $result = ob_get_clean();

        $this->assertSame($d->dump(1, Dumper::RETURN), $result);
    }

    private function makeDumper(LoggerInterface $logger = null)
    {
        $d = new Dumper($logger);
        $d->setRenderer(Dumper::OUTPUT, new PlainRenderer());
        $d->setRenderer(Dumper::RETURN, new PlainRenderer());
        $d->setRenderer(Dumper::OUTPUT_CLI_COLORS, new PlainRenderer());

        return $d;
    }

    public function testScalar(): void
    {
        $d = $this->makeDumper();
        $result = $d->dump(123, Dumper::RETURN);

        $this->assertStringContainsString('123', $result);
    }

    public function testString(): void
    {
        $d = $this->makeDumper();
        $result = $d->dump('hello world', Dumper::RETURN);

        $this->assertStringContainsString('hello world', $result);
    }

    public function testResource(): void
    {
        $d = $this->makeDumper();
        $result = $d->dump(STDOUT, Dumper::RETURN);

        $this->assertStringContainsString('resource', $result);
    }

    public function testHTML(): void
    {
        $d = $this->makeDumper();
        $result = $d->dump('hello <br/>world', Dumper::RETURN);

        $this->assertStringContainsString('hello &lt;br/&gt;world', $result);
    }

    public function testArray(): void
    {
        $d = $this->makeDumper();
        $result = $d->dump(['G', 'B', 'N'], Dumper::RETURN);

        $this->assertStringContainsString('array', $result);
        $this->assertStringContainsString('G', $result);
        $this->assertStringContainsString('B', $result);
        $this->assertStringContainsString('N', $result);
    }

    public function testAnonClass(): void
    {
        $d = $this->makeDumper();

        $result = $d->dump(new class() {
            private $name = 'Test';
        }, Dumper::RETURN);

        $this->assertStringContainsString('object', $result);
        $this->assertStringContainsString('private', $result);
        $this->assertStringContainsString('name', $result);
        $this->assertStringContainsString('string(4)', $result);
        $this->assertStringContainsString('test', $result);
    }

    public function testClosure(): void
    {
        $d = $this->makeDumper();

        $result = $d->dump(static function (): void {
            echo 'hello world';
        }, Dumper::RETURN);

        $this->assertStringContainsString('Closure', $result);
        $this->assertStringContainsString('DumperTest.php', $result);
    }

    public function testToLog(): void
    {
        $mock = $this->createMock(LoggerInterface::class);
        $d = $this->makeDumper($mock);

        $mock->method('debug')->with($d->dump('abc', Dumper::RETURN));
        $this->assertNull($d->dump('abc', Dumper::LOGGER));
    }

    public function testErrorLog(): void
    {
        $d = $this->makeDumper();

        ini_set('error_log', 'errors.log');
        $this->assertNull($d->dump('abc', Dumper::ERROR_LOG));

        $this->assertStringContainsString('abc', file_get_contents('errors.log'));
        unlink('errors.log');
    }

    public function testToLogException(): void
    {
        $this->expectException(DumperException::class);

        $d = $this->makeDumper();
        $this->assertNull($d->dump('abc', Dumper::LOGGER));
    }

    public function testTargetException(): void
    {
        $this->expectException(DumperException::class);

        $d = $this->makeDumper();
        $this->assertNull($d->dump('abc', 9));
    }

    public function testDebugInfo(): void
    {
        $d = $this->makeDumper();
        $result = $d->dump(new class() {
            protected $inner = '_kk_';

            public function __debugInfo()
            {
                return [
                    '_magic_' => '_value_',
                ];
            }
        }, Dumper::RETURN);

        $this->assertStringContainsString('_magic_', $result);
        $this->assertStringContainsString('_value_', $result);
        $this->assertStringNotContainsString('inner', $result);
        $this->assertStringNotContainsString('_kk_', $result);
    }

    public function testinternal(): void
    {
        $d = $this->makeDumper();
        $result = $d->dump(new class() {
            protected $visible = '_kk_';

            /** @internal */
            protected $internal = '_ok_';
        }, Dumper::RETURN);

        $this->assertStringContainsString('visible', $result);
        $this->assertStringContainsString('_kk_', $result);

        $this->assertStringNotContainsString('internal', $result);
        $this->assertStringNotContainsString('_ok_', $result);
    }

    public function testSetRenderer(): void
    {
        $this->expectException(DumperException::class);

        $d = $this->makeDumper();
        $d->setRenderer(8, new HtmlRenderer());
    }

    public function testHtmlRenderer(): void
    {
        $d = $this->makeDumper();
        $d->setRenderer(Dumper::RETURN, new HtmlRenderer());

        $result = $d->dump(new class() {
            protected static $static = 'yes';

            private $value = 123;

            protected $visible = '_kk_';

            /** @internal */
            protected $internal = '_ok_';
        }, Dumper::RETURN);

        $this->assertStringContainsString('visible', $result);
        $this->assertStringContainsString('_kk_', $result);

        $this->assertStringNotContainsString('internal', $result);
        $this->assertStringNotContainsString('_ok_', $result);
    }

    public function testInvertedRenderer(): void
    {
        $d = $this->makeDumper();
        $d->setRenderer(Dumper::RETURN, new HtmlRenderer(HtmlRenderer::INVERTED));
        $d->setMaxLevel(5);

        $result = $d->dump(new class() {
            private $value = 123;

            protected $visible = '_kk_';

            public $data = ['name' => 'value'];

            /** @internal */
            protected $internal = '_ok_';
        }, Dumper::RETURN);

        $this->assertStringContainsString('visible', $result);
        $this->assertStringContainsString('_kk_', $result);

        $this->assertStringNotContainsString('internal', $result);
        $this->assertStringNotContainsString('_ok_', $result);
    }

    public function testConsoleRenderer(): void
    {
        $d = $this->makeDumper();
        $d->setRenderer(Dumper::RETURN, new ConsoleRenderer());
        $d->setMaxLevel(5);

        $result = $d->dump(new class() {
            private $value = 123;

            protected $visible = '_kk_';

            public $data = ['name' => 'value'];

            /** @internal */
            protected $internal = '_ok_';
        }, Dumper::RETURN);

        $this->assertStringContainsString('visible', $result);
        $this->assertStringContainsString('_kk_', $result);

        $this->assertStringNotContainsString('internal', $result);
        $this->assertStringNotContainsString('_ok_', $result);
    }
}
