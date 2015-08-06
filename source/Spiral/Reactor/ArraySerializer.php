<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Reactor;

use Spiral\Reactor\Exceptions\SerializeException;

/**
 * Serializes simple array into pretty form.
 */
class ArraySerializer
{
    /**
     * Serialize array data into pretty but valid PHP code.
     *
     * @param array  $array
     * @param string $indent
     * @param int    $level Internal value.
     * @return string
     */
    public function serialize(array $array, $indent = AbstractElement::INDENT, $level = 0)
    {
        //Delimiters between rows and sub-arrays.
        $subIndent = "\n" . str_repeat($indent, $level + 2);
        $keyIndent = "\n" . str_repeat($indent, $level + 1);

        //No keys for associated array
        $associated = array_diff_key($array, array_keys(array_keys($array)));

        $result = [];
        $keyLength = 0;
        foreach ($array as $name => $value) {
            $keyLength = max(strlen(var_export($name, true)), $keyLength);
        }

        foreach ($array as $name => $value) {
            if ($associated) {
                $name = str_pad(var_export($name, true), $keyLength, ' ', STR_PAD_RIGHT) . " => ";
            } else {
                $name = "";
            }

            if (!is_array($value)) {
                $result[] = $this->packValue($name, $value);
                continue;
            }

            if ($value == []) {
                $result[] = $name . "[]";
                continue;
            }

            //Sub-array
            $result[] = $name
                . "[{$subIndent}" . $this->serialize($value, $indent, $level + 1) . "{$keyIndent}]";
        }

        if ($level !== 0) {
            return $result ? join(",$keyIndent", $result) : "";
        } else {
            return "[{$keyIndent}" . join(",{$keyIndent}", $result) . "\n]";
        }
    }

    /**
     * Pack array key value into string.
     *
     * @param string $name
     * @param mixed  $value
     * @return string
     * @throws SerializeException
     */
    protected function packValue($name, $value)
    {
        if (is_null($value)) {
            $value = "null";
        } elseif (is_bool($value)) {
            $value = ($value ? "true" : "false");
        } elseif (!is_numeric($value)) {
            if (!is_string($value)) {
                throw new SerializeException("Unable to pack non scalar value.");
            }

            $value = var_export($value, true);
        }

        return $name . $value;
    }
}