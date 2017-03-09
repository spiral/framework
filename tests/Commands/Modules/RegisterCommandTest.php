<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Commands\Modules;

use Spiral\Core\ConfiguratorInterface;
use Spiral\Tests\BaseTest;
use Spiral\Views\Configs\ViewsConfig;
use Symfony\Component\Console\Input\ArrayInput;

class RegisterCommandTest extends BaseTest
{
    protected $configBackup = '';

    public function setUp()
    {
        parent::setUp();

        $this->configBackup = file_get_contents(directory('application') . 'config/views.php');
    }

    public function tearDown()
    {
        file_put_contents(directory('application') . 'config/views.php', $this->configBackup);

        parent::tearDown();
    }

//    public function testPublishUndefined()
//    {
//        $this->assertContains(
//            'Class \'Sample\ModuleModule\' is not valid module',
//            $this->commands->run('register', ['module' => 'sample/module'])->getOutput()->fetch()
//        );
//    }
//
//    public function testPublishInvalid()
//    {
//        $this->assertContains(
//            'Class \'TestApplication\TestModule\' is not valid module',
//            $this->commands->run('publish',
//                ['module' => 'test-application/test'])->getOutput()->fetch()
//        );
//    }
//
//    public function testRegisterEmpty()
//    {
//        $this->assertSame(
//            0,
//            $this->commands->run('register', ['module' => 'test-application/empty'])->getCode()
//        );
//    }

    public function testRegisterWithConfigsAndFiles()
    {
        $viewConfig = $this->container->get(ConfiguratorInterface::class)->getConfig('views');

        $input = new ArrayInput(['module' => 'test-application/profiler']);
        $input->setInteractive(false);

        $output = $this->commands->run(
            'register',
            $input
        );

        $this->assertSame(0, $output->getCode());

        clearstatcache();
        opcache_reset();
print_r(file_get_contents(directory('application') . 'config/views.php'));
        $this->container->get(ConfiguratorInterface::class)->flushCache();

        $this->assertNotSame(
            $viewConfig,
            $newConfig = $this->container->get(ConfiguratorInterface::class)->getConfig('views')
        );

        $this->assertArrayHasKey(
            'profiler',
            $this->container->get(ViewsConfig::class)->getNamespaces()
        );
    }

    /**
     * @expectedException \Spiral\Modules\Exceptions\RegistratorException
     * @expectedExceptionMessage Config syntax of 'views' does not valid after registrations
     */
    public function testRegisterInvalidConfig()
    {
        $input = new ArrayInput(['module' => 'test-application/invalid']);
        $input->setInteractive(false);

        $output = $this->commands->run('register', $input);
    }
}