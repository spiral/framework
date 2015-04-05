<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Tests\Cases\Components\Debugger;

use Spiral\Components\Debug\Debugger;
use Spiral\Components\Files\FileManager;
use Spiral\Components\Tokenizer\Tokenizer;
use Spiral\Components\View\ViewManager;
use Spiral\Core\Container;
use Spiral\Core\Loader;
use Spiral\Support\Tests\TestCase;
use Spiral\Tests\MemoryCore;

class SnapshotTest extends TestCase
{
    /**
     * @var Loader
     */
    protected $loader = null;

    protected function setUp()
    {
        $this->loader = new Loader(MemoryCore::getInstance());
        Container::bind('view', $this->viewManager());
        Container::bind('tokenizer', $this->tokenizerComponent());
    }

    protected function tearDown()
    {
        Container::removeBinding('view');
        Container::removeBinding('tokenizer');

        $this->loader->disable();
        $this->loader = null;

        $file = new FileManager();
        foreach ($file->getFiles(directory('runtime')) as $filename)
        {
            $file->remove($filename);
        }
    }

    public function testMemorySnapshot()
    {
        $debug = $this->debuggerComponent();

        $snapshot = $debug->handleException(new \ErrorException('Snapshot Test'), false);

        $this->assertNull($snapshot->getSnapshotFilename());

        //But we still should be able to render it
        $this->assertNotEmpty($snapshot->renderSnapshot());
    }

    public function testFileSnapshot()
    {
        $debug = $this->debuggerComponent(array(
            'loggers'   => array(
                'containers' => array()
            ),
            'backtrace' => array(
                'view'      => 'spiral:exception',
                'snapshots' => array(
                    'enabled'    => true,
                    'timeFormat' => 'd.m.Y-Hi.s',
                    'directory'  => directory('runtime')
                )
            )
        ));

        $snapshot = $debug->handleException(new \ErrorException('Snapshot Test'), false);

        $this->assertNotEmpty($snapshot->getSnapshotFilename());
        $this->assertFileExists($snapshot->getSnapshotFilename());

        //But we still should be able to render it
        $this->assertNotEmpty($snapshot->renderSnapshot());

        $this->assertSame(
            $snapshot->renderSnapshot(),
            file_get_contents($snapshot->getSnapshotFilename())
        );

        //Should always contain exception name
        $this->assertTrue(strpos($snapshot->getSnapshotFilename(), 'ErrorException') !== false);
    }

    protected function debuggerComponent(array $config = array())
    {
        if (empty($config))
        {
            $config = array(
                'loggers'   => array(
                    'containers' => array()
                ),
                'backtrace' => array(
                    'view'      => 'spiral:exception',
                    'snapshots' => array(
                        'enabled'    => false,
                        'timeFormat' => 'd.m.Y-Hi.s',
                        'directory'  => directory('runtime')
                    )
                )
            );
        }

        return new Debugger(
            MemoryCore::getInstance()->setConfig('debug', $config)
        );
    }

    protected function viewManager(array $config = array())
    {
        if (empty($config))
        {
            $config = array(
                'namespaces'      => array(
                    'spiral' => array(
                        directory('framework') . '/views'
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
                        'compiler'   => 'Spiral\Components\View\LayeredCompiler',
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

    /**
     * Configured tokenizer component.
     *
     * @param array $config
     * @return Tokenizer
     * @throws \Spiral\Core\CoreException
     */
    protected function tokenizerComponent(array $config = array())
    {
        if (empty($config))
        {
            $config = array(
                'directories' => array(__DIR__),
                'exclude'     => array('XX')
            );
        }

        return new Tokenizer(
            MemoryCore::getInstance()->setConfig('tokenizer', $config),
            new FileManager(),
            $this->loader
        );
    }
}