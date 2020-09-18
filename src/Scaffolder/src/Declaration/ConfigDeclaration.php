<?php

/**
 * Spiral Framework. Scaffolder
 *
 * @license MIT
 * @author  Valentin V (vvval)
 */

declare(strict_types=1);

namespace Spiral\Scaffolder\Declaration;

use Cocur\Slugify\SlugifyInterface;
use Doctrine\Inflector\Rules\English\InflectorFactory;
use Spiral\Core\InjectableConfig;
use Spiral\Files\FilesInterface;
use Spiral\Reactor\ClassDeclaration;
use Spiral\Reactor\DependedInterface;
use Spiral\Reactor\FileDeclaration;
use Spiral\Reactor\Partial\Method;
use Spiral\Reactor\Partial\Source;
use Spiral\Scaffolder\Config\ScaffolderConfig;
use Spiral\Scaffolder\Exception\ScaffolderException;

use function Spiral\Scaffolder\defineArrayType;
use function Spiral\Scaffolder\isAssociativeArray;

class ConfigDeclaration extends ClassDeclaration implements DependedInterface
{
    /** @var ScaffolderConfig */
    private $config;

    /** @var FilesInterface */
    private $files;

    /** @var SlugifyInterface */
    private $slugify;

    /** @var ConfigDeclaration\TypeAnnotations */
    private $typeAnnotations;

    /** @var ConfigDeclaration\TypeHints */
    private $typeHints;

    /** @var ConfigDeclaration\Defaults */
    private $defaultValues;

    /** @var string */
    private $configName;

    /** @var string */
    private $directory;

    /**
     * @param ScaffolderConfig                  $config
     * @param FilesInterface                    $files
     * @param SlugifyInterface                  $slugify
     * @param ConfigDeclaration\TypeAnnotations $typeAnnotations
     * @param ConfigDeclaration\TypeHints       $typeHints
     * @param ConfigDeclaration\Defaults        $defaultValues
     * @param string                            $configName
     * @param string                            $name
     * @param string                            $comment
     * @param string                            $directory
     */
    public function __construct(
        ScaffolderConfig $config,
        FilesInterface $files,
        SlugifyInterface $slugify,
        ConfigDeclaration\TypeAnnotations $typeAnnotations,
        ConfigDeclaration\TypeHints $typeHints,
        ConfigDeclaration\Defaults $defaultValues,
        string $configName,
        string $name,
        string $comment = '',
        string $directory = ''
    ) {
        parent::__construct($name, 'InjectableConfig', [], $comment);

        $this->config = $config;
        $this->files = $files;
        $this->slugify = $slugify;
        $this->typeAnnotations = $typeAnnotations;
        $this->typeHints = $typeHints;
        $this->defaultValues = $defaultValues;
        $this->directory = $directory;
        $this->configName = $configName;
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies(): array
    {
        return [InjectableConfig::class => null];
    }

    /**
     * @param bool $reverse
     */
    public function create(bool $reverse): void
    {
        $filename = $this->makeConfigFilename($this->configName);
        if ($reverse) {
            if (!$this->files->exists($filename)) {
                throw new ScaffolderException("Config filename $filename doesn't exist");
            }

            $defaultsFromFile = require $filename;
            $this->declareGetters($defaultsFromFile);
            $this->declareStructure($this->configName, $this->defaultValues->get($defaultsFromFile));
        } else {
            if (!$this->files->exists($filename)) {
                $this->touchConfigFile($filename);
            }

            $this->declareStructure($this->configName, []);
        }
    }

    /**
     * @param string $filename
     * @return string
     */
    private function makeConfigFilename(string $filename): string
    {
        return "{$this->directory}{$filename}.php";
    }

    /**
     * @param string $filename
     */
    private function touchConfigFile(string $filename): void
    {
        $this->files->touch($filename);

        $file = new FileDeclaration();
        $file->setDirectives('strict_types=1');
        $file->setComment($this->phpDocSeeReference());
        $file->addElement(new Source(['', 'return [];']));
        $file->render();

        $this->files->write(
            $filename,
            $file->render(),
            FilesInterface::READONLY,
            true
        );
    }

    /**
     * @return string
     */
    private function phpDocSeeReference(): string
    {
        $namespace = trim($this->config->classNamespace('config', $this->getName()), '\\');

        return "@see \\$namespace\\{$this->getName()}";
    }

    /**
     * @param array $defaults
     * @return double[]|float[]
     */
    private function declareGetters(array $defaults): array
    {
        $output = [];
        $getters = [];
        $gettersByKey = [];

        foreach ($defaults as $key => $value) {
            $key = (string)$key;
            $getter = $this->makeGetterName($key);
            $getters[] = $getter;

            $method = $this->method($getter)->setPublic();
            $method->setSource("return \$this->config['$key'];");
            $method->setComment("@return {$this->typeAnnotations->getAnnotation($value)}");

            if (is_array($value)) {
                $gettersByKey[] = compact('key', 'value');
            }

            $returnTypeHint = $this->typeHints->getHint(gettype($value));
            if ($returnTypeHint !== null) {
                $method->setReturn($returnTypeHint);
            }
        }

        foreach ($gettersByKey as $item) {
            $method = $this->declareGettersByKey($getters, $item['key'], $item['value']);
            if ($method !== null) {
                $getters[] = $method->getName();
            }
        }

        return $output;
    }

    /**
     * @param array  $methodNames
     * @param string $key
     * @param array  $value
     * @return Method|null
     */
    private function declareGettersByKey(array $methodNames, string $key, array $value): ?Method
    {
        //Won't create if there's less than 2 sub-items
        if (count($value) < 2) {
            return null;
        }

        $singularKey = $this->singularize($key);
        $name = $this->makeGetterName($singularKey);
        if (in_array($name, $methodNames, true)) {
            $name = $this->makeGetterName($singularKey, 'get', 'by');
        }

        //Name conflict, won't merge
        if (in_array($name, $methodNames, true)) {
            return null;
        }

        $keyType = defineArrayType(array_keys($value), '-mixed-');
        $valueType = defineArrayType(array_values($value), '-mixed-');
        //We need a fixed structure here
        if ($keyType === '-mixed-' || $valueType === '-mixed-') {
            return null;
        }

        //Won't create for associated arrays
        if ($this->typeAnnotations->mapType($keyType) === 'int' && !isAssociativeArray($value)) {
            return null;
        }

        $method = $this->method($name)->setPublic();
        $method->setSource("return \$this->config['$key'][\$$singularKey];");
        $method->setReturn($valueType);
        $method->setComment([
            "@param {$this->typeAnnotations->mapType($keyType)} $singularKey",
            "@return {$this->typeAnnotations->getAnnotation(array_values($value)[0])}"
        ]);

        $param = $method->parameter($singularKey);
        $paramTypeHint = $this->typeHints->getHint($keyType);
        if ($paramTypeHint !== null) {
            $param->setType($paramTypeHint);
        }

        return $method;
    }

    /**
     * @param string $name
     * @param string $prefix
     * @param string $postfix
     * @return string
     */
    private function makeGetterName(string $name, string $prefix = 'get', string $postfix = ''): string
    {
        $chunks = [];
        if (!empty($prefix)) {
            $chunks[] = $prefix;
        }

        $name = $this->slugify->slugify($name, ['lowercase' => false]);
        $chunks[] = count($chunks) !== 0 ? $this->classify($name) : $name;
        if (!empty($postfix)) {
            $chunks[] = ucfirst($postfix);
        }

        return implode('', $chunks);
    }

    /**
     * Declare constant and property.
     *
     * @param string $configName
     * @param array  $defaults
     */
    private function declareStructure(string $configName, array $defaults): void
    {
        $this->constant('CONFIG')->setPublic()->setValue($configName);
        $this->property('config')->setProtected()->setDefaultValue($defaults)
            ->setComment('@internal For internal usage. Will be hydrated in the constructor.');
    }

    /**
     * @param string $name
     * @return string
     */
    private function classify(string $name): string
    {
        return ( new InflectorFactory() )
            ->build()
            ->classify($name);
    }

    /**
     * @param string $name
     * @return string
     */
    private function singularize(string $name): string
    {
        return ( new InflectorFactory() )
            ->build()
            ->singularize($name);
    }
}
