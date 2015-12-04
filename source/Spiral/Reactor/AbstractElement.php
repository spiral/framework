<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Reactor;

/**
 * Reactor element represents on piece of class/method or property.
 *
 * @deprecated To be replaced with Zend Code.
 */
abstract class AbstractElement
{
    /**
     * Method/property access level values.
     */
    const ACCESS_PUBLIC    = 'public';
    const ACCESS_PRIVATE   = 'private';
    const ACCESS_PROTECTED = 'protected';

    /**
     * Indent is always 4 spaces.
     */
    const INDENT = "    ";

    /**
     * @var string
     */
    private $name = '';

    /**
     * @var array
     */
    protected $comment = [];

    /**
     * @param string $name Element name.
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Replace element DocComment, comment can be provided in a form of array or string.
     *
     * @param string|array $comment
     * @param bool         $append
     * @return $this
     */
    public function setComment($comment, $append = false)
    {
        if (is_array($comment)) {
            if (!$append) {
                $this->comment = $comment;
            } else {
                $this->comment = array_merge($this->comment, $comment);
            }

            return $this;
        }

        if (!$append) {
            $this->comment = [];
        }

        //Normalizing endings
        $comment = explode("\n", preg_replace('/[\n\r]+/', "\n", $comment));

        foreach ($comment as $line) {
            //Cutting start spaces
            $line = trim(preg_replace('/[ \*]+/si', ' ', $line));
            if ($line != '/') {
                $this->comment[] = $line;
            }
        }

        return $this;
    }

    /**
     * Get docComment lines.
     *
     * @return array
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Replace string occurrence in element comment and every sub comment.
     *
     * @param string|array $search
     * @param string|array $replace
     * @return $this
     */
    public function replaceComments($search, $replace)
    {
        foreach ($this->comment as &$comment) {
            $comment = str_replace($search, $replace, $comment);
            unset($comment);
        }

        return $this;
    }

    /**
     * Convert element into set of indented lines.
     *
     * @param int $indent
     * @return array
     */
    abstract public function lines($indent = 0);

    /**
     * Render element doc comment lines with specified indent.
     *
     * @param array $comment Comment to be rendered instead of default one.
     * @param int   $indent  Indent level.
     * @return array
     */
    protected function commentLines(array $comment = [], $indent = 0)
    {
        if (empty($comment)) {
            if (empty($comment = $this->comment)) {
                return [];
            }
        }

        $result = ["/**"];
        foreach ($comment as $line) {
            $result[] = " * " . $line;
        }

        $result[] = " */";

        return $this->indentLines($result, $indent);
    }

    /**
     * Apply indent to string.
     *
     * @param string $string
     * @param int    $indent
     * @return string
     */
    public function indent($string, $indent = 0)
    {
        return str_repeat(self::INDENT, max($indent, 0)) . $string;
    }

    /**
     * @param     $lines
     * @param int $indent
     * @return array
     */
    protected function indentLines($lines, $indent = 0)
    {
        $result = [];
        foreach ($lines as $line) {
            $result[] = $this->indent($line, $indent);
        }

        return $result;
    }

    /**
     * Remove all properties, methods and constants.
     */
    public function flushSchema()
    {
        $this->comment = [];
    }
}