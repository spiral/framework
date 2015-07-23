<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Support\Html;

use Spiral\Components\Files\FileManager;
use Spiral\Components\Tokenizer\Isolator;
use Spiral\Core\Component;

class Tokenizer extends Component
{
    /**
     * Current tokenizer position. Tokenizer is a linear processor (no regular expression is involved).
     * This slows it down, but the results are much more reliable. Please don't forget that this is
     * tokenizer, not parser.
     */
    const POSITION_PLAIN_TEXT = 0x001;
    const POSITION_IN_TAG     = 0x002;
    const POSITION_IN_QUOTAS  = 0x003;

    /**
     * Token types detected and processed by tokenizer.
     */
    const PLAIN_TEXT = 'plain';
    const TAG_OPEN   = 'open';
    const TAG_CLOSE  = 'close';
    const TAG_SHORT  = 'short';

    /**
     * Token fields. There are a lot of tokens in HTML (up to 10,000 different ones). It is better to
     * use numeric keys for array than any text fields or even objects.
     */
    const TOKEN_NAME       = 0;
    const TOKEN_TYPE       = 1;
    const TOKEN_CONTENT    = 2;
    const TOKEN_ATTRIBUTES = 3;

    /**
     * Array of parsed tokens. Every token has fields name, type, content and arguments.
     *
     * @var array
     */
    protected $tokens = [];

    /**
     * PHP block should be isolated while parsing, Keep enabled.
     *
     * @var bool
     */
    protected $isolatePHP = false;

    /**
     * PHP Blocks isolator, which holds all existing PHP blocks and restores them in output.
     *
     * @var Isolator|null
     */
    protected $isolator = null;

    /**
     * New HTML tokenizer object. Tokenizer is linear processor (no regular expression is involved),
     * This can slow it down but the results are much more reliable. Please don't forget this is
     * tokenizer, not parser.
     *
     * @param bool $isolatePHP PHP block should be isolated and enabled by default
     */
    public function __construct($isolatePHP = true)
    {
        $this->isolatePHP = $isolatePHP;
        $this->isolator = new Isolator();
    }

    /**
     * Get associated isolator.
     *
     * @return Isolator
     */
    protected function getIsolator()
    {
        return $this->isolator;
    }

    /**
     * Will restore all existing PHP blocks to their original content.
     *
     * @param string $source
     * @return string
     */
    protected function repairPHP($source)
    {
        if (!$this->isolatePHP)
        {
            return $source;
        }

        return $this->getIsolator()->repairPHP($source);
    }

    /**
     * Bypassed Tokenizer creation and immediately returns parsed tokens.
     *
     * @param string $source     HTML source.
     * @param bool   $isolatePHP Should PHP block should be isolated and enabled by default
     * @param bool   $aspTags    ASP like PHP blocks should be isolated and enabled by default.
     * @return array
     */
    public static function parseSource($source, $isolatePHP = true, $aspTags = true)
    {
        return static::make(compact('isolatePHP', 'aspTags'))->parse($source);
    }

    /**
     * Parser HTML content and return it's tokens. You can use callback function for handling tokens
     * while parsing.
     *
     * @param string $source HTML source.
     * @return array
     */
    public function parse($source)
    {
        //Cleaning list of already parsed tokens
        $this->tokens = [];

        if ($this->isolatePHP)
        {
            $source = $this->getIsolator()->isolatePHP($source);
        }

        $quotas = '';
        $buffer = '';

        $length = strlen($source);
        $position = self::POSITION_PLAIN_TEXT;
        for ($pointer = 0; $pointer < $length; $pointer++)
        {
            $char = $source[$pointer];
            switch ($char)
            {
                case '<':
                    if ($position == self::POSITION_IN_QUOTAS)
                    {
                        $buffer .= $char;
                        break;
                    }

                    if ($position == self::POSITION_IN_TAG)
                    {
                        $buffer = '<' . $buffer;
                    }

                    //Handling previous token
                    $this->handleToken(self::PLAIN_TEXT, $buffer);

                    //We are in tag now
                    $position = self::POSITION_IN_TAG;
                    $buffer = '';
                    break;
                case '>':
                    if ($position != self::POSITION_IN_TAG)
                    {
                        $buffer .= $char;
                        break;
                    }

                    //Token ended
                    $this->handleToken(false, $buffer);

                    //We are in a plain text now
                    $position = self::POSITION_PLAIN_TEXT;
                    $buffer = '';
                    break;
                case '"':
                case "'":
                    if ($position == self::POSITION_IN_TAG)
                    {
                        //Jumping into argument
                        $position = self::POSITION_IN_QUOTAS;
                        $quotas = $char;
                    }
                    elseif ($position == self::POSITION_IN_QUOTAS && $char == $quotas)
                    {
                        //Jumping from argument
                        $position = self::POSITION_IN_TAG;
                        $quotas = '';
                    }
                default:
                    //Checking for invalid characters in tag name or arguments
                    if ($position == self::POSITION_IN_TAG)
                    {
                        if (!preg_match('/[a-z0-9 \._\-="\':\/\r\n\t]/i', $char))
                        {
                            $buffer = '<' . $buffer;
                            $position = self::POSITION_PLAIN_TEXT;
                        }
                    }
                    $buffer .= $char;
            }
        }

        $this->handleToken(self::PLAIN_TEXT, $buffer);

        return $this->tokens;
    }

    /**
     * Open and parse file using tokenizer. The same output will be returned as the readSource()
     * method.
     *
     * @param string $filename HTML file.
     * @return array
     */
    public function openFile($filename)
    {
        return $this->parse(FileManager::getInstance()->read($filename));
    }

    /**
     * Parses tag body for arguments, name, etc.
     *
     * @param string $content Tag content to be parsed (from < till >).
     * @return array
     */
    protected function parseToken($content)
    {
        $token = [
            self::TOKEN_NAME       => '',
            self::TOKEN_TYPE       => self::TAG_OPEN,
            self::TOKEN_CONTENT    => '<' . ($content = $this->repairPHP($content)) . '>',
            self::TOKEN_ATTRIBUTES => []
        ];

        //Some parts of text just looks like tags, but their not
        if (!preg_match('/^\/?[a-z0-9_:\/][a-z 0-9\._\-:\/]*/i', $content))
        {
            $token[self::TOKEN_TYPE] = self::PLAIN_TEXT;
            unset($token[self::TOKEN_NAME], $token[self::TOKEN_NAME]);

            return $token;
        }

        //Local PHP isolation
        $isolator = new Isolator('-argument-', '-block-', true);

        $content = $isolator->isolatePHP($content);

        //Parsing arguments, due they already checked for open-close quotas we can use regular expression
        $attribute = '/(?P<name>[a-z0-9_\-\.\:]+)[ \n\t\r]*(?:(?P<equal>=)[ \n\t\r]*'
            . '(?P<value>[a-z0-9\-]+|\'[^\']+\'|\"[^\"]+\"))?/si';

        preg_match_all($attribute, $content, $attributes);

        foreach ($attributes['value'] as $index => $value)
        {
            if ($value && ($value{0} == "'" || $value{0} == '"'))
            {
                $value = trim($value, $value{0});
            }

            $name = $this->repairPHP($isolator->repairPHP($attributes['name'][$index]));
            $token[self::TOKEN_ATTRIBUTES][$name] = $this->repairPHP($isolator->repairPHP($value));

            if (empty($attributes['equal'][$index]))
            {
                $token[self::TOKEN_ATTRIBUTES][$name] = null;
            }
        }

        //Fetching name
        $name = current(explode(' ', $content));
        if ($name{0} == '/')
        {
            $token[self::TOKEN_TYPE] = self::TAG_CLOSE;
            unset($token[self::TOKEN_ATTRIBUTES]);
        }

        if ($content{strlen($content) - 1} == '/')
        {
            $token[self::TOKEN_TYPE] = self::TAG_SHORT;
        }

        $token[self::TOKEN_NAME] = $name = trim($name, '/');
        unset($token[self::TOKEN_ATTRIBUTES][$name]);

        $token[self::TOKEN_NAME] = trim($token[self::TOKEN_NAME]);

        return $token;
    }

    /**
     * Handles single token and passes it to a callback function if specified.
     *
     * @param int    $tokenType Token type.
     * @param string $content   Non parsed token content.
     */
    protected function handleToken($tokenType, $content)
    {
        if ($tokenType == self::PLAIN_TEXT)
        {
            if (empty($content))
            {
                return;
            }

            $token = [
                self::TOKEN_TYPE    => self::PLAIN_TEXT,
                self::TOKEN_CONTENT => $this->repairPHP($content)
            ];
        }
        else
        {
            $token = $this->parseToken($content);
        }

        $this->tokens[] = $token;
    }
}