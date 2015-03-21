<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\View;

use Spiral\Core\CoreException;

class ViewException extends CoreException
{
    /**
     * Set exception location. Can be used to force exception caused by invalid view syntax inside
     * view processors.
     *
     * @param string $file
     * @param int    $line
     */
    public function setLocation($file, $line)
    {
        $this->file = $file;
        $this->line = $line;
    }
}