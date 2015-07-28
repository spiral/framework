<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Translator;

interface ImporterInterface
{
    /**
     * Manually force import language id. Should be valid language id and have presets section in i18n
     * configuration. All bundles will be imported to that language directory.
     *
     * @param string $language Valid language id.
     */
    public function setLanguage($language);

    /**
     * Detected or manually defined language. Should be valid language ID and have presets section in
     * translator configuration. All bundles will be imported to that language directory.
     *
     * @return string
     */
    public function getLanguage();

    /**
     * Open import file.
     *
     * @param string $filename
     * @return self
     * @throws TranslatorException
     */
    public function open($filename);

    /**
     * Upload parsed data to target language bundles, language should be detected during parsing or
     * can be specified manually after (import destination will be changed accordingly).
     *
     * @param bool $replace If true imported data should replace existed bundle translations entirely.
     * @throws TranslatorException
     */
    public function import($replace = false);
}