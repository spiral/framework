<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Translator\Exporters;

/**
 * Export application translation into PO using spiral specific comments and hooks, can be imported
 * back using GetTextImporter.
 */
class GetTextExporter extends AbstractExporter
{
    /**
     * {@inheritdoc}
     */
    public function compile()
    {
        //Duplicate strings has to be appended with spaces
        $duplicates = [];

        $pluralForms = $this->translator->pluralizer($this->getLanguage())->countForms();
        $pluralFormula = $this->translator->pluralizer($this->getLanguage())->getFormula();

        /**
         * PO file header.
         */
        $output = [];
        $output[] = 'msgid ""';
        $output[] = 'msgstr ""';
        $output[] = '"Project-Id-Version: Spiral Framework\n"';
        $output[] = '"Language-Id: ' . $this->getLanguage() . '\n"';
        $output[] = '"Language: ' . $this->getLanguage() . '\n"';
        $output[] = '"MIME-Version: 1.0\n"';
        $output[] = '"Content-Type: text/plain; charset=UTF-8\n"';
        $output[] = '"Content-Type: text/plain; charset=iso-8859-1\n"';
        $output[] = '"Plural-Forms: nplurals=' . $pluralForms . '; plural=(' . $pluralFormula . ')\n"';
        $output[] = '';

        /**
         * This is not default gettext syntax, but as spiral supports multiple namespaces for identifiers
         * and gettext not. There is simple hack: if identifier duplicated additional space symbol
         * added at the end of it the string (4 dups => 3 spaces), translator can see comment to
         * understand where he can find this line. Extra spaces will be removed during import.
         */
        foreach ($this->bundles as $bundle => $data)
        {
            foreach ($data as $line => $value)
            {
                if (isset($duplicates[$line]))
                {
                    $duplicates[$line]++;

                    //Nobody will see space at right :) and we will remove this space on importing
                    $line = $line . str_repeat(' ', $duplicates[$line] - 1);
                }
                else
                {
                    $duplicates[$line] = 1;
                }

                //Bundle and message ID
                $output[] = '# ' . $bundle;
                $output[] = '#: ' . $bundle;
                $output[] = 'msgid "' . addcslashes($line, '"') . '"';

                if (!is_array($value))
                {
                    $output[] = 'msgstr ' . $this->escape($value);
                    $output[] = '';
                    continue;
                }

                //Plural forms
                $output[] = 'msgid_plural ' . $this->escape($value[count($value) - 1]);
                for ($form = 0; $form < $pluralForms; $form++)
                {
                    if (isset($value[$form]))
                    {
                        $output[] = 'msgstr[' . $form . '] ' . $this->escape($value[$form]);
                        continue;
                    }

                    $output[] = 'msgstr[' . $form . '] ' . $this->escape($value[count($value) - 1]);
                }
            }
        }

        return join("\n", $output);
    }

    /**
     * Escape string to validly insert it into PO file.
     *
     * @param string $string
     * @return string
     */
    protected function escape($string)
    {
        return '"' . addcslashes(preg_replace('/[\n\r]+/', '\n', $string), '"') . '"';
    }
}