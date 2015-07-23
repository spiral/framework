<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Tests\Cases\Components\View;

use Spiral\Components\Files\FileManager;
use Spiral\Components\View\ViewManager;
use Spiral\Core\Configurator;
use Spiral\Core\Container;
use Spiral\Helpers\StringHelper;
use Spiral\Support\Tests\TestCase;

class TemplaterTest extends TestCase
{
    /**
     * Configured view component.
     *
     * @param array $config
     * @return ViewManager
     * @throws \Spiral\Core\CoreException
     */
    protected function viewManager(array $config = [])
    {
        if (empty($config))
        {
            $config = [
                'namespaces'   => [
                    'default'   => [
                        __DIR__ . '/fixtures/templater/default/'
                    ],
                    'namespace' => [
                        __DIR__ . '/fixtures/templater/namespace/',
                    ]
                ],
                'caching'      => [
                    'enabled'   => false,
                    'directory' => directory('runtime')
                ],
                'dependencies' => [],
                'engines'      => [
                    'default' => [
                        'extensions' => ['php'],
                        'compiler'   => 'Spiral\Components\View\Compiler\Compiler',
                        'view'       => 'Spiral\Components\View\View',
                        'processors' => [
                            'Spiral\Components\View\Compiler\Processors\TemplateProcessor' => []
                        ]
                    ]
                ]
            ];
        }

        return new ViewManager(
            new Configurator(['views' => $config]),
            Container::getInstance(),
            new FileManager()
        );
    }

    protected function tearDown()
    {
        $file = new FileManager();
        foreach ($file->getFiles(directory('runtime')) as $filename)
        {
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
        $lines = explode("\n", StringHelper::normalizeEndings($this->viewManager()->render($view)));

        return array_values(array_map('trim', array_filter($lines, 'trim')));
    }

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
        $result = $this->render('base-c');

        $this->assertSame('Block A defined in file B(default).', $result[0]);
        $this->assertSame('Block B defined in file A(default).', $result[1]);
        $this->assertSame('Block C defined in file C(default).', $result[2]);
    }

    public function testBaseD()
    {
        $result = $this->render('namespace:base-d');

        $this->assertSame('Block A defined in file B(default).', $result[0]);
        $this->assertSame('Block B defined in file A(default).', $result[1]);
        $this->assertSame('Block B defined in file D(namespace).', $result[2]);
        $this->assertSame('Block C defined in file C(default).', $result[3]);
    }

    public function testBaseE()
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
        foreach ($result as $index => $line)
        {
            echo '$this->assertSame(\'' . $line . '\', $result[' . $index . ']);' . "\n";
        }
    }
}