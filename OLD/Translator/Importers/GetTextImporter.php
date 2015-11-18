<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Translator\Importers;

/**
 * Parse PO file created by GetTextExporter to fetch language and location strings.
 */
class GetTextImporter extends AbstractImporter
{
    /**
     * {@inheritdoc}
     */
    protected function parseStrings($source)
    {
        $plurals = false;
        $buffer = '';

        foreach (explode("\n", $source) as $line) {
            if (strpos($line, '"') === 0) {
                //Meta information
                $line = substr($line, 1, -1);
                if (strpos($line, 'Language-Id:') === 0) {
                    //Language is a 2 characters string identifier
                    $this->setLanguage(substr($line, 13, 2));
                }

                continue;
            }

            if (preg_match('/\#: (.*)/', $line, $matches)) {
                //Namespace definition
                $bundle = $matches[1];
                continue;
            }

            if (substr($line, 0, 12) == 'msgid_plural') {
                $plurals = true;
                continue;
            }

            if (substr($line, 0, 1) == '#' || !trim($line)) {
                if (!empty($token) && !empty($bundle)) {
                    //Previously read line
                    $this->bundles[$bundle][$this->normalize($token)] = is_array($buffer)
                        ? $buffer
                        : str_replace('\n', "\n", $buffer);

                    $token = '';
                    $plurals = false;
                }

                //Comment or empty line
                continue;
            }

            if (substr($line, 0, 5) == 'msgid') {
                //New token
                $buffer = $this->unescape(substr($line, 6));
                continue;
            }

            if (substr($line, 0, 6) == 'msgstr') {
                if (!$plurals) {
                    //Leaving token, message is here
                    $token = $buffer;
                    $buffer = $this->unescape(substr($line, 7));
                } else {
                    if (!is_array($buffer)) {
                        $token = $buffer;
                        $buffer = [];
                    }

                    //Plurals
                    $buffer[] = $this->unescape(substr($line, 10));
                }

                continue;
            }

            //Multiple lines
            if (is_array($buffer)) {
                $buffer[count($buffer) - 1] .= $this->unescape($line);
            } else {
                $buffer .= $this->unescape($line);
            }
        }

        //Last line
        if (!empty($bundle) && !empty($token)) {
            $this->bundles[$bundle][$this->normalize($token)] = is_array($buffer)
                ? $buffer
                : str_replace('\n', "\n", $buffer);
        }
    }

    /**
     * Normalizes bundle key (string) to prevent data loosing while extra lines or spaces or
     * formatting. Method will be applied only to keys, final value will be kept untouched.
     *
     * @param string $string String to be localized.
     * @return string
     */
    protected function normalize($string)
    {
        return preg_replace('/[ \t\n\r]+/', ' ', trim($string));
    }

    /**
     * Remove quotas and spaces used GetText PO file.
     *
     * @param string $string Message string fetched from PO file.
     * @return string
     */
    protected function unescape($string)
    {
        if (substr($string, 0, 1) == '"') {
            $string = substr($string, 1, -1);
        }

        return str_replace('\"', '"', $string);
    }
}