<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\DBAL;

interface ParameterInterface extends SqlFragmentInterface
{
    /**
     * Get parameter value, this method will be called by driver at moment of sending parameters to
     * PDO. Method is required as some parameters contain array value which should be presented as
     * multiple query bindings.
     *
     * @return mixed
     */
    public function getValue();
}