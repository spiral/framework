<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Views\Processors;

use Spiral\Stempler\HtmlTokenizer;
use Spiral\Support\Strings;
use Spiral\Tokenizer\Isolator;
use Spiral\Views\EnvironmentInterface;
use Spiral\Views\ProcessorInterface;
use Spiral\Views\SourceContextInterface;
use Spiral\Views\ViewSource;

/**
 * Cuts blank lines in template html code and normalize attrbiutes.
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
            //Drop spaces
            'trim'      => ['class', 'style', 'id'],

            //Drop when empty
            'drop'      => ['class', 'style', 'id']
        ]
    ];

    /**
     * @var HtmlTokenizer
     */
    protected $tokenizer = null;

    /**
     * @param HtmlTokenizer $tokenizer
     * @param array         $options
     */
    public function __construct(HtmlTokenizer $tokenizer, array $options = [])
    {
        $this->tokenizer = $tokenizer;
        $this->options = $options + $this->options;
    }

    /**
     * {@inheritdoc}
     */
    public function modify(
        EnvironmentInterface $environment,
        ViewSource $view,
        string $code
    ): string {
        if ($this->options['endings']) {
            $view = $this->normalizeEndings($code, new Isolator());
        }

        if ($this->options['attributes']['normalize']) {
            $view = $this->normalizeAttributes($code, $this->tokenizer);
        }

        return $view;
    }

    /**
     * Remove blank lines.
     *
     * @param string   $source
     * @param Isolator $isolator
     *
     * @return string
     */
    protected function normalizeEndings($source, Isolator $isolator)
    {
        //Step #1, \n only
        $source = $isolator->isolatePHP(Strings::normalizeEndings($source));

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
     *
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