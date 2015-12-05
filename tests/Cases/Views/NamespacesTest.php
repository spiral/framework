<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Tests\Cases\Views;

use Spiral\Core\Container;
use Spiral\Core\ContainerInterface;
use Spiral\Core\Containers\SpiralContainer;
use Spiral\Files\FileManager;
use Spiral\Views\Configs\ViewsConfig;
use Spiral\Views\Engines\NativeEngine;
use Spiral\Views\ViewManager;

class NamespacesTest extends \PHPUnit_Framework_TestCase
{
    public function testNamespaces()
    {
        $view = $this->viewManager();

        $this->assertSame(
            'This is view A in default namespace A.', $view->render('view-a')
        );

        $this->assertSame(
            'This is view B in default namespace B.', $view->render('view-b')
        );

        $this->assertSame(
            'This is view A in default namespace A.', $view->render('default:view-a')
        );

        $this->assertSame(
            'This is view B in default namespace B.', $view->render('default:view-b')
        );
        $this->assertSame(
            'This is view A in custom namespace.', $view->render('namespace:view-a')
        );
    }

    protected function tearDown()
    {
        $files = new FileManager();
        if ($files->isDirectory(TEST_CACHE . '/views')) {
            foreach ($files->getFiles(TEST_CACHE . '/views') as $filename) {
                $files->delete($filename);
            }
        }
    }

    /**
     * @param array $config
     * @return ViewManager
     */
    protected function viewManager(array $config = [])
    {
        if (empty($config)) {
            $config = [
                'cache'       => [
                    'enabled'   => false,
                    'directory' => TEST_CACHE
                ],
                'namespaces'  => [
                    'default'   => [
                        __DIR__ . '/fixtures/default/',
                        __DIR__ . '/fixtures/default-b/',
                    ],
                    'namespace' => [
                        __DIR__ . '/fixtures/namespace/',
                    ]
                ],
                'environment' => [],
                'engines'     => [
                    'native' => [
                        'class'     => NativeEngine::class,
                        'extension' => 'php'
                    ],
                ]
            ];
        }

        $container = new SpiralContainer();
        $container->bind(ContainerInterface::class, $container);

        return new ViewManager(new ViewsConfig($config), new FileManager(), $container);
    }
}