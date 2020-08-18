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

    public function testScalar(): void
    {
        $d = $this->makeDumper();
        $result = $d->dump(123, Dumper::RETURN);

        $this->assertContains('123', $result);
    }

    public function testString(): void
    {
        $d = $this->makeDumper();
        $result = $d->dump('hello world', Dumper::RETURN);

        $this->assertContains('hello world', $result);
    }

    public function testResource(): void
    {
        $d = $this->makeDumper();
        $result = $d->dump(STDOUT, Dumper::RETURN);

        $this->assertContains('resource', $result);
    }


    public function testHTML(): void
    {
        $d = $this->makeDumper();
        $result = $d->dump('hello <br/>world', Dumper::RETURN);

        $this->assertContains('hello &lt;br/&gt;world', $result);
    }

    public function testArray(): void
    {
        $d = $this->makeDumper();
        $result = $d->dump(['G', 'B', 'N'], Dumper::RETURN);

        $this->assertContains('array', $result);
        $this->assertContains('G', $result);
        $this->assertContains('B', $result);
        $this->assertContains('N', $result);
    }

    public function testAnonClass(): void
    {
        $d = $this->makeDumper();

        $result = $d->dump(new class() {
            private $name = 'Test';
        }, Dumper::RETURN);

        $this->assertContains('object', $result);
        $this->assertContains('private', $result);
        $this->assertContains('name', $result);
        $this->assertContains('string(4)', $result);
        $this->assertContains('test', $result);
    }

    public function testClosure(): void
    {
        $d = $this->makeDumper();

        $result = $d->dump(function (): void {
            echo 'hello world';
        }, Dumper::RETURN);

        $this->assertContains('Closure', $result);
        $this->assertContains('DumperTest.php', $result);
    }

    public function testToLog(): void
    {
        $mock = $this->createMock(LoggerInterface::class);
        $d = $this->makeDumper($mock);

        $mock->method('debug')->with($d->dump('abc', Dumper::RETURN));
        $this->assertSame(null, $d->dump('abc', Dumper::LOGGER));
    }

    public function testErrorLog(): void
    {
        $d = $this->makeDumper();

        ini_set('error_log', 'errors.log');
        $this->assertSame(null, $d->dump('abc', Dumper::ERROR_LOG));

        $this->assertContains('abc', file_get_contents('errors.log'));
        unlink('errors.log');
    }

    /**
     * @expectedException \Spiral\Debug\Exception\DumperException
     */
    public function testToLogException(): void
    {
        $d = $this->makeDumper();
        $this->assertSame(null, $d->dump('abc', Dumper::LOGGER));
    }

    /**
     * @expectedException \Spiral\Debug\Exception\DumperException
     */
    public function testTargetException(): void
    {
        $d = $this->makeDumper();
        $this->assertSame(null, $d->dump('abc', 9));
    }

    public function testDebugInfo(): void
    {
        $d = $this->makeDumper();
        $result = $d->dump(new class() {
            protected $inner = '_kk_';

            public function __debugInfo()
            {
                return [
                    '_magic_' => '_value_'
                ];
            }
        }, Dumper::RETURN);

        $this->assertContains('_magic_', $result);
        $this->assertContains('_value_', $result);
        $this->assertNotContains('inner', $result);
        $this->assertNotContains('_kk_', $result);
    }

    public function testinternal(): void
    {
        $d = $this->makeDumper();
        $result = $d->dump(new class() {
            protected $visible = '_kk_';

            /** @internal */
            protected $internal = '_ok_';
        }, Dumper::RETURN);

        $this->assertContains('visible', $result);
        $this->assertContains('_kk_', $result);

        $this->assertNotContains('internal', $result);
        $this->assertNotContains('_ok_', $result);
    }

    /**
     * @expectedException \Spiral\Debug\Exception\DumperException
     */
    public function testSetRenderer(): void
    {
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

        $this->assertContains('visible', $result);
        $this->assertContains('_kk_', $result);

        $this->assertNotContains('internal', $result);
        $this->assertNotContains('_ok_', $result);
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

        $this->assertContains('visible', $result);
        $this->assertContains('_kk_', $result);

        $this->assertNotContains('internal', $result);
        $this->assertNotContains('_ok_', $result);
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

        $this->assertContains('visible', $result);
        $this->assertContains('_kk_', $result);

        $this->assertNotContains('internal', $result);
        $this->assertNotContains('_ok_', $result);
    }

    private function makeDumper(LoggerInterface $logger = null)
    {
        $d = new Dumper($logger);
        $d->setRenderer(Dumper::OUTPUT, new PlainRenderer());
        $d->setRenderer(Dumper::RETURN, new PlainRenderer());
        $d->setRenderer(Dumper::OUTPUT_CLI_COLORS, new PlainRenderer());

        return $d;
    }
}
