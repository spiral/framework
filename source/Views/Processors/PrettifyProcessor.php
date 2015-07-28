<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Views\Processors;

use Spiral\Helpers\HtmlTokenizer;
use Spiral\Helpers\StringHelper;
use Spiral\Tokenizer\Isolator;
use Spiral\Views\Compiler\Compiler;
use Spiral\Views\Compiler\ProcessorInterface;
use Spiral\Views\ViewsInterface;

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
     * New processors instance with options specified in view config.
     *
     * @param ViewsInterface $viewFacade
     * @param Compiler       $compiler SpiralCompiler instance.
     * @param array          $options
     */
    public function __construct(ViewsInterface $viewFacade, Compiler $compiler, array $options)
    {
        $this->options = $options + $this->options;
    }

    /**
     * Performs view code pre-processing. LayeredCompiler will provide view source into processors,
     * processors can perform any source manipulations using this code expect final rendering.
     *
     * @param string   $source View source (code).
     * @param Isolator $isolator
     * @param HtmlTokenizer $tokenizer
     * @return string
     * @throws \ErrorException
     */
    public function process($source, Isolator $isolator = null, HtmlTokenizer $tokenizer = null)
    {
        $isolator = !empty($isolator) ? $isolator : new Isolator();
        $tokenizer = !empty($tokenizer) ? $tokenizer : new HtmlTokenizer();

        if ($this->options['endings'])
        {
            $source = $this->normalizeEndings($source, $isolator);
        }

        if ($this->options['attributes']['normalize'])
        {
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
        $sourceLines = array_filter($sourceLines, function ($line)
        {
            return trim($line);
        });

        $source = $isolator->repairPHP(join("\n", $sourceLines));
        $isolator->reset();

        return $source;
    }

    /**
     * Normalize attribute values.
     *
     * @param string $source
     * @param HtmlTokenizer $tokenizer
     * @return mixed
     */
    protected function normalizeAttributes($source, HtmlTokenizer $tokenizer)
    {
        $result = '';
        foreach ($tokenizer->parse($source) as $token)
        {
            if (in_array($token[HtmlTokenizer::TOKEN_TYPE], [HtmlTokenizer::PLAIN_TEXT, HtmlTokenizer::TAG_CLOSE]))
            {
                $result .= $token[HtmlTokenizer::TOKEN_CONTENT];
                continue;
            }

            if (empty($token[HtmlTokenizer::TOKEN_ATTRIBUTES]))
            {
                $result .= $token[HtmlTokenizer::TOKEN_CONTENT];
                continue;
            }

            $tokenContent = $token[HtmlTokenizer::TOKEN_NAME];

            $attributes = [];
            foreach ($token[HtmlTokenizer::TOKEN_ATTRIBUTES] as $attribute => $value)
            {
                if (in_array($attribute, $this->options['attributes']['trim']))
                {
                    $value = trim($value);
                }

                if (empty($value) && in_array($attribute, $this->options['attributes']['drop']))
                {
                    //Empty value
                    continue;
                }

                if ($value === null)
                {
                    $attributes[] = $attribute;
                    continue;
                }

                $attributes[] = $attribute . '="' . $value . '"';
            }

            if ($attributes)
            {
                $tokenContent .= ' ' . join(' ', $attributes);
            }

            if ($token[HtmlTokenizer::TOKEN_TYPE] == HtmlTokenizer::TAG_SHORT)
            {
                $tokenContent .= '/';
            }

            $result .= '<' . $tokenContent . '>';
        }

        return $result;
    }
}