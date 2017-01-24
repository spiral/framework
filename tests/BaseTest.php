<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests;

use Spiral\Core\Traits\SharedTrait;
use Spiral\Tests\Core\Fixtures\SharedComponent;

/**
 * @property \Spiral\Core\MemoryInterface             $memory
 * @property \Spiral\Core\ContainerInterface          $container
 * @property \Spiral\Debug\LogsInterface              $logs
 * @property \Spiral\Http\HttpDispatcher              $http
 * @property \Spiral\Console\ConsoleDispatcher        $console
 * @property \Spiral\Files\FilesInterface             $files
 * @property \Spiral\Tokenizer\TokenizerInterface     $tokenizer
 * @property \Spiral\Tokenizer\ClassesInterface       $locator
 * @property \Spiral\Tokenizer\InvocationsInterface   $invocationLocator
 * @property \Spiral\Storage\StorageInterface         $storage
 * @property \Spiral\Views\ViewManager                $views
 * @property \Spiral\Translator\Translator            $translator
 * @property \Spiral\Database\DatabaseManager         $dbal
 * @property \Spiral\ORM\ORM                          $orm
 * @property \Spiral\ODM\ODM                          $odm
 * @property \Spiral\Encrypter\EncrypterInterface     $encrypter
 * @property \Spiral\Database\Entities\Database       $db
 * @property \Spiral\ODM\Entities\MongoDatabase       $mongo
 * @property \Spiral\Http\Cookies\CookieQueue         $cookies
 * @property \Spiral\Http\Routing\RouterInterface     $router
 * @property \Spiral\Pagination\PaginatorsInterface   $paginators
 * @property \Psr\Http\Message\ServerRequestInterface $request
 * @property \Spiral\Http\Request\InputManager        $input
 * @property \Spiral\Http\Response\ResponseWrapper    $response
 * @property \Spiral\Http\Routing\RouteInterface      $route
 * @property \Spiral\Security\PermissionsInterface    $permissions
 * @property \Spiral\Security\RulesInterface          $rules
 * @property \Spiral\Security\ActorInterface          $actor
 */
abstract class BaseTest extends \PHPUnit_Framework_TestCase
{
    use SharedTrait;

    /**
     * @var TestApplication
     */
    protected $app;

    public function setUp()
    {
        $root = __DIR__ . '/-app-/';
        $this->app = TestApplication::init([
            'root'        => $root,
            'libraries'   => dirname(__DIR__) . '/vendor/',
            'application' => $root,
            'framework'   => dirname(__DIR__) . '/source/',
            'runtime'     => $root . 'runtime/',
            'cache'       => $root . 'runtime/cache/',
        ]);

        //Open application scope
        SharedComponent::shareContainer($this->app->container);
    }

    public function tearDown()
    {
        $this->db->getDriver()->disconnect();

        $files = $this->app->files;
        foreach ($files->getFiles(directory('runtime')) as $filename) {
            $files->delete($filename);
        }

        //Close scope
        SharedComponent::shareContainer(null);
    }

    /**
     * @return \Spiral\Core\ContainerInterface
     */
    protected function iocContainer()
    {
        return $this->app->container;
    }
}