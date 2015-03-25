<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\I18n\GetText;

use Spiral\Components\I18n\Importer as LocalizationImporter;

class Importer extends LocalizationImporter
{
    /**
     * Method should read language bundles from specified filename and format them in an appropriate
     * way. Language has to be automatically detected during parsing, however it can be redefined
     * manually after.
     *
     * GetText PO file will be parsed for language headers, message lines and etc. Spiral expect
     * bundle id's located in message comments.
     *
     * @param string $filename
     * @return array
     */
    protected function parseData($filename)
    {
        //Indexing plural phrases
        $plurals = false;

        $poLines = explode("\n", $this->core->file->read($filename));

        $buffer = '';
        foreach ($poLines as $line)
        {
            if (strpos($line, '"') === 0)
            {
                //Meta information
                $line = substr($line, 1, -1);
                if (strpos($line, 'Language-Id:') === 0)
                {
                    //Language is a 2 characters string identifier
                    $this->language = substr($line, 13, 2);
                }

                continue;
            }

            if (preg_match('/\#: (.*)/', $line, $matches))
            {
                //Namespace definition
                $bundle = $matches[1];
                continue;
            }

            if (substr($line, 0, 12) == 'msgid_plural')
            {
                $plurals = true;
                continue;
            }

            if (substr($line, 0, 1) == '#' || !trim($line))
            {
                if (!empty($token) && !empty($bundle))
                {
                    //Previously read line
                    $this->bundles[$bundle][$this->i18n->normalize($token)] = is_array($buffer)
                        ? $buffer
                        : str_replace('\n', "\n", $buffer);

                    $token = '';
                    $plurals = false;
                }

                //Comment or empty line
                continue;
            }

            if (substr($line, 0, 5) == 'msgid')
            {
                //New token
                $buffer = $this->unescape(substr($line, 6));
                continue;
            }

            if (substr($line, 0, 6) == 'msgstr')
            {
                if (!$plurals)
                {
                    //Leaving token, message is here
                    $token = $buffer;
                    $buffer = $this->unescape(substr($line, 7));
                }
                else
                {
                    if (!is_array($buffer))
                    {
                        $token = $buffer;
                        $buffer = array();
                    }

                    //Plurals
                    $buffer[] = $this->unescape(substr($line, 10));
                }
                continue;
            }

            //Multiple lines
            if (is_array($buffer))
            {
                $buffer[count($buffer) - 1] .= $this->unescape($line);
            }
            else
            {
                $buffer .= $this->unescape($line);
            }
        }

        //Last line
        if (!empty($bundle) && !empty($token))
        {
            $this->bundles[$bundle][$this->i18n->normalize($token)] = is_array($buffer)
                ? $buffer
                : str_replace('\n', "\n", $buffer);
        }
    }

    /**
     * Remove quotas and spaces used GetText PO file.
     *
     * @param string $string Message string fetched from PO file.
     * @return string
     */
    protected function unescape($string)
    {
        if (substr($string, 0, 1) == '"')
        {
            $string = substr($string, 1, -1);
        }

        return str_replace('\"', '"', $string);
    }
}