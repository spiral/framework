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

class View extends Component
{
    protected $filename = '';
    protected $data = array();

    protected $namespace = '';
    protected $view = '';

    /**
     *
     * @param       $namespace
     * @param       $view
     * @param       $filename
     * @param array $data
     */
    public function __construct($filename, array $data = array(), $namespace = '', $view = '')
    {
        $this->namespace = $namespace;
        $this->view = $view;
        $this->filename = $filename;
        $this->data = $data;
    }

    public static function make($parameters = array())
    {
        if (is_string($parameters))
        {
            return call_user_func_array(array(ViewManager::getInstance(), 'get'), func_get_args());
        }

        return parent::make($parameters);
    }

    public function render()
    {
        benchmark('view::render', $this->namespace . ':' . $this->view);
        ob_start();

        extract($this->data, EXTR_OVERWRITE);
        include $this->filename;
        $result = ob_get_clean();

        benchmark('view::render', $this->namespace . ':' . $this->view);

        return $result;
    }

    public function __toString()
    {
        return $this->render();
    }
}