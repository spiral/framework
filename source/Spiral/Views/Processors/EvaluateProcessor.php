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
     * @param string        $source
     * @param string        $cachedFilename
     * @param Isolator|null $isolator
     * @return string
     * @throws \ErrorException
     */
    public function process($source, $cachedFilename = null, Isolator $isolator = null)
    {
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
            $this->files->write(
                $filename,
                $this->prepareSource($source),
                FilesInterface::RUNTIME,
                true
            );

            ob_start();
            include_once $this->files->localUri($filename);
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
     * Mount set of constructions needed to simplify compilation block definitions. Basically this
     * method mount php sugar methods.
     *
     * @param string $source
     * @return string
     */
    protected function prepareSource($source)
    {

//        protected $expressions = [
//        //Create variable based on provided PHP code, will erase PHP braces and echo,
//        //this expression should be used only inside evaluator code, expression should be executed
//        //before Templater
//        'fetchVariable'  => [
//            'pattern'  => '/(?:(\/\/)\s*)?\$([a-z_][a-z_0-9]*(?:\[\])?)\s*=\s*fetchVariable\([\'"]([^\'"]+)[\'"]\)\s*;/i',
//            'callback' => ['self', 'fetchVariable']
//        ],
//        //Used to create php variable related to some php block, will always contain valid php code,
//        //this expressions should be used only in compiled php
//        'createVariable' => [
//            'pattern'  => '/(?:(\/\/)\s*)?createVariable\([\'"]([^\'"]+)[\'"]\s*,\s*[\'"]([^\'"]+)[\'"]\)\s*;/i',
//            'callback' => ['self', 'createVariable']
//        ]
//    ];

//        /**
//         * Export value or expressions of template block to evaluator variable which can be used to
//         * build php expressions.
//         *
//         * @param array $matches
//         * @return string
//         */
//        public function fetchVariable(array $matches)
//    {
//        if (!empty($matches[1])) {
//            return '//This code is commented';
//        }
//
//        return "ob_start(); >$matches[3]<?php #compile
//        \$$matches[2] = \$this->fetchPHP(\$isolator->repairPHP(trim(ob_get_clean())));";
//    }
//
//        /**
//         * Create php variable based on provided block.
//         *
//         * @param array $matches
//         * @return string
//         */
//        public function createVariable(array $matches)
//    {
//        if (!empty($matches[1])) {
//            return '//This code is commented';
//        }
//
//        return "ob_start(); >$matches[3]<?php #compile\n"
//        . "echo '<?php \$$matches[2] = ' . \$this->fetchPHP("
//        . "\$isolator->repairPHP(trim(ob_get_clean()))"
//        . ") . ';>';";
//    }

        return $source;
    }

    /**
     * Unique filename for evaluation.
     *
     * @param string $filename
     * @return string
     */
    private function evalFilename($filename)
    {
        return $filename . '.eval.' . spl_object_hash($this);
    }
}