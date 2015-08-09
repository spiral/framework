<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Support\Generators\Config;

use Spiral\Components\Files\FileManager;
use Spiral\Components\Tokenizer\Tokenizer;
use Spiral\Core\Component;
use Spiral\Modules\Exceptions\ConfigWriterException;

class ConfigWriter extends Component
{

    /**
     * Methods will be applied to merge existed and custom configuration data in merge method is
     * specified as Config::MERGE_CUSTOM. This method usually used to perform logical merge.
     *
     * @param mixed $internal Requested configuration data.
     * @param mixed $existed  Existed configuration data.
     * @return mixed
     * @throws ConfigWriterException
     */
    protected function customMerge($internal, $existed)
    {
        throw new ConfigWriterException("No merging function defined.");
    }
}