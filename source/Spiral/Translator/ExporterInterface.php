<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Translator;

interface ExporterInterface
{
    /**
     * Load all bundle strings into memory for future exporting in specified format.
     *
     * @param string $language Should be valid language id.
     * @param string $prefix   Only bundle names started with this prefix will be exported.
     * @return self
     * @throws TranslatorException
     */
    public function load($language, $prefix = '');

    /**
     * Export collected bundle strings to specified file using format described by exporter.
     *
     * @param string $filename
     */
    public function export($filename);
}