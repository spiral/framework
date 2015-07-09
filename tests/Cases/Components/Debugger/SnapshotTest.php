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
use Spiral\Core\Configurator;
use Spiral\Core\Container;
use Spiral\Core\Loader;
use Spiral\Support\Tests\TestCase;
use Spiral\Tests\RuntimeCache;

class SnapshotTest extends TestCase
{
    /**
     * @var Loader
     */
    protected $loader = null;

    /**
     * @param array $config
     * @return ViewManager
     */
    protected function getViewManager(array $config = [])
    {
        if (empty($config))
        {
            $config = [
                'namespaces'   => [
                    'spiral' => [directory('framework') . '/views']
                ],
                'caching'      => [
                    'enabled'   => false,
                    'directory' => directory('runtime')
                ],
                'dependencies' => [],
                'engines'      => [
                    'default' => [
                        'extensions' => ['php'],
                        'compiler'   => null,
                        'view'       => 'Spiral\\Components\\View\\View'
                    ]
                ]
            ];
        }

        return new ViewManager(
            new Configurator(['views' => $config]),
            new Container(),
            new FileManager()
        );
    }

    /**
     * @param array $config
     * @return Tokenizer
     */
    protected function getTokenizer(array $config = [])
    {
        if (empty($config))
        {
            $config = [
                'directories' => [__DIR__],
                'exclude'     => ['XX']
            ];
        }

        return new Tokenizer(
            new Configurator(['tokenizer' => $config]),
            new RuntimeCache(),
            new FileManager(),
            $this->loader
        );
    }

    protected function getDebugger(array $config = [])
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

        return new Debugger(new Configurator(['debug' => $config]));
    }

    protected function setUp()
    {
        $this->loader = new Loader(new RuntimeCache());

        Container::getInstance()->bind('view', $this->getViewManager());
        Container::getInstance()->bind('tokenizer', $this->getTokenizer());
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
        $debug = $this->getDebugger();

        $snapshot = $debug->handleException(new \ErrorException('Snapshot Test'), false);

        $this->assertNull($snapshot->getSnaphotFilename());

        //But we still should be able to render it
        $this->assertNotEmpty($snapshot->renderSnapshot());
    }

    public function testFileSnapshot()
    {
        $debug = $this->getDebugger([
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

        $this->assertNotEmpty($snapshot->getSnaphotFilename());
        $this->assertFileExists($snapshot->getSnaphotFilename());

        //But we still should be able to render it
        $this->assertNotEmpty($snapshot->renderSnapshot());

        $this->assertSame(
            $snapshot->renderSnapshot(),
            file_get_contents($snapshot->getSnaphotFilename())
        );

        //Should always contain exception name
        $this->assertTrue(strpos($snapshot->getSnaphotFilename(), 'ErrorException') !== false);
    }
}