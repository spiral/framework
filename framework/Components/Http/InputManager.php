<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\Http;

use Spiral\Components\Files\FileManager;
use Spiral\Core\Component;
use Spiral\Core\Container;

class InputManager extends Component
{
    /**
     * Component is singleton.
     */
    use Component\SingletonTrait;

    /**
     * Declaring to IoC that component should be presented as singleton.
     */
    const SINGLETON = __CLASS__;

    /**
     * Container is required to resolve active instance of Request.
     *
     * @var Container
     */
    protected $container = null;

    /**
     * FileManager is required to create temporary files based on UploadedFileInterface.
     *
     * @var FileManager
     */
    protected $file = null;

    /**
     * Instance of InputManager. Input manager responsible for simplifying access to
     * ServerRequestInterface parameters such as data (post), query, cookies and etc. InputManager
     * additionally provides simplified fallback to create temporary files based on
     * UploadedFileInterface.
     *
     * @param Container   $container
     * @param FileManager $file
     */
    public function __construct(Container $container, FileManager $file)
    {
        $this->container = $container;
        $this->file = $file;
    }
}