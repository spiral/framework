<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Views\Processors;

use Spiral\Views\EnvironmentInterface;
use Spiral\Views\ProcessorInterface;
use Spiral\Views\ViewSource;

/**
 * Set of helper expressions for Evaluate processor.
 *
 * Generate evaluator variable based on stempler block:
 * <?php #compile
 *      compileVariable("name", "${stempler-block}");
 * ?>
 *
 * Generate runtime php variable based on stempler block:
 * <?php #compile
 *      runtimeVariable("name", "${stempler-block}");
 * ?>
 *
 * Required for Toolkit component in order to help view designers pass variables thought html
 * attributes.
 *
 * @see EvaluateProcessor
 */
class ExpressionsProcessors implements ProcessorInterface
{
    /**
     * Set of expressions to be replaced in view source.
     */
    const EXPRESSIONS = [
        'evaluatorVariable' => [
            'pattern'  => '/(?:(\/\/)\s*)?\$this->evaluatorVariable\([\'"]([^\'"]+)[\'"]\s*,\s*[\'"]([^\'"]+)[\'"]\)\s*;/i',
            'callback' => ['self', 'evaluatorVariable']
        ],
        'runtimeVariable'   => [
            'pattern'  => '/(?:(\/\/)\s*)?\$this->runtimeVariable\([\'"]([^\'"]+)[\'"]\s*,\s*[\'"]([^\'"]+)[\'"]\)\s*;/i',
            'callback' => ['self', 'runtimeVariable']
        ]
    ];

    /**
     * {@inheritdoc}
     */
    public function modify(
        EnvironmentInterface $environment,
        ViewSource $view,
        string $code
    ): string {
        foreach (static::EXPRESSIONS as $expression) {
            $code = preg_replace_callback(
                $expression['pattern'],
                $expression['callback'],
                $code
            );
        }

        return $code;
    }

    /**
     * Create variable to be used in compiled php code based on some stempler block.
     *
     * @see EvaluateProcessor
     *
     * @param array $matches
     *
     * @return string
     */
    protected function evaluatorVariable(array $matches): string
    {
        if (!empty($matches[1])) {
            return '//This code is commented';
        }

        return $this->extractCode($matches[2], $matches[3]);
    }

    /**
     * Create variable to be used in runtime php code based on some stempler block.
     *
     * @see EvaluateProcessor
     *
     * @param array $matches
     *
     * @return string
     */
    protected function runtimeVariable(array $matches): string
    {
        if (!empty($matches[1])) {
            return '//This code is commented';
        }

        //We need unique temporary variable
        $tempVariable = 'evaluator_' . str_replace('.', '_', uniqid('', true));

        $lines = [
            $this->extractCode($tempVariable, $matches[3]),
            //This will generate runtime code
            "echo '<?php \${$matches[2]} = ', \${$tempVariable}, '; ?>';"
        ];

        return join("\n", $lines);
    }

    /**
     * Generate code which needed to extract value of given block. Can be injected ONLY inside
     * evaluation block.
     *
     * @param string $variable Variable to store extracted code.
     * @param string $block
     *
     * @return string
     */
    private function extractCode(string $variable, string $block): string
    {
        $lines = [
            "//~ Extracting code into variable '{$variable}'",
            "ob_start();?>{$block}<?php #compile",
            "\${$variable} = \$this->fetchPHP(\$isolator->repairPHP(trim(ob_get_clean())));",
            "//~ End of variable '{$variable}' extracting"
        ];

        return join("\n", $lines);
    }
}