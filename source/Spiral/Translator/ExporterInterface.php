<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Translator;

use Spiral\Translator\Exceptions\ExporterException;

/**
 * Export language strings to file using defined format.
 */
interface ExporterInterface
{
    /**
     * Load all bundle strings into memory for future exporting in specified format.
     *
     * @param string $language Should be valid language id.
     * @param string $prefix   Only bundle names started with this prefix will be exported.
     * @return self
     * @throws ExporterException
     */
    public function load($language, $prefix = '');

    /**
     * Export collected bundle strings to specified file using format described by exporter. Method
     * load has to be already called.
     *
     * @see load()
     * @param string $filename
     * @throws ExporterException
     */
    public function export($filename);
}