<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\Tokenizer;

use Spiral\Core\Component;
use Spiral\Helpers\StringHelper;

class Isolator extends Component
{
    /**
     * All existing and isolated PHP blocks.
     *
     * @var array
     */
    protected $phpBlocks = [];

    /**
     * Isolated prefix and postfix. Use any values that will not corrupt HTML or other source.
     *
     * @var string
     */
    protected $prefix = '';
    protected $postfix = '';

    /**
     * Replaces has to be performed before / after finding and mounting blocks. This replaces used
     * to index PHP blocks which are not reachable in some conditions, such as if short_tags disabled
     * or specified syntax required (ASP tags).
     *
     * @var array
     */
    protected $patterns = [];

    /**
     * Revert replaces, will contain list of existing and replaced tags (unique set), so output
     * source will be identical
     * to input.
     *
     * @var array
     */
    protected $replaces = [];

    /**
     * Short tags will automatically be replaced to solve the issue with short_tags = off.
     *
     * @var bool
     */
    protected $shortTags = true;

    /**
     * New php isolator.
     *
     * @param string $prefix    Replaced block prefix, -php by default.
     * @param string $postfix   Replaced block postfix, block- by default.
     * @param bool   $shortTags Handle short tags. This is not required if short_tags are enabled.
     */
    public function __construct($prefix = '-php-', $postfix = '-block-', $shortTags = true)
    {
        $this->prefix = $prefix;
        $this->postfix = $postfix;

        $this->shortTags($shortTags);
    }

    /**
     * Adding a new tag replacement pattern. Should include tag name, regular expression to handle
     * tag and replacement string. Originally was used to support asp tags, now only for short tags/
     *
     * @param string $tag     PHP Tag to handle, can be an open or closed PHP tag.
     * @param string $regexp  Pattern used to catch tags, can be empty, in this case str_replace
     *                        will be used.
     * @param string $replace String tags has to be replaced with, has to be valid php opening or
     *                        closed tag. Should include %s which will be used to identity how to
     *                        revert replacements.
     */
    protected function addPattern($tag, $regexp = null, $replace = "<?php /*%s*/")
    {
        $this->patterns[$tag] = ['regexp' => $regexp, 'replace' => $replace];
    }

    /**
     * Enable/disable caching blocks defined by PHP short tags. This allows the system to isolate
     * blocks even in an environment where the short_tags option is disabled. This is enabled by
     * default.
     *
     * @param bool $enable
     * @return $this
     */
    public function shortTags($enable)
    {
        if ($enable)
        {
            $this->addPattern('<?=', false, "<?php /*%s*/ echo ");
            $this->addPattern('<?', '/<\?(?!php)/is');
        }
        else
        {
            unset($this->patterns['<?'], $this->patterns['<?=']);
        }

        return $this;
    }

    /**
     * Replace all matched tags with their <?php equivalent. These tags will be detected and parsed
     * by token_get_all() function even if there isn't a directive in php.ini file.
     *
     * @param string $source Valid PHP code.
     * @return string
     */
    protected function replaceTags($source)
    {
        $replaces = &$this->replaces;
        foreach ($this->patterns as $tag => $pattern)
        {
            if (empty($pattern['regexp']))
            {
                if ($replace = array_search($tag, $replaces))
                {
                    $source = str_replace($tag, $replace, $source);
                    continue;
                }

                //Simple replacement (shold be enought randomization)
                $replace = sprintf($pattern['replace'], StringHelper::random(10) . '-' . uniqid());
                $replaces[$replace] = $tag;

                //Replacing
                $source = str_replace($tag, $replace, $source);
                continue;
            }

            $source = preg_replace_callback($pattern['regexp'], function ($tag) use (&$replaces, $pattern)
            {
                $tag = $tag[0];

                if ($key = array_search($tag, $replaces))
                {
                    return $key;
                }

                $replace = sprintf($pattern['replace'], StringHelper::random(10) . '-' . uniqid());
                $replaces[$replace] = $tag;

                return $replace;
            }, $source);
        }

        return $source;
    }

    /**
     * Mount all original tags searched and replaced by replaceTags() function. The result of this
     * function converts source to it's original form.
     *
     * @param string $source
     * @return string
     */
    protected function restoreTags($source)
    {
        return strtr($source, $this->replaces);
    }

    /**
     * Isolates all returned PHP blocks with a defined pattern.
     *
     * @param string $source Valid PHP code.
     * @return string
     */
    public function isolatePHP($source)
    {
        //Replacing all
        $source = $this->replaceTags($source);
        $tokens = token_get_all($source);

        $this->phpBlocks = [];
        $phpBlock = false;
        $blockID = 0;

        $source = '';
        foreach ($tokens as $token)
        {
            if ($token[0] == T_OPEN_TAG || $token[0] == T_OPEN_TAG_WITH_ECHO)
            {
                $phpBlock = $token[1];
                continue;
            }

            if ($token[0] == T_CLOSE_TAG)
            {
                $phpBlock .= $token[1];
                $this->phpBlocks[$blockID] = $phpBlock;
                $phpBlock = '';

                $source .= $this->prefix . ($blockID++) . $this->postfix;

                continue;
            }

            if (!empty($phpBlock))
            {
                $phpBlock .= is_array($token) ? $token[1] : $token;
            }
            else
            {
                $source .= is_array($token) ? $token[1] : $token;
            }
        }

        foreach ($this->phpBlocks as &$phpBlock)
        {
            //Will repair php source with correct (original) tags
            $phpBlock = $this->restoreTags($phpBlock);
            unset($phpBlock);
        }

        //Will restore tags which were replaced but weren't handled by php (for example string
        //contents)
        return $this->restoreTags($source);
    }

    /**
     * List of all returned and replaced php blocks.
     *
     * @return array
     */
    public function getBlocks()
    {
        return $this->phpBlocks;
    }

    /**
     * Update isolator php blocks.
     *
     * @param array $phpBlocks
     * @return $this
     */
    public function setBlocks($phpBlocks)
    {
        $this->phpBlocks = $phpBlocks;

        return $this;
    }

    /**
     * Restore PHP blocks position in isolated source (isolatePHP() should be already called).
     *
     * @param string $source
     * @return string
     */
    public function repairPHP($source)
    {
        return preg_replace_callback(
            '/' . preg_quote($this->prefix) . '(?P<id>[0-9]+)' . preg_quote($this->postfix) . '/',
            [$this, 'getBlock'],
            $source
        );
    }

    /**
     * Remove PHP blocks from isolated source (isolatePHP() should be already called).
     *
     * @param string $isolatedSource
     * @return string
     */
    public function removePHP($isolatedSource)
    {
        return preg_replace(
            '/' . preg_quote($this->prefix) . '(?P<id>[0-9]+)' . preg_quote($this->postfix) . '/',
            '',
            $isolatedSource
        );
    }

    /**
     * Get saved PHP block by ID.
     *
     * @param int $blockID
     * @return mixed
     */
    protected function getBlock($blockID)
    {
        if (!isset($this->phpBlocks[$blockID['id']]))
        {
            return $blockID[0];
        }

        return $this->phpBlocks[$blockID['id']];
    }

    /**
     * Reset isolator state.
     */
    public function reset()
    {
        $this->phpBlocks = $this->replaces = [];
    }
}