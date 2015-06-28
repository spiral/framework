<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Support\Generators;

use Spiral\Core\Component;
use Spiral\Support\Generators\Reactor\BaseElement;

class ArrayExporter extends Component
{
    /**
     * Serialize config data with valid formatting (4 spaces for indent) and mounted path constants.
     *
     * @param array  $array  Merged config data.
     * @param string $indent Indent value (4 spaces by default).
     * @param int    $level  Array level.
     * @return string
     */
    public function export(array $array, $indent = BaseElement::INDENT, $level = 0)
    {
        //Delimiters between rows and sub-arrays.
        $assign = " => ";
        $subIndent = "\n" . str_repeat($indent, $level + 2);
        $keyIndent = "\n" . str_repeat($indent, $level + 1);

        //No keys for associated array
        $associated = array_diff_key($array, array_keys(array_keys($array)));

        $result = [];
        $keyLength = 0;
        foreach ($array as $name => $value)
        {
            $keyLength = max(strlen(var_export($name, true)), $keyLength);
        }

        foreach ($array as $name => $value)
        {
            if ($associated)
            {
                $name = str_pad(var_export($name, true), $keyLength, ' ', STR_PAD_RIGHT) . $assign;
            }
            else
            {
                $name = "";
            }

            if (!is_array($value))
            {
                $result[] = $this->packValue($name, $value);
                continue;
            }

            if ($value == [])
            {
                $result[] = $name . "[]";
                continue;
            }

            //Sub-array
            $result[] = $name . "[{$subIndent}" . $this->export(
                    $value,
                    $indent,
                    $level + 1
                ) . "{$keyIndent}]";
        }

        if ($level !== 0)
        {
            return $result ? join(",$keyIndent", $result) : "";
        }
        else
        {
            return "[{$keyIndent}" . join(",{$keyIndent}", $result) . "\n]";
        }
    }

    /**
     * Pack scalar value to config.
     *
     * @param string $name
     * @param mixed  $value
     * @return string
     */
    protected function packValue($name, $value)
    {
        if (is_null($value))
        {
            $value = "null";
        }
        elseif (is_bool($value))
        {
            $value = ($value ? "true" : "false");
        }
        elseif (!is_numeric($value))
        {
            if (!is_string($value))
            {
                throw new \RuntimeException("Unable to pack non scalar value.");
            }

            $value = var_export($value, true);
        }

        return $name . $value;
    }
}