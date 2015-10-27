<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Tests\Cases\Views;

use Spiral\Core\Configurator;
use Spiral\Core\Container;
use Spiral\Core\ContainerInterface;
use Spiral\Core\Core;
use Spiral\Files\FileManager;
use Spiral\Support\StringHelper;
use Spiral\Views\ViewManager;

class TemplaterTest extends \PHPUnit_Framework_TestCase
{
    public function testBaseA()
    {
        $result = $this->render('base-a');

        $this->assertSame('Block A defined in file A(default).', $result[0]);
        $this->assertSame('Block B defined in file A(default).', $result[1]);
        $this->assertSame('Block C defined in file A(default).', $result[2]);
    }

    public function testBaseB()
    {
        $result = $this->render('base-b');

        $this->assertSame('Block A defined in file B(default).', $result[0]);
        $this->assertSame('Block B defined in file A(default).', $result[1]);
        $this->assertSame('Block C defined in file A(default).', $result[2]);
    }

    public function testBaseC()
    {
        $result = $this->render('namespace:base-e');

        $this->assertSame('Block A defined in file B(default).', $result[0]);
        $this->assertSame('Block B defined in file A(default).', $result[1]);
        $this->assertSame('Block B defined in file D(namespace). Base E.', $result[2]);
        $this->assertSame('Block C defined in file C(default).', $result[3]);
    }

    public function testIncludesA()
    {
        $result = $this->render('includes-a');

        $this->assertSame('Include A, block A.', $result[0]);
        $this->assertSame('<tag name="tag-a">', $result[1]);
        $this->assertSame('Include A, block B (inside tag).', $result[2]);
        $this->assertSame('</tag>', $result[3]);
        $this->assertSame('Include A, block C.', $result[4]);
    }

    public function testIncludesB()
    {
        $result = $this->render('includes-b');

        $this->assertSame('Include A, block A.', $result[0]);
        $this->assertSame('<tag name="tag-a">', $result[1]);
        $this->assertSame('<tag class="tag-b" name="tag-b">', $result[2]);
        $this->assertSame('Include A, block C (inside tag B).', $result[3]);
        $this->assertSame('</tag>', $result[4]);
        $this->assertSame('</tag>', $result[5]);
        $this->assertSame('Include A, block C.', $result[6]);
    }

    public function testIncludesC()
    {
        $result = $this->render('includes-c');

        $this->assertSame('Include A, block A.', $result[0]);
        $this->assertSame('<tag name="tag-a">', $result[1]);
        $this->assertSame('Include A, block B (inside tag).', $result[2]);
        $this->assertSame('</tag>', $result[3]);
        $this->assertSame('<tag class="tag-b" name="ABC">', $result[4]);
        $this->assertSame('<tag name="tag-a">', $result[5]);
        $this->assertSame('Include A, block B (inside tag).', $result[6]);
        $this->assertSame('</tag>', $result[7]);
        $this->assertSame('</tag>', $result[8]);
    }

    public function testIncludesD()
    {
        $result = $this->render('namespace:includes-d');

        $this->assertSame('<tag class="class my-class" id="123">', $result[0]);
        $this->assertSame('<tag class="tag-b" name="tag-b">', $result[1]);
        $this->assertSame('<tag class="class new-class" value="abc">', $result[2]);
        $this->assertSame('Some context.', $result[3]);
        $this->assertSame('</tag>', $result[4]);
        $this->assertSame('</tag>', $result[5]);
        $this->assertSame('</tag>', $result[6]);
    }

    protected function createAsserts($result)
    {
        foreach ($result as $index => $line) {
            echo '$this->assertSame(\'' . $line . '\', $result[' . $index . ']);' . "\n";
        }
    }

    protected function tearDown()
    {
        $file = new FileManager();
        foreach ($file->getFiles($this->spiralCore()->directory('runtime')) as $filename) {
            $file->delete($filename);
        }
    }

    /**
     * Render view and return it's blank lines.
     *
     * @param string $view
     * @return array
     */
    protected function render($view)
    {
        $lines = explode("\n", StringHelper::normalizeEndings(
            $this->viewManager()->render($view)
        ));

        return array_values(array_map('trim', array_filter($lines, 'trim')));
    }

    /**
     * @param array $config
     * @return ViewManager
     */
    protected function viewManager(array $config = [])
    {
        if (empty($config)) {
            $config = [
                'cache'        => [
                    'enabled'   => false,
                    'directory' => $this->spiralCore()->directory('runtime')
                ],
                'namespaces'   => [
                    'default'   => [
                        __DIR__ . '/fixtures/templater/default/'
                    ],
                    'namespace' => [
                        __DIR__ . '/fixtures/templater/namespace/',
                    ]
                ],
                'dependencies' => [],
                'engines'      => [
                    'default' => [
                        'extensions' => ['php'],
                        'compiler'   => 'Spiral\Views\Compiler',
                        'view'       => 'Spiral\Views\View'
                    ]
                ],
                'compiler'     => [
                    'processors' => [
                        'Spiral\Views\Processors\TemplateProcessor' => []
                    ]
                ],
                'classes'      => [

                ]
            ];
        }

        return new ViewManager(
            new Configurator($config),
            $this->spiralCore(),
            new FileManager()
        );
    }

    /**
     * @return Core
     */
    protected function spiralCore()
    {
        $core = new Core([
            'root'        => TEST_ROOT,
            'application' => TEST_ROOT
        ]);

        $core->bind(ContainerInterface::class, $core);

        return $core;
    }
}