<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Views\Processors;

use Spiral\Core\Component;
use Spiral\Core\ContainerInterface;
use Spiral\Files\FilesInterface;
use Spiral\Tokenizer\Isolator;
use Spiral\Views\ProcessorInterface;

/**
 * Evaluate processor can evaluate php blocks which contain specific flags at compilation phase.
 */
class EvaluateProcessor extends Component implements ProcessorInterface
{
    /**
     * @var array
     */
    protected $flags = [
        '/*compile*/',
        '#compile',
        '#php-compile'
    ];

    /**
     * @var string
     */
    protected $namespace = '';

    /**
     * @var string
     */
    protected $view = '';

    /**
     * @var FilesInterface
     */
    protected $files = null;

    /**
     * @invisible
     * @var ContainerInterface
     */
    protected $container = null;

    /**
     * @param array              $flags
     * @param FilesInterface     $files
     * @param ContainerInterface $container
     */
    public function __construct(
        array $flags = [],
        FilesInterface $files,
        ContainerInterface $container
    ) {
        if (!empty($flags)) {
            $this->flags = $flags;
        }

        $this->files = $files;
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     *
     * @param Isolator|null $isolator
     */
    public function process(
        $source,
        $namespace,
        $view,
        $cachedFilename = null,
        Isolator $isolator = null
    ) {
        $isolator = !empty($isolator) ? $isolator : new Isolator();

        //Let's hide original php blocks
        $source = $isolator->isolatePHP($source);

        //Now we have to detect blocks which has to be compiled
        //Restoring only evaluator blocks
        $phpBlocks = $evaluateBlocks = [];

        foreach ($isolator->getBlocks() as $id => $phpBlock) {
            if ($this->evaluatable($phpBlock)) {
                $evaluateBlocks[$id] = $phpBlock;
                continue;
            }

            $phpBlocks[$id] = $phpBlock;
        }

        //Let's only mount blocks to be evaluated
        $source = $isolator->setBlocks($evaluateBlocks)->repairPHP($source);
        $isolator->setBlocks($phpBlocks);

        //Let's create temporary filename
        $filename = $this->evalFilename($cachedFilename);

        //I must validate file syntax in a future

        try {
            $this->files->write($filename, $source, FilesInterface::RUNTIME, true);

            ob_start();
            $__outputLevel__ = ob_get_level();

            //Can be accessed in evaluated php
            $this->namespace = $namespace;
            $this->view = $view;

            try {
                include_once $this->files->localUri($filename);
            } finally {
                while (ob_get_level() > $__outputLevel__) {
                    ob_end_clean();
                }
            }

            $this->namespace = '';
            $this->view = '';

            $source = ob_get_clean();

            //Dropping temporary filename
            $this->files->delete($filename);
        } catch (\ErrorException $exception) {
            throw $exception;
        }

        //Restoring original php blocks
        return $isolator->repairPHP($source);
    }

    /**
     * Extract php source from php block (no echos). Used to convert php blocks provided by
     * templater to local variables.
     *
     * Function available in evaluated blocks using $this->fetchPHP($block);
     *
     * @param string $phpBlock
     * @return string
     */
    public function fetchPHP($phpBlock)
    {
        if (strpos($phpBlock, '<?') !== 0) {
            return var_export($phpBlock, true);
        }

        $phpBlock = substr(trim($phpBlock), 2, -2);
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

    /**
     * Check if php block has to be compiled.
     *
     * @param string $block
     * @return bool
     */
    protected function evaluatable($block)
    {
        foreach ($this->flags as $flag) {
            if (strpos($block, $flag) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Unique filename for evaluation.
     *
     * @param string $filename
     * @return string
     */
    private function evalFilename($filename)
    {
        return $filename . '.eval.' . spl_object_hash($this) . '.php';
    }
}