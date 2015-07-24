<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\View\Compiler\Processors;

use Spiral\Components\Tokenizer\Isolator;
use Spiral\Components\View\Compiler\Compiler;
use Spiral\Components\View\Compiler\ProcessorInterface;
use Spiral\Components\View\ViewManager;
use Spiral\Helpers\StringHelper;
use Spiral\Support\Html\Tokenizer;

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
     * New processors instance with options specified in view config. I wrote this processor just for
     * fun, there is no real need in it.
     *
     * @param ViewManager $viewManager
     * @param Compiler    $compiler SpiralCompiler instance.
     * @param array       $options
     */
    public function __construct(ViewManager $viewManager, Compiler $compiler, array $options)
    {
        $this->options = $options + $this->options;
    }

    /**
     * Performs view code pre-processing. LayeredCompiler will provide view source into processors,
     * processors can perform any source manipulations using this code expect final rendering.
     *
     * @param string    $source View source (code).
     * @param Isolator  $isolator
     * @param Tokenizer $tokenizer
     * @return string
     * @throws \ErrorException
     */
    public function process($source, Isolator $isolator = null, Tokenizer $tokenizer = null)
    {
        $isolator = !empty($isolator) ? $isolator : new Isolator();
        $tokenizer = !empty($tokenizer) ? $tokenizer : new Tokenizer();

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

        return $isolator->repairPHP(join("\n", $sourceLines));
    }

    /**
     * Normalize attribute values.
     *
     * @param string    $source
     * @param Tokenizer $tokenizer
     * @return mixed
     */
    protected function normalizeAttributes($source, Tokenizer $tokenizer)
    {
        $result = '';
        foreach ($tokenizer->parse($source) as $token)
        {
            if (in_array($token[Tokenizer::TOKEN_TYPE], [Tokenizer::PLAIN_TEXT, Tokenizer::TAG_CLOSE]))
            {
                $result .= $token[Tokenizer::TOKEN_CONTENT];
                continue;
            }

            if (empty($token[Tokenizer::TOKEN_ATTRIBUTES]))
            {
                $result .= $token[Tokenizer::TOKEN_CONTENT];
                continue;
            }

            $tokenContent = $token[Tokenizer::TOKEN_NAME];

            $attributes = [];
            foreach ($token[Tokenizer::TOKEN_ATTRIBUTES] as $attribute => $value)
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

            if ($token[Tokenizer::TOKEN_TYPE] == Tokenizer::TAG_SHORT)
            {
                $tokenContent .= '/';
            }

            $result .= '<' . $tokenContent . '>';
        }

        return $result;
    }
}