<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Views\Processors;

use Spiral\Core\Container\SaturableInterface;
use Spiral\Files\FilesInterface;
use Spiral\Tokenizer\Isolator;
use Spiral\Views\Compiler;
use Spiral\Views\ProcessorInterface;
use Spiral\Views\ViewManager;

/**
 * Evaluates php blocks marked with compilation flag at moment of view code compilation. This processor
 * is required for spiral toolkit.
 */
class EvaluateProcessor implements ProcessorInterface, SaturableInterface
{
    /**
     * @var ViewManager
     */
    protected $views = null;

    /**
     * @var Compiler
     */
    protected $compiler = null;

    /**
     * @var FilesInterface
     */
    protected $files = null;

    /**
     * @var array
     */
    protected $options = [
        'flags' => [
            '/*compile*/',
            '#compile',
            '#php-compile'
        ]
    ];

    /**
     * {@inheritdoc}
     */
    public function __construct(ViewManager $views, Compiler $compiler, array $options)
    {
        $this->views = $views;
        $this->compiler = $compiler;

        $this->options = $options + $this->options;
    }

    /**
     * @param FilesInterface $files
     */
    public function init(FilesInterface $files)
    {
        $this->files = $files;
    }

    /**
     * {@inheritdoc}
     *
     * @param Isolator $isolator Custom PHP isolator instance.
     */
    public function process($source, Isolator $isolator = null)
    {
        $isolator = !empty($isolator) ? $isolator : new Isolator();

        //Real php source code isolation
        $source = $isolator->isolatePHP($source);

        //Restoring only evaluator blocks
        $phpBlocks = $evaluateBlocks = [];

        foreach ($isolator->getBlocks() as $id => $phpBlock) {
            foreach ($this->options['flags'] as $flag) {
                if (strpos($phpBlock, $flag) !== false) {
                    $evaluateBlocks[$id] = $phpBlock;
                    continue 2;
                }
            }

            $phpBlocks[$id] = $phpBlock;
        }

        $source = $isolator->setBlocks($evaluateBlocks)->repairPHP($source);
        $isolator->setBlocks($phpBlocks);

        //Required to prevent collisions
        $filename = directory('cache') . "/{$this->uniqueID()}.php";

        try {
            $this->files->write($filename, $source, FilesInterface::RUNTIME, true);

            ob_start();
            require_once $filename;
            $source = ob_get_clean();

            $this->files->delete($filename);
        } catch (\ErrorException $exception) {
            throw $exception;
        }

        return $isolator->repairPHP($source);
    }

    /**
     * Get evaluation unique id, requried to prevent collisions.
     *
     * @return string
     */
    protected function uniqueID()
    {
        return spl_object_hash($this) . '-' . md5($this->compiler->compiledFilename());
    }

    /**
     * Extract php source from php block (no echos). Used to convert php blocks provided by templater
     * to local variables.
     *
     * @param string $phpBlock
     * @return string
     */
    static public function fetchPHP($phpBlock)
    {
        if (strpos($phpBlock, '<?') !== 0) {
            return var_export($phpBlock, true);
        }

        $phpBlock = trim(substr($phpBlock, 2, -2));
        if (substr($phpBlock, 0, 3) == 'php') {
            $phpBlock = trim(substr($phpBlock, 3));
        }

        if (substr($phpBlock, 0, 1) == '=') {
            $phpBlock = substr($phpBlock, 1);
        }

        if (substr($phpBlock, 0, 4) == 'echo') {
            $phpBlock = substr($phpBlock, 4);
        }

        return trim($phpBlock, '; ');
    }
}