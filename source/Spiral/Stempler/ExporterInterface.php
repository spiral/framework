<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Stempler;

/**
 * ExporterInterface used to export outer (non handled by "block" elements) node blocks into html
 * source, for example it can convert all additional include attributes into valid tag attributes,
 * or create json/php array using them.
 */
interface ExporterInterface
{
    /**
     * @param string $content
     * @param array  $inject
     * @return string
     */
    public function mountBlocks($content, array $inject);
}