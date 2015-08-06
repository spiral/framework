<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Reactor;

/**
 * Reactor element represents on piece of class/method or property.
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
    protected $docComment = [];

    /**
     * @param string $name Element name.
     */
    public function __construct($name)
    {
        $this->setName($name);
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
     * @param string|array $docComment
     * @return $this
     */
    public function setComment($docComment)
    {
        if (is_array($docComment)) {
            $this->docComment = $docComment;

            return $this;
        }

        $this->docComment = [];
        $docComment = explode("\n", preg_replace('/[\n\r]+/', "\n", $docComment));
        foreach ($docComment as $line) {
            //Cutting start spaces
            $line = trim(preg_replace('/[ \*]+/si', ' ', $line));
            if ($line != '/') {
                $this->docComment[] = $line;
            }
        }

        return $this;
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
        foreach ($this->docComment as &$comment) {
            $comment = str_replace($search, $replace, $comment);
            unset($comment);
        }

        return $this;
    }

    /**
     * Multiple comment replaces.
     *
     * @param array $replaces Associated array (search => replace).
     * @return $this
     */
    public function batchReplace(array $replaces)
    {
        foreach ($replaces as $target => $replace) {
            $this->replaceComments($target, $replaces);
        }

        return $this;
    }

    /**
     * Render element into string with specified start indent level.
     *
     * @param int $indentLevel
     * @return string
     */
    abstract public function render($indentLevel = 0);

    /**
     * Render element doc comment.
     *
     * @param int $indentLevel
     * @return string
     */
    protected function renderComment($indentLevel = 0)
    {
        if (!$this->docComment) {
            return "";
        }

        $result = ["", "/**"];
        foreach ($this->docComment as $comment) {
            $result[] = " * " . $comment;
        }

        $result[] = " */";

        return $this->join($result, $indentLevel);
    }

    /**
     * Apply indent to string.
     *
     * @param string $string
     * @param int    $indentLevel
     * @return string
     */
    public function indent($string, $indentLevel = 0)
    {
        return str_repeat(self::INDENT, max($indentLevel, 0)) . $string;
    }

    /**
     * Join multiple string lines and apply indent to every of them.
     *
     * @param array $lines
     * @param int   $indentLevel
     * @return string
     */
    protected function join(array $lines, $indentLevel = 0)
    {
        foreach ($lines as &$line) {
            $line = $this->indent($line, $indentLevel);
            unset($line);
        }

        return join("\n", $lines);
    }

    /**
     * Remove all properties, methods and constants.
     */
    public function flushSchema()
    {
        $this->docComment = [];
    }
}