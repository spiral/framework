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
use Spiral\Support\Tests\TestCase;

class NamespacesTest extends TestCase
{
    protected function getViewManager(array $config = [])
    {
        if (empty($config))
        {
            $config = [
                'namespaces'   => [
                    'default'   => [
                        __DIR__ . '/fixtures/default/',
                        __DIR__ . '/fixtures/default-b/',
                    ],
                    'namespace' => [
                        __DIR__ . '/fixtures/namespace/',
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
                        'compiler'   => false,
                        'view'       => 'Spiral\Components\View\View'
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

    public function testNamespaces()
    {
        $view = $this->getViewManager();

        $this->assertSame('This is view A in default namespace A.', $view->render('view-a'));
        $this->assertSame('This is view B in default namespace B.', $view->render('view-b'));

        $this->assertSame('This is view A in default namespace A.', $view->render('default:view-a'));
        $this->assertSame('This is view B in default namespace B.', $view->render('default:view-b'));
        $this->assertSame('This is view A in custom namespace.', $view->render('namespace:view-a'));
    }
}