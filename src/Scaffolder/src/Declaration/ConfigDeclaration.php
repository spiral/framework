<?php

declare(strict_types=1);

namespace Spiral\Scaffolder\Declaration;

use Cocur\Slugify\SlugifyInterface;
use Doctrine\Inflector\Rules\English\InflectorFactory;
use Nette\PhpGenerator\Dumper;
use Nette\PhpGenerator\Literal;
use Spiral\Boot\DirectoriesInterface;
use Spiral\Core\InjectableConfig;
use Spiral\Files\FilesInterface;
use Spiral\Reactor\FileDeclaration;
use Spiral\Reactor\Partial\Method;
use Spiral\Scaffolder\Config\ScaffolderConfig;
use Spiral\Scaffolder\Exception\ScaffolderException;

use function Spiral\Scaffolder\defineArrayType;

class ConfigDeclaration extends AbstractDeclaration implements HasInstructions
{
    public const TYPE = 'config';

    public function __construct(
        ScaffolderConfig $config,
        protected readonly FilesInterface $files,
        protected readonly DirectoriesInterface $dirs,
        protected readonly SlugifyInterface $slugify,
        protected readonly ConfigDeclaration\TypeAnnotations $typeAnnotations,
        protected readonly ConfigDeclaration\TypeHints $typeHints,
        protected readonly ConfigDeclaration\Defaults $defaultValues,
        protected string $name,
        protected ?string $comment = null,
        private readonly string $directory = '',
        ?string $namespace = null,
    ) {
        parent::__construct($config, $name, $comment, $namespace);
    }

    public function create(bool $reverse, string $configName): void
    {
        $this->class->addConstant('CONFIG', $configName)->setPublic();

        $filename = $this->makeConfigFilename($configName);
        if ($reverse) {
            if (!$this->files->exists($filename)) {
                throw new ScaffolderException(\sprintf("Config filename %s doesn't exist", $filename));
            }

            $defaultsFromFile = require $filename;
            $this->declareGetters($defaultsFromFile);

            $this->class->getProperty('config')->setValue($this->defaultValues->get($defaultsFromFile));
        } elseif (!$this->files->exists($filename)) {
            $this->touchConfigFile($filename);
        }
    }

    /**
     * Declare constant and property.
     */
    public function declare(): void
    {
        $this->namespace->addUse(InjectableConfig::class);

        $this->class->setExtends(InjectableConfig::class);
        $this->class->setFinal();

        $this->class
            ->addProperty('config')
            ->setProtected()
            ->setType('array')
            ->setValue([])
            ->setComment(
                <<<'DOC'
                Default values for the config.
                Will be merged with application config in runtime.
                DOC,
            );
    }

    public function getInstructions(): array
    {
        $configFile = $this->makeConfigFilename(
            $this->class->getConstant('CONFIG')->getValue()
        );

        $configFile = \str_replace($this->dirs->get('root'), '', $configFile);

        return [
            \sprintf('You can now add your config values to the \'<comment>%s</comment>\' file.', $configFile),
            'Read more about Config Objects in the documentation: https://spiral.dev/docs/framework-config',
        ];
    }

    private function makeConfigFilename(string $filename): string
    {
        return \sprintf('%s%s.php', $this->directory, $filename);
    }

    private function touchConfigFile(string $filename): void
    {
        $this->files->touch($filename);

        $file = new FileDeclaration();
        $file->setComment($this->phpDocSeeReference());

        $this->files->write(
            $filename,
            $file->render() . PHP_EOL . (new Dumper())->dump(new Literal('return [];')),
            FilesInterface::READONLY,
            true,
        );
    }

    private function phpDocSeeReference(): string
    {
        $namespace = \trim($this->config->classNamespace('config', $this->class->getName()), '\\');

        return \sprintf('@see \%s\%s', $namespace, $this->class->getName());
    }

    private function declareGetters(array $defaults): void
    {
        $getters = [];
        $gettersByKey = [];

        foreach ($defaults as $key => $value) {
            $key = (string)$key;
            $getter = $this->makeGetterName($key);
            $getters[] = $getter;

            $method = $this->class->addMethod($getter)->setPublic();
            $method->setBody(\sprintf('return $this->config[\'%s\'];', $key));

            if (\is_array($value)) {
                $gettersByKey[] = ['key' => $key, 'value' => $value];
            }

            $returnTypeHint = $this->typeHints->getHint(\gettype($value));
            if ($returnTypeHint !== null) {
                $method->setReturnType($returnTypeHint);
            }
        }

        foreach ($gettersByKey as $item) {
            $method = $this->declareGettersByKey($getters, $item['key'], $item['value']);
            if ($method !== null) {
                $getters[] = $method->getName();
            }
        }
    }

    private function declareGettersByKey(array $methodNames, string $key, array $value): ?Method
    {
        //Won't create if there's less than 2 sub-items
        if (\count($value) < 2) {
            return null;
        }

        $singularKey = $this->singularize($key);
        $name = $this->makeGetterName($singularKey);
        if (\in_array($name, $methodNames, true)) {
            $name = $this->makeGetterName($singularKey, 'get', 'by');
        }

        //Name conflict, won't merge
        if (\in_array($name, $methodNames, true)) {
            return null;
        }

        $keyType = defineArrayType(\array_keys($value), '-mixed-');
        $valueType = defineArrayType(\array_values($value), '-mixed-');
        //We need a fixed structure here
        if ($keyType === '-mixed-' || $valueType === '-mixed-') {
            return null;
        }

        //Won't create for associated arrays
        if ($this->typeAnnotations->mapType($keyType) === 'int' && \array_is_list($value)) {
            return null;
        }

        $method = $this->class->addMethod($name)->setPublic();
        $method->setBody(\sprintf('return $this->config[\'%s\'][$%s];', $key, $singularKey));
        $method->setReturnType($valueType);

        $param = $method->addParameter($singularKey);
        $paramTypeHint = $this->typeHints->getHint($keyType);
        if ($paramTypeHint !== null) {
            $param->setType($paramTypeHint);
        }

        return $method;
    }

    private function makeGetterName(string $name, string $prefix = 'get', string $postfix = ''): string
    {
        $chunks = [];
        if (!empty($prefix)) {
            $chunks[] = $prefix;
        }

        $name = $this->slugify->slugify($name, ['lowercase' => false]);
        $chunks[] = \count($chunks) !== 0 ? $this->classify($name) : $name;
        if (!empty($postfix)) {
            $chunks[] = \ucfirst($postfix);
        }

        return \implode('', $chunks);
    }

    private function classify(string $name): string
    {
        return (new InflectorFactory())
            ->build()
            ->classify($name);
    }

    private function singularize(string $name): string
    {
        return (new InflectorFactory())
            ->build()
            ->singularize($name);
    }
}
