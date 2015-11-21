<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Stempler;

use Spiral\Stempler\Exceptions\SyntaxException;

/**
 * Used to detect token behaviour based on internal rules.
 */
interface SyntaxInterface
{
    /**
     * Basic templater behaviours.
     */
    const TYPE_BLOCK    = 'block';
    const TYPE_EXTENDS  = 'extends';
    const TYPE_IMPORTER = 'use';
    const TYPE_INCLUDE  = 'include';
    const TYPE_NONE     = 'none';

    /**
     * In strict mode every unpaired close tag or other html error will raise an
     * StrictModeException.
     *
     * @return bool
     */
    public function isStrict();

    /**
     * Detect token behaviour.
     *
     * @param array  $token
     * @param string $name Node name stripper from token name.
     * @return string
     */
    public function tokenType(array $token, &$name = null);

    /**
     * Resolve include or extend location based on given token.
     *
     * @param array $token
     * @return string
     * @throws SyntaxException
     */
    public function resolvePath(array $token);

    /**
     * @param array      $token
     * @param Supervisor $supervisor
     * @return ImporterInterface
     * @throws SyntaxException
     */
    public function createImporter(array $token, Supervisor $supervisor);

    /**
     * Get all syntax block exporters.
     *
     * @return ExporterInterface[]
     */
    public function blockExporters();
}