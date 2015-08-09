<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Views\Processors;

use Spiral\Views\Compiler;
use Spiral\Views\ProcessorInterface;
use Spiral\Views\ViewManager;

/**
 * Provides set of expression replacements in view source, every expression specified as regex
 * pattern and replace callback.
 */
class ExpressionsProcessor implements ProcessorInterface
{
    /**
     * @var array
     */
    protected $expressions = [
        //Export value of view dependency by it's name
        'dependency'     => [
            'pattern'  => '/@\\{(?P<name>[a-z0-9_\\.\\-]+)(?: *\\| *(?P<default>[^}]+))?}/i',
            'callback' => ['self', 'dependency']
        ],
        //Create variable based on provided PHP code, will erase PHP braces and echo,
        //this expression should be used only inside evaluator code, expression should be executed
        //before Templater
        'fetchVariable'  => [
            'pattern'  => '/(?:(\/\/)\s*)?\$([a-z_][a-z_0-9]*)\s*=\s*fetchVariable\([\'"]([^\'"]+)[\'"]\)\s*;/i',
            'callback' => ['self', 'fetchVariable']
        ],
        //Used to create php variable related to some php block, will always contain valid php code,
        //this expressions should be used only in compiled php
        'createVariable' => [
            'pattern'  => '/(?:(\/\/)\s*)?createVariable\([\'"]([^\'"]+)[\'"]\s*,\s*[\'"]([^\'"]+)[\'"]\)\s*;/i',
            'callback' => ['self', 'createVariable']
        ]
    ];

    /**
     * {@inheritdoc}
     */
    public function __construct(ViewManager $views, Compiler $compiler, array $options)
    {
        $this->viewManager = $views;

        if (!empty($options['expressions'])) {
            $this->expressions = $options['expressions'] + $this->expressions;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function process($source)
    {
        foreach ($this->expressions as $expression) {
            $source = preg_replace_callback(
                $expression['pattern'],
                $expression['callback'],
                $source
            );
        }

        return $source;
    }

    /**
     * Mount view dependency value.
     *
     * @param array $matches
     * @return string
     */
    public function dependency(array $matches)
    {
        return $this->viewManager->getDependency(
            $matches['name'],
            !empty($matches['default']) ? $matches['default'] : ''
        );
    }

    /**
     * Export value or expressions of template block to evaluator variable which can be used to build
     * php expressions.
     *
     * @param array $matches
     * @return string
     */
    public function fetchVariable(array $matches)
    {
        if (!empty($matches[1])) {
            return '//This code is commented';
        }

        return "ob_start(); ?>$matches[3]<?php #compile
        \$$matches[2] = \$this->fetchPHP(\$isolator->repairPHP(trim(ob_get_clean())));";
    }

    /**
     * Create php variable based on provided block.
     *
     * @param array $matches
     * @return string
     */
    public function createVariable(array $matches)
    {
        if (!empty($matches[1])) {
            return '//This code is commented';
        }

        return "ob_start(); ?>$matches[3]<?php #compile\n"
        . "echo '<?php \$$matches[2] = ' . \$this->fetchPHP("
        . "\$isolator->repairPHP(trim(ob_get_clean()))"
        . ") . '; ?>';";
    }
}