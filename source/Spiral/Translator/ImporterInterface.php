<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Translator;

use Spiral\Translator\Exceptions\ImporterException;

/**
 * Import bundle strings into automatically resolved or manually specified language.
 */
interface ImporterInterface
{
    /**
     * Open file contains language strings.
     *
     * @param string $filename
     * @return self
     * @throws ImporterException
     */
    public function open($filename);

    /**
     * Manually set import target language.
     *
     * @param string $language
     */
    public function setLanguage($language);

    /**
     * Language to be imported.
     *
     * @return string
     */
    public function getLanguage();

    /**
     * Import strings and bundles into targeted language.
     *
     * @param bool $replace Entirely replace language strings without merging translations.
     * @throws ImporterException
     */
    public function import($replace = false);
}