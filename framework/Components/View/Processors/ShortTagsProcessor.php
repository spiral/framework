<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\View\Processors;

use Spiral\Components\Tokenizer\Isolator;
use Spiral\Components\View\ProcessorInterface;
use Spiral\Components\View\ViewManager;

class ShortTagsProcessor implements ProcessorInterface
{
    /**
     * PHP Blocks isolator.
     *
     * @var Isolator
     */
    protected $isolator = null;

    /**
     * New processors instance with options specified in view config.
     *
     * @param array    $options
     * @param ViewManager     $view View component instance (if presented).
     * @param Isolator $isolator
     */
    public function __construct(array $options, ViewManager $view = null, Isolator $isolator = null)
    {
        $this->isolator = $isolator;
    }

    /**
     * Will convert short php tags to their longer representation, this will allow spiral views work
     * even in environment with disabled short_tag_open.
     *
     * @param string $source    View source (code).
     * @param string $view      View name.
     * @param string $namespace View namespace.
     * @return string
     */
    public function processSource($source, $view, $namespace)
    {
        //All blocks will be isolated at this moment
        $source = $this->isolator->shortTags(true)->isolatePHP($source);

        $phpBlocks = $this->isolator->getBlocks();

        foreach ($phpBlocks as &$phpBlock)
        {
            if (substr($phpBlock, 0, 3) == '<?=')
            {
                $phpBlock = '<?php echo ' . ltrim(substr($phpBlock, 3));
            }

            if (preg_match('/^<\?(?!php)/', $phpBlock))
            {
                $phpBlock = '<?php ' . substr($phpBlock, 2);
            }

            unset($phpBlock);
        }

        //Restoring php blocks after their repairing
        return $this->isolator->setBlocks($phpBlocks)->repairPHP($source);
    }
}