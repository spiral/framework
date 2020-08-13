<?php

declare(strict_types=1);

namespace Symplify\ComposerJsonManipulator\ValueObject;

use Composer\Json\JsonManipulator;
use Symplify\ComposerJsonManipulator\Exception\InvalidComposerJsonKeyException;
use Symplify\PackageBuilder\Reflection\PrivatesCaller;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ComposerJson
{
    /**
     * @var string
     */
    public const KEY_NAME = 'name';

    /**
     * @var string
     */
    public const KEY_DESCRIPTION = 'description';

    /**
     * @var string
     */
    public const KEY_LICENSE = 'license';

    /**
     * @var string
     */
    public const KEY_MINIMUM_STABILITY = 'minimum-stability';

    /**
     * @var string
     */
    public const KEY_PREFER_STABLE = 'prefer-stable';

    /**
     * @var string
     */
    public const KEY_REPOSITORIES = 'repositories';

    /**
     * @var string
     */
    public const KEY_REQUIRE = 'require';

    /**
     * @var string
     */
    public const KEY_REQUIRE_DEV = 'require-dev';

    /**
     * @var string
     */
    public const KEY_AUTOLOAD = 'autoload';

    /**
     * @var string
     */
    public const KEY_AUTOLOAD_DEV = 'autoload-dev';

    /**
     * @var string
     */
    public const KEY_EXTRA = 'extra';

    /**
     * @var string
     */
    public const KEY_REPLACE = 'replace';

    /**
     * @var string
     */
    public const KEY_SCRIPTS = 'scripts';

    /**
     * @var string
     */
    public const KEY_CONFIG = 'config';

    /**
     * @var string
     */
    public const KEY_TYPE = 'type';

    /**
     * @var string
     */
    public const KEY_HOMEPAGE = 'homepage';

    /**
     * @var string
     */
    public const KEY_SUPPORT = 'support';

    /**
     * @var string
     */
    public const KEY_AUTHORS = 'authors';

    /**
     * @var string
     */
    public const KEY_KEYWORDS = 'keywords';

    /**
     * @var string
     */
    public const KEY_BIN = 'bin';

    /**
     * @var string
     */
    public const KEY_README = 'readme';

    /**
     * @var string
     */
    public const KEY_TIME = 'time';

    /**
     * @var string
     */
    public const KEY_FUNDING = 'funding';

    /**
     * @var string
     */
    public const KEY_INCLUDE_PATH = 'include-path';

    /**
     * @var string
     */
    public const KEY_TARGET_DIR = 'target-dir';

    /**
     * @var string
     */
    public const KEY_ARCHIVE = 'archive';

    /**
     * @var string
     */
    public const KEY_ABANDONED = 'abandoned';

    /**
     * @var string
     */
    public const KEY_NON_FEATURE_BRANCHES = 'non-feature-branches';

    /**
     * @var string
     */
    public const KEY_CONFLICT = 'conflict';

    /**
     * @var string
     */
    public const KEY_PROVIDE = 'provide';

    /**
     * @var string
     */
    public const KEY_SUGGEST = 'suggest';

    /**
     * @var string
     */
    private const ERROR_INVALID_KEY = 'The key "%s" looks like an invalid composer.json element';

    /**
     * @var string
     */
    private const ERROR_INVALID_VALUE = 'The given composer.json key "%s" contains an invalid value of type "%s"';

    /**
     * @var string
     */
    private const KEY_AUTOLOAD_CLASSMAP = 'classmap';

    /**
     * List of allowed composer file root keys and its types defined
     * as a callable match expression.
     *
     * @var callable[]
     */
    private const KEYS_ALLOWED = [
        self::KEY_NAME => 'is_string',
        self::KEY_TYPE => 'is_string',
        self::KEY_LICENSE => 'is_string',
        self::KEY_DESCRIPTION => 'is_string',
        self::KEY_KEYWORDS => 'is_array',
        self::KEY_README => 'is_string',
        self::KEY_TIME => 'is_string',
        self::KEY_FUNDING => 'is_array',
        self::KEY_HOMEPAGE => 'is_string',
        self::KEY_SUPPORT => 'is_array',
        self::KEY_AUTHORS => 'is_array',
        self::KEY_BIN => 'is_array',
        self::KEY_INCLUDE_PATH => 'is_array',
        self::KEY_REPOSITORIES => 'is_array',
        self::KEY_REQUIRE => 'is_array',
        self::KEY_REQUIRE_DEV => 'is_array',
        self::KEY_AUTOLOAD => 'is_array',
        self::KEY_AUTOLOAD_DEV => 'is_array',
        self::KEY_CONFLICT => 'is_array',
        self::KEY_PROVIDE => 'is_array',
        self::KEY_SUGGEST => 'is_array',
        self::KEY_TARGET_DIR => 'is_string',
        self::KEY_ARCHIVE => 'is_array',
        self::KEY_REPLACE => 'is_array',
        self::KEY_EXTRA => 'is_array',
        self::KEY_SCRIPTS => 'is_array',
        self::KEY_CONFIG => 'is_array',
        self::KEY_NON_FEATURE_BRANCHES => 'is_array',
        self::KEY_ABANDONED => 'is_bool',
        self::KEY_MINIMUM_STABILITY => 'is_string',
        self::KEY_PREFER_STABLE => 'is_bool',
    ];

    /**
     * List of composer file keys that should passed as array.
     *
     * @var string[]
     */
    private const KEYS_CASTS_TO_ARRAY = [self::KEY_BIN, self::KEY_INCLUDE_PATH];

    /**
     * @var string[]
     */
    private $orderedKeys = [];

    /**
     * @var SmartFileInfo|null
     */
    private $fileInfo;

    /**
     * @var mixed[]
     */
    private $data = [];

    public function __construct(array $data = [])
    {
        $this->merge($data);
    }

    public function merge(array $data): void
    {
        foreach ($data as $key => $value) {
            assert(is_string($key));

            try {
                $this->set($key, $value);
            } catch (\InvalidArgumentException $e) {
                // Just ignore non-registered composer key
            }
        }
    }

    /**
     * Sets a new value into composer data by its key
     *
     * @param mixed $value
     */
    public function set(string $key, $value): void
    {
        $value = $this->castKeyVal($key, $value);

        $this->assertKeyValIsValid($key, $value);

        //
        // Additional hardcode manipulations
        //
        switch ($key) {
            case self::KEY_REQUIRE:
            case self::KEY_REQUIRE_DEV:
                $value = $this->sortPackages($value);
                break;

            case self::KEY_REPLACE:
                ksort($value);
                break;
        }

        $this->data[$key] = $value;
    }

    public function getName(): ?string
    {
        return $this->get(self::KEY_NAME);
    }

    /**
     * Returns value from composer data by its key
     *
     * @param mixed|null $default
     * @return mixed|null
     */
    public function get(string $key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    public function setName(string $name): void
    {
        $this->set(self::KEY_NAME, $name);
    }

    public function getDescription(): ?string
    {
        return $this->get(self::KEY_DESCRIPTION);
    }

    public function setDescription(string $description): void
    {
        $this->set(self::KEY_DESCRIPTION, $description);
    }

    public function getLicense(): ?string
    {
        return $this->get(self::KEY_LICENSE);
    }

    public function setLicense(string $license): void
    {
        $this->set(self::KEY_LICENSE, $license);
    }

    public function getMinimumStability(): ?string
    {
        return $this->get(self::KEY_MINIMUM_STABILITY);
    }

    public function setMinimumStability(string $minimumStability): void
    {
        $this->set(self::KEY_MINIMUM_STABILITY, $minimumStability);
    }

    public function getPreferStable(): ?bool
    {
        return $this->get(self::KEY_PREFER_STABLE);
    }

    public function setPreferStable(bool $preferStable): void
    {
        $this->set(self::KEY_PREFER_STABLE, $preferStable);
    }

    public function removePreferStable(): void
    {
        $this->remove(self::KEY_PREFER_STABLE);
    }

    /**
     * Remove composer value by its key
     */
    public function remove(string $key): void
    {
        unset($this->data[$key]);
    }

    public function removeMinimumStability(): void
    {
        $this->remove(self::KEY_MINIMUM_STABILITY);
    }

    /**
     * @return array[]
     */
    public function getRepositories(): array
    {
        return $this->get(self::KEY_REPOSITORIES, []);
    }

    /**
     * @param array[] $repositories
     */
    public function setRepositories(array $repositories): void
    {
        $this->set(self::KEY_REPOSITORIES, $repositories);
    }

    /**
     * @param string[] $require
     */
    public function setRequire(array $require): void
    {
        $this->set(self::KEY_REQUIRE, $require);
    }

    /**
     * @param string[] $requireDev
     */
    public function setRequireDev(array $requireDev): void
    {
        $this->set(self::KEY_REQUIRE_DEV, $requireDev);
    }

    /**
     * @param array[] $autoload
     */
    public function setAutoload(array $autoload): void
    {
        $this->set(self::KEY_AUTOLOAD, $autoload);
    }

    /**
     * @param array[] $autoloadDev
     */
    public function setAutoloadDev(array $autoloadDev): void
    {
        $this->set(self::KEY_AUTOLOAD_DEV, $autoloadDev);
    }

    /**
     * @return array[]
     */
    public function getExtra(): array
    {
        return $this->get(self::KEY_EXTRA, []);
    }

    /**
     * @param array[] $extra
     */
    public function setExtra(array $extra): void
    {
        $this->set(self::KEY_EXTRA, $extra);
    }

    /**
     * @return string[]
     */
    public function getOrderedKeys(): array
    {
        return $this->orderedKeys;
    }

    /**
     * @param string[] $orderedKeys
     */
    public function setOrderedKeys(array $orderedKeys): void
    {
        $this->orderedKeys = $orderedKeys;
    }

    /**
     * @param string[] $replace
     */
    public function setReplace(array $replace): void
    {
        $this->set(self::KEY_REPLACE, $replace);
    }

    public function isReplacePackageSet(string $packageName): bool
    {
        return isset($this->getReplace()[$packageName]);
    }

    /**
     * @return string[]
     */
    public function getReplace(): array
    {
        return $this->get(self::KEY_REPLACE, []);
    }

    public function setReplacePackage(string $packageName, string $version): void
    {
        $replacements = $this->get(self::KEY_REPLACE);
        $replacements[$packageName] = $version;

        $this->set(self::KEY_REPLACE, $replacements);
    }

    public function getJsonArray(): array
    {
        // Except invalid "null" values
        $array = array_filter($this->data, static function ($value): bool {
            return $value !== null;
        });

        if (isset($array[self::KEY_MINIMUM_STABILITY])) {
            $this->moveValueToBack(self::KEY_MINIMUM_STABILITY);
        }

        if (isset($array[self::KEY_PREFER_STABLE])) {
            $this->moveValueToBack(self::KEY_PREFER_STABLE);
        }

        return $this->sortItemsByOrderedListOfKeys($array, $this->orderedKeys);
    }

    /**
     * @return array[]
     */
    public function getScripts(): array
    {
        return $this->get(self::KEY_SCRIPTS, []);
    }

    /**
     * @param array[] $scripts
     */
    public function setScripts(array $scripts): void
    {
        $this->set(self::KEY_SCRIPTS, $scripts);
    }

    /**
     * @return array[]
     */
    public function getConfig(): array
    {
        return $this->get(self::KEY_CONFIG, []);
    }

    /**
     * @param array[] $config
     */
    public function setConfig(array $config): void
    {
        $this->set(self::KEY_CONFIG, $config);
    }

    /**
     * @api
     */
    public function hasPackage(string $packageName): bool
    {
        if ($this->hasRequiredPackage($packageName)) {
            return true;
        }

        return $this->hasRequiredDevPackage($packageName);
    }

    /**
     * @api
     */
    public function hasRequiredPackage(string $packageName): bool
    {
        return isset($this->getRequire()[$packageName]);
    }

    /**
     * @return string[]
     */
    public function getRequire(): array
    {
        return $this->get(self::KEY_REQUIRE, []);
    }

    /**
     * @api
     */
    public function hasRequiredDevPackage(string $packageName): bool
    {
        return isset($this->getRequireDev()[$packageName]);
    }

    /**
     * @return string[]
     */
    public function getRequireDev(): array
    {
        return $this->get(self::KEY_REQUIRE_DEV, []);
    }

    public function getFileInfo(): ?SmartFileInfo
    {
        return $this->fileInfo;
    }

    /**
     * @return string[]
     */
    public function getAllClassmaps(): array
    {
        $autoloadClassmaps = $this->getAutoload()[self::KEY_AUTOLOAD_CLASSMAP] ?? [];
        $autoloadDevClassmaps = $this->getAutoloadDev()[self::KEY_AUTOLOAD_CLASSMAP] ?? [];

        return array_merge($autoloadClassmaps, $autoloadDevClassmaps);
    }

    /**
     * @return array[]
     */
    public function getAutoload(): array
    {
        return $this->get(self::KEY_AUTOLOAD, []);
    }

    /**
     * @return array[]
     */
    public function getAutoloadDev(): array
    {
        return $this->get(self::KEY_AUTOLOAD_DEV, []);
    }

    public function setOriginalFileInfo(SmartFileInfo $fileInfo): void
    {
        $this->fileInfo = $fileInfo;
    }

    public function has(string $key): bool
    {
        return isset($this->data[$key]);
    }

    /**
     * Some of the composer elements accept both strings and arrays. Both
     * entries are correct, but they should be cast into an array.
     *
     * For example:
     *
     * <code>
     *  {"bin": "path/to/binary"}
     * </code>
     *
     * <code>
     *  {"bin": ["path/to/binary"]}
     * <code>
     *
     * @param mixed $value
     * @return mixed
     */
    private function castKeyVal(string $key, $value)
    {
        if (in_array($key, self::KEYS_CASTS_TO_ARRAY, true)) {
            return is_array($value) ? $value : [$value];
        }

        return $value;
    }

    /**
     * Validates that the value of composer data is correct by its key.
     *
     * @param mixed $value
     */
    private function assertKeyValIsValid(string $key, $value): void
    {
        if (! isset(self::KEYS_ALLOWED[$key])) {
            throw new \InvalidArgumentException(sprintf(self::ERROR_INVALID_KEY, $key), 1);
        }

        /** @var callable $matcher */
        $matcher = self::KEYS_ALLOWED[$key];

        if (! $matcher($value)) {
            throw new \InvalidArgumentException(sprintf(self::ERROR_INVALID_VALUE, $key, gettype($value)), 2);
        }
    }

    /**
     * @param string[] $packages
     * @return string[]
     */
    private function sortPackages(array $packages): array
    {
        return (new PrivatesCaller())
            ->callPrivateMethodWithReference(JsonManipulator::class, 'sortPackages', $packages);
    }

    private function moveValueToBack(string $valueName): void
    {
        $key = array_search($valueName, $this->orderedKeys, true);
        if ($key !== false) {
            unset($this->orderedKeys[$key]);
        }

        $this->orderedKeys[] = $valueName;
    }

    /**
     * 2. sort item by prescribed key order
     *
     * @see https://www.designcise.com/web/tutorial/how-to-sort-an-array-by-keys-based-on-order-in-a-secondary-array-in-php
     * @param mixed[] $contentItems
     * @param string[] $orderedVisibleItems
     * @return mixed[]
     */
    private function sortItemsByOrderedListOfKeys(array $contentItems, array $orderedVisibleItems): array
    {
        uksort($contentItems, static function ($firstContentItem, $secondContentItem) use ($orderedVisibleItems): int {
            $firstItemPosition = array_search($firstContentItem, $orderedVisibleItems, true);
            $secondItemPosition = array_search($secondContentItem, $orderedVisibleItems, true);

            return $firstItemPosition <=> $secondItemPosition;
        });

        return $contentItems;
    }
}
