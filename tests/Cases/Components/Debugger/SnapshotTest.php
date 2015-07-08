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
        Container::getInstance()->bind('view', $this->viewManager());
        Container::getInstance()->bind('tokenizer', $this->tokenizerComponent());
    }

    protected function tearDown()
    {
        Container::getInstance()->removeBinding('view');
        Container::getInstance()->removeBinding('tokenizer');

        $this->loader->disable();
        $this->loader = null;

        $file = new FileManager();
        foreach ($file->getFiles(directory('runtime')) as $filename)
        {
            $file->delete($filename);
        }
    }

    public function testMemorySnapshot()
    {
        $debug = $this->debuggerComponent();

        $snapshot = $debug->handleException(new \ErrorException('Snapshot Test'), false);

        $this->assertNull($snapshot->getFilename());

        //But we still should be able to render it
        $this->assertNotEmpty($snapshot->renderSnapshot());
    }

    public function testFileSnapshot()
    {
        $debug = $this->debuggerComponent([
            'loggers'   => [
                'containers' => []
            ],
            'backtrace' => [
                'view'      => 'spiral:exception',
                'snapshots' => [
                    'enabled'    => true,
                    'timeFormat' => 'd.m.Y-Hi.s',
                    'directory'  => directory('runtime')
                ]
            ]
        ]);

        $snapshot = $debug->handleException(new \ErrorException('Snapshot Test'), false);

        $this->assertNotEmpty($snapshot->getFilename());
        $this->assertFileExists($snapshot->getFilename());

        //But we still should be able to render it
        $this->assertNotEmpty($snapshot->renderSnapshot());

        $this->assertSame(
            $snapshot->renderSnapshot(),
            file_get_contents($snapshot->getFilename())
        );

        //Should always contain exception name
        $this->assertTrue(strpos($snapshot->getFilename(), 'ErrorException') !== false);
    }

    protected function debuggerComponent(array $config = [])
    {
        if (empty($config))
        {
            $config = [
                'loggers'   => [
                    'containers' => []
                ],
                'backtrace' => [
                    'view'      => 'spiral:exception',
                    'snapshots' => [
                        'enabled'    => false,
                        'timeFormat' => 'd.m.Y-Hi.s',
                        'directory'  => directory('runtime')
                    ]
                ]
            ];
        }

        return new Debugger(
            MemoryCore::getInstance()->setConfig('debug', $config)
        );
    }

    protected function viewManager(array $config = [])
    {
        if (empty($config))
        {
            $config = [
                'namespaces'      => [
                    'spiral' => [
                        directory('framework') . '/views'
                    ]
                ],
                'caching'         => [
                    'enabled'   => false,
                    'directory' => directory('runtime')
                ],
                'staticVariables' => [],
                'engines'         => [
                    'default' => [
                        'extensions' => ['php'],
                        'compiler'   => 'Spiral\Components\View\LayeredCompiler',
                        'view'       => 'Spiral\Components\View\View',
                        'processors' => []
                    ]
                ]
            ];
        }

        return new ViewManager(
            MemoryCore::getInstance()->setConfig('views', $config),
            MemoryCore::getInstance(),
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
    protected function tokenizerComponent(array $config = [])
    {
        if (empty($config))
        {
            $config = [
                'directories' => [__DIR__],
                'exclude'     => ['XX']
            ];
        }

        return new Tokenizer(
            MemoryCore::getInstance()->setConfig('tokenizer', $config),
            MemoryCore::getInstance(),
            new FileManager(),
            $this->loader
        );
    }
}