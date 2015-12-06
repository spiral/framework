<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Modules\Entities;

use Spiral\Core\Component;
use Spiral\Modules\RegistratorInterface;

/**
 * Provides ability to modify existed configuration files and inject specific set of lines.
 *
 * All altered config files has to be checked for valid syntax and validation using associated
 * config class before saving.
 */
class Registrator extends Component implements RegistratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function configure($config, $placeholder, $wrapper, array $lines)
    {
        dump(func_get_args());
    }

    public function save()
    {

    }
}