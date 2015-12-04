<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Reactor\Traits;


trait UsesTrait
{
    private $uses = [];

    public function hasUses()
    {
        return true;
    }

    private function renderUses($indentLevel = 0)
    {

    }
}