<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Views\Processors;

use Spiral\Support\Helpers\StringHelper;
use Spiral\Support\HtmlTokenizer;
use Spiral\Tokenizer\Isolator;
use Spiral\Views\Compiler;
use Spiral\Views\ProcessorInterface;
use Spiral\Views\ViewManager;

/**
 * Performs few simple operations to make output HTML look better.
 */
class PrettifyProcessor implements ProcessorInterface
{
    /**
     * Prettify-options.
     *
     * @var array
     */
    protected $options = [
        //Drop blank lines
        'endings'    => true,
        //Trim attributes
        'attributes' => [
            'normalize' => true,
            'trim'      => ['class', 'style', 'id'],
            'drop'      => ['class', 'style', 'id']
        ]
    ];

    /**
     * {@inheritdoc}
     */
    public function __construct(ViewManager $viewFacade, Compiler $compiler, array $options)
    {
        $this->options = $options + $this->options;
    }

    /**
     * {@inheritdoc}
     *
     * @param Isolator      $isolator
     * @param HtmlTokenizer $tokenizer
     */
    public function process($source, Isolator $isolator = null, HtmlTokenizer $tokenizer = null)
    {
        $isolator = !empty($isolator) ? $isolator : new Isolator();
        $tokenizer = !empty($tokenizer) ? $tokenizer : new HtmlTokenizer();

        if ($this->options['endings']) {
            $source = $this->normalizeEndings($source, $isolator);
        }

        if ($this->options['attributes']['normalize']) {
            $source = $this->normalizeAttributes($source, $tokenizer);
        }

        return $source;
    }

    /**
     * Remove blank lines.
     *
     * @param string   $source
     * @param Isolator $isolator
     * @return string
     */
    protected function normalizeEndings($source, Isolator $isolator)
    {
        //Step #1, \n only
        $source = $isolator->isolatePHP(StringHelper::normalizeEndings($source));

        //Step #2, chunk by lines
        $sourceLines = explode("\n", $source);

        //Step #3, no blank lines and html comments (will keep conditional commends)
        $sourceLines = array_filter($sourceLines, function ($line) {
            return trim($line);
        });

        $source = $isolator->repairPHP(join("\n", $sourceLines));
        $isolator->reset();

        return $source;
    }

    /**
     * Normalize attribute values.
     *
     * @param string        $source
     * @param HtmlTokenizer $tokenizer
     * @return mixed
     */
    protected function normalizeAttributes($source, HtmlTokenizer $tokenizer)
    {
        $result = '';
        foreach ($tokenizer->parse($source) as $token) {
            if (empty($token[HtmlTokenizer::TOKEN_ATTRIBUTES])) {
                $result .= $tokenizer->compile($token);
                continue;
            }

            $attributes = [];
            foreach ($token[HtmlTokenizer::TOKEN_ATTRIBUTES] as $attribute => $value) {
                if (in_array($attribute, $this->options['attributes']['trim'])) {
                    $value = trim($value);
                }

                if (empty($value) && in_array($attribute, $this->options['attributes']['drop'])) {
                    //Empty value
                    continue;
                }

                $attributes[$attribute] = $value;
            }

            $token[HtmlTokenizer::TOKEN_ATTRIBUTES] = $attributes;
            $result .= $tokenizer->compile($token);
        }

        return $result;
    }
}