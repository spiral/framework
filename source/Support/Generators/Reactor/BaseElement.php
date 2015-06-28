<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Support\Generators\Reactor;

use Spiral\Core\Component;
use Spiral\Helpers\StringHelper;

abstract class BaseElement extends Component
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
     * Element name, which can be used for multiple purposes such as class name, property name,
     * method name or even namespace name.
     *
     * @var string
     */
    protected $name = '';

    /**
     * Element docComment. Doc comments can be assigned to any existing element and can be inherited
     * from the parent class if element is cloned by it's own schema. DocComment content can be replaced
     * or updated using element methods.
     *
     * @var array
     */
    protected $docComment = [];

    /**
     * Constructing a new element under a given name.
     *
     * @param string $name Element name.
     */
    public function __construct($name)
    {
        $this->setName($name);
    }

    /**
     * Current element name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Overwrite the element's name.
     *
     * @param string $name New element name.
     * @return static
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Set docComment for element. Each value in array represents a comment line.
     *
     * @param string|array $docComment
     * @param bool         $append If false, the current doc comment will be erased.
     * @return static
     */
    public function setComment($docComment, $append = false)
    {
        $append || $this->docComment = [];
        if (!is_array($docComment))
        {
            $docComment = explode("\n", StringHelper::normalizeEndings($docComment));
            foreach ($docComment as $line)
            {
                //Cutting start spaces
                $line = trim(preg_replace('/[ \*]+/si', ' ', $line));

                if ($line != '/')
                {
                    $this->docComment[] = $line;
                }
            }
        }
        else
        {
            $this->docComment = array_merge($this->docComment, $docComment);
        }

        return $this;
    }

    /**
     * Replace strings in all doc comment lines or other names. This is especially  useful when you
     * want to build virtual documentation class based on another declaration.
     *
     * @param string|array $search  String to find.
     * @param string|array $replace String to replace with.
     * @return static
     */
    public function replaceComments($search, $replace)
    {
        foreach ($this->docComment as &$comment)
        {
            $comment = str_replace($search, $replace, $comment);
            unset($comment);
        }

        return $this;
    }

    /**
     * Performs multiple replacements for every key => value pair.
     *
     * @param array $replaces Associated array (search=>replace).
     * @return static
     */
    public function batchCommentsReplace(array $replaces)
    {
        foreach ($replaces as $target => $replace)
        {
            $this->replaceComments($target, $replaces);
        }

        return $this;
    }

    /**
     * Render the docComment based on the lines or strings stored in element.
     *
     * @param int $indentLevel Tabulation level.
     * @return string
     */
    protected function renderComment($indentLevel = 0)
    {
        if (!$this->docComment)
        {
            return "";
        }

        $result = ["", "/**"];
        foreach ($this->docComment as $comment)
        {
            $result[] = " * " . $comment;
        }

        $result[] = " */";

        return self::join($result, $indentLevel);
    }

    /**
     * Render element declaration. The method should be declared in RElement child classes and then
     * perform the operation for rendering a specific type of content.
     *
     * @param int $indentLevel Tabulation level.
     * @return string
     */
    abstract public function createDeclaration($indentLevel = 0);


    /**
     * Apply current indent and tabulation level to string. This method will shift string to the right
     * using an indent string declared in RElement::$indent using $indentLevel argument as a multiplier
     * (0 - no shifts).
     *
     * @param string $string      Indent string.
     * @param int    $indentLevel Tabulation level.
     * @return string
     */
    public static function setIndent($string, $indentLevel = 0)
    {
        return str_repeat(self::INDENT, max($indentLevel, 0)) . $string;
    }

    /**
     * Apply current indent and tabulation to multiple lines stored as array of strings. This method
     * will shift every line string to the right using an indent declared in RElement::$indent using
     * $indentLevel argument as a multiplier.
     *
     * @param array $lines       Lines array that will be shifted.
     * @param int   $indentLevel Tabulation level.
     * @return string
     */
    protected static function join(array $lines, $indentLevel = 0)
    {
        foreach ($lines as &$line)
        {
            $line = self::setIndent($line, $indentLevel);
            unset($line);
        }

        return join("\n", $lines);
    }
}