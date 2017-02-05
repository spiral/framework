<?php
/**
 * Spiral Framework, SpiralScout LLC.
 *
 * @package   spiralFramework
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2011
 */

namespace Spiral\Translator;

use Spiral\Core\MemoryInterface;
use Spiral\Translator\Exceptions\CatalogueException;
use Symfony\Component\Translation\MessageCatalogue;

/**
 * Similar to Symfony catalogue, however this one does not operate with fallback locale.
 * Provides ability to cache domains in memory.
 *
 * @todo improve implementation by using original message catalogue and cache at higher level
 */
class Catalogue
{
    /**
     * Locale name.
     *
     * @var string
     */
    private $locale = '';

    /**
     * Cached domains data (aggregated and loaded on domain basis).
     *
     * @var array
     */
    private $domains = [];

    /**
     * @var MemoryInterface
     */
    protected $memory = null;

    /**
     * @param string          $locale
     * @param MemoryInterface $memory
     */
    public function __construct($locale, MemoryInterface $memory)
    {
        $this->locale = $locale;
        $this->memory = $memory;
    }

    /**
     * @return string
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * List of loaded domains
     *
     * @return array
     */
    public function loadedDomains(): array
    {
        return array_keys($this->domains);
    }

    /**
     * Check if domain message exists.
     *
     * @param string $domain
     * @param string $string
     *
     * @return bool
     */
    public function has(string $domain, string $string): bool
    {
        if (!empty($this->domains[$domain]) && array_key_exists($string, $this->domains[$domain])) {
            return true;
        }

        $this->loadDomain($domain);

        return array_key_exists($string, $this->domains[$domain]);
    }

    /**
     * Get domain message.
     *
     * @param string $domain
     * @param string $string
     *
     * @return string
     *
     * @throws CatalogueException
     */
    public function get(string $domain, string $string): string
    {
        if (!$this->has($domain, $string)) {
            throw new CatalogueException("Undefined string in domain '{$domain}'");
        }

        return $this->domains[$domain][$string];
    }

    /**
     * Get all domain messages
     *
     * @param string $domain
     *
     * @return array
     */
    public function domainMessages(string $domain): array
    {
        if (!$this->isLoaded($domain)) {
            $this->loadDomain($domain);
        }

        return $this->domains[$domain];
    }

    /**
     * Adding string association to be stored into memory.
     *
     * @param string $domain
     * @param string $string
     * @param string $value
     */
    public function set(string $domain, string $string, string $value)
    {
        $this->domains[$domain][$string] = $value;
    }

    /**
     * @param MessageCatalogue $catalogue
     * @param bool             $follow When set to true messages from given catalogue will overwrite
     *                                 existed messages.
     */
    public function mergeFrom(MessageCatalogue $catalogue, bool $follow = true)
    {
        foreach ($catalogue->all() as $domain => $messages) {

            if (!isset($this->domains[$domain])) {
                $this->domains[$domain] = [];
            }

            if ($follow) {
                //MessageCatalogue string has higher priority that string stored in memory
                $this->domains[$domain] = array_merge($messages, $this->domains[$domain]);
            } else {
                $this->domains[$domain] = array_merge($this->domains[$domain], $messages);
            }
        }
    }

    /**
     * Converts into one MessageCatalogue.
     *
     * @return MessageCatalogue
     */
    public function toMessageCatalogue(): MessageCatalogue
    {
        return new MessageCatalogue($this->locale, $this->domains);
    }

    /**
     * Load all catalogue domains.
     *
     * @param array $domains Domains to be loaded
     *
     * @return self
     */
    public function loadDomains(array $domains = []): Catalogue
    {
        foreach ($domains as $domain) {
            $this->loadDomain($domain);
        }

        return $this;
    }

    /**
     * Save catalogue domains into memory (all domains to be saved)
     */
    public function saveDomains()
    {
        foreach ($this->domains as $domain => $data) {
            $this->saveDomain($domain, $data);
        }
    }

    /**
     * Check if domain data was loaded
     *
     * @param string $domain
     *
     * @return bool
     */
    protected function isLoaded(string $domain): bool
    {
        return isset($this->domains[$domain]);
    }

    /**
     * Trying to load domain data from memory.
     *
     * @param string $domain
     */
    protected function loadDomain(string $domain)
    {
        $data = $this->memory->loadData(Translator::MEMORY . '.' . $this->domainSection($domain));

        if (empty($data)) {
            $data = [];
        }

        if (!empty($this->domains[$domain])) {
            $this->domains[$domain] = array_merge($this->domains[$domain], $data);
        } else {
            $this->domains[$domain] = $data;
        }
    }

    /**
     * Save domain data into memory.
     *
     * @param string $domain
     * @param array  $data
     */
    protected function saveDomain(string $domain, array $data)
    {
        $this->memory->saveData(Translator::MEMORY . '.' . $this->domainSection($domain), $data);
    }

    /**
     * Memory section to store domain data in memory
     *
     * @param string $domain
     *
     * @return string
     */
    private function domainSection(string $domain): string
    {
        return "{$this->locale}-{$domain}";
    }
}