<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Tests\Cases\View;

use Spiral\Support\Tests\TestCase;

class TemplaterTest extends TestCase
{

    protected function viewComponent(array $config = array())
    {
        if (empty($config))
        {
            $config = array(
                'namespaces'        => array(
                    'default'   => array(
                        __DIR__ . '/fixtures/default/',
                        __DIR__ . '/fixtures/default-b/',
                    ),
                    'namespace' => array(
                        __DIR__ . '/fixtures/namespace/',
                    )
                ),
                'caching'           => array(
                    'enabled'   => false,
                    'directory' => sys_get_temp_dir()
                ),
                'variableProviders' => array(),
                'processors'        => array()
            );
        }

        return new View(
            MemoryCore::getInstance()->setConfig('views', $config),
            new FileManager()
        );
    }
}