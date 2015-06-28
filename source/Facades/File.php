<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Facades;

use Psr\Log\LoggerInterface;
use Spiral\Components\Files\FileManager;
use Spiral\Core\Facade;

class File extends Facade
{
    /**
     * Facade can statically represent methods of one binded component, such component alias or class
     * name should be defined in bindedComponent constant.
     */
    const COMPONENT = 'file';

    /**
     * Size constants for better size manipulations.
     */
    const KB = 1024;
    const MB = 1048576;
    const GB = 1073741824;

    /**
     * Default file permissions is 777 (directories 777), this files are fully writable and readable
     * by all application environments. Usually this files stored under application/data folder,
     * however they can be in some other public locations.
     */
    const RUNTIME = FileManager::RUNTIME;

    /**
     * Work files are files which create by or for framework, like controllers, configs and config
     * directories. This means that only CLI mode application can modify them. You should not create
     * work files from web application.
     */
    const READONLY = FileManager::READONLY;
}