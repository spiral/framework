<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\View;

use Spiral\Core\Component;
use Spiral\Core\Container;

class View extends Component implements ViewInterface
{
    /**
     * View filename, usually cached.
     *
     * @var string
     */
    protected $filename = '';

    /**
     * View namespace.
     *
     * @var string
     */
    protected $namespace = '';

    /**
     * View name.
     *
     * @var string
     */
    protected $view = '';

    /**
     * Runtime data has to be passed to view.
     *
     * @var array
     */
    protected $data = [];

    /**
     * View instance binded to specified view file (file has to be already pre-processed).
     *
     * @param ViewManager $manager    ViewManager component.
     * @param string      $filename   Compiled view file.
     * @param string      $namespace  View namespace.
     * @param string      $view       View name.
     * @param array       $data       Runtime data passed by controller or model, should be injected
     *                                into view.
     */
    public function __construct(
        ViewManager $manager,
        $filename,
        $namespace = '',
        $view = '',
        array $data = []
    )
    {
        $this->filename = $filename;
        $this->namespace = $namespace;
        $this->view = $view;
        $this->data = $data;
    }

    /**
     * New instance of view class.
     *
     * Example:
     * View::make('namespace:view');
     * View::make('namespace:view', ['name' => 'value']);
     *
     * @param array     $parameters
     * @param Container $container
     * @return mixed|static
     */
    public static function make($parameters = [], Container $container = null)
    {
        if (empty($container))
        {
            $container = Container::getInstance();
        }

        if (is_string($parameters))
        {
            return call_user_func_array(
                [ViewManager::getInstance($container), 'get'], func_get_args()
            );
        }

        return parent::make($parameters, $container);
    }

    /**
     * Perform view file rendering. View data has to be associated array and will be exported using
     * extract() function and set of local view variables, here variable name will be identical to
     * array key.
     *
     * Every view file will be pro-processed using view processors (also defined in view config) before
     * rendering, result of pre-processing will be stored in names cache file to speed-up future
     * renderings.
     *
     * @return string
     */
    public function render()
    {
        !empty($this->view) && benchmark('view::render', $this->namespace . ':' . $this->view);
        ob_start();

        extract($this->data, EXTR_OVERWRITE);
        include $this->filename;
        $result = ob_get_clean();

        !empty($this->view) && benchmark('view::render', $this->namespace . ':' . $this->view);

        return $result;
    }

    /**
     * Alias for render method.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }
}