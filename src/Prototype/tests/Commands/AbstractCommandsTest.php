<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Prototype\Commands;

use PHPUnit\Framework\TestCase;
use Spiral\Tests\Prototype\Fixtures\TestApp;
use Spiral\Tests\Prototype\Storage;

abstract class AbstractCommandsTest extends TestCase
{
    protected const STORE = [
        'TestClass.php',
        'TestEmptyClass.php',
        'ChildClass.php',
        'ChildWithConstructorClass.php',
        'WithConstructor.php',
        'OptionalConstructorArgsClass.php'
    ];

    /** @var TestApp */
    protected $app;

    /** @var array */
    protected $buf = [];
    /**
     * @var Storage
     */
    private $storage;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        $this->storage = new Storage($this->dir() . '/Fixtures/');
        parent::__construct($name, $data, $dataName);
    }

    public function setUp(): void
    {
        $this->app = TestApp::init([
            'root'   => $this->dir(),
            'config' => $this->dir(),
            'app'    => $this->dir(),
            'cache'  => sys_get_temp_dir()
        ], null, false);

        foreach (static::STORE as $name) {
            $this->storage->store($name);
        }
    }

    public function tearDown(): void
    {
        foreach (static::STORE as $name) {
            $this->storage->restore($name);
        }
    }

    private function dir(): string
    {
        return dirname(__DIR__);
    }
}
