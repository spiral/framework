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
use Spiral\Support\Tests\TestCase;
use Spiral\Tests\MemoryCore;

class NamespacesTest extends TestCase
{
    protected function tearDown()
    {
        $file = new FileManager();
        foreach ($file->getFiles(directory('runtime')) as $filename)
        {
            $file->remove($filename);
        }
    }

    public function testNamespaces()
    {
        $view = $this->viewManager();

        $this->assertSame('This is view A in default namespace A.', $view->render('viewA'));
        $this->assertSame('This is view B in default namespace B.', $view->render('viewB'));

        $this->assertSame('This is view A in default namespace A.', $view->render('default:viewA'));
        $this->assertSame('This is view B in default namespace B.', $view->render('default:viewB'));
        $this->assertSame('This is view A in custom namespace.', $view->render('namespace:viewA'));
    }

    protected function viewManager(array $config = array())
    {
        if (empty($config))
        {
            $config = array(
                'namespaces'      => array(
                    'default'   => array(
                        __DIR__ . '/fixtures/default/',
                        __DIR__ . '/fixtures/default-b/',
                    ),
                    'namespace' => array(
                        __DIR__ . '/fixtures/namespace/',
                    )
                ),
                'caching'         => array(
                    'enabled'   => false,
                    'directory' => directory('runtime')
                ),
                'staticVariables' => array(),
                'engines'         => array(
                    'default' => array(
                        'extensions' => array('php'),
                        'compiler'   => false,
                        'view'       => 'Spiral\Components\View\View',
                        'processors' => array()
                    )
                )
            );
        }

        return new ViewManager(
            MemoryCore::getInstance()->setConfig('views', $config),
            new FileManager()
        );
    }
}