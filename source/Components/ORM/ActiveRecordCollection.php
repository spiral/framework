<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\ORM;

use Spiral\Core\Component;

class ActiveRecordCollection extends Component
{
    protected $entities = [];

    protected $class = '';

    public function __construct(array $data, $class)
    {
        $this->entities = $data;
        $this->class = $class;
    }
}