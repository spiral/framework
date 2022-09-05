<?php

declare(strict_types=1);

namespace Spiral\Reactor;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\GlobalFunction;
use Nette\PhpGenerator\Helpers;
use Nette\PhpGenerator\PhpNamespace as NettePhpNamespace;
use Nette\PhpGenerator\Factory;
use Nette\PhpGenerator\PhpFile;
use Spiral\Reactor\Aggregator\Classes;
use Spiral\Reactor\Aggregator\Functions;
use Spiral\Reactor\Aggregator\Namespaces;
use Spiral\Reactor\Partial\PhpNamespace;
use Spiral\Reactor\Traits\CommentAware;

/**
 * Provides ability to render file content.
 */
class FileDeclaration implements \Stringable, DeclarationInterface
{
    use CommentAware;

    private PhpFile $element;

    public function __construct()
    {
        $this->element = new PhpFile();
        $this->element->setStrictTypes(true);
    }

    public function __toString(): string
    {
        return (new Printer())->print($this);
    }

    public static function fromCode(string $code): static
    {
        return self::fromElement((new Factory())->fromCode($code));
    }

    public function addClass(string $name): ClassDeclaration
    {
        return ClassDeclaration::fromElement($this->element->addClass($name));
    }

    public function addInterface(string $name): InterfaceDeclaration
    {
        return InterfaceDeclaration::fromElement($this->element->addInterface($name));
    }

    public function addTrait(string $name): TraitDeclaration
    {
        return TraitDeclaration::fromElement($this->element->addTrait($name));
    }

    public function addEnum(string $name): EnumDeclaration
    {
        return EnumDeclaration::fromElement($this->element->addEnum($name));
    }

    public function addNamespace(string|PhpNamespace $namespace): PhpNamespace
    {
        if ($namespace instanceof PhpNamespace) {
            $this->element->addNamespace($namespace->getElement());

            return $namespace;
        }

        return PhpNamespace::fromElement($this->element->addNamespace($namespace));
    }

    public function addFunction(string $name): FunctionDeclaration
    {
        return FunctionDeclaration::fromElement($this->element->addFunction($name));
    }

    public function getNamespaces(): Namespaces
    {
        return new Namespaces(\array_map(
            static fn (NettePhpNamespace $namespace) => PhpNamespace::fromElement($namespace),
            $this->element->getNamespaces()
        ));
    }

    public function getClasses(): Classes
    {
        return new Classes(\array_map(
            static fn (ClassType $class) => ClassDeclaration::fromElement($class),
            $this->element->getClasses()
        ));
    }

    public function getClass(string $name): ClassDeclaration
    {
        /**
         * @psalm-suppress InternalClass
         * @psalm-suppress InternalMethod
         */
        return $this->getClasses()->get(Helpers::extractShortName($name));
    }

    public function getFunctions(): Functions
    {
        return new Functions(\array_map(
            static fn (GlobalFunction $function) => FunctionDeclaration::fromElement($function),
            $this->element->getFunctions()
        ));
    }

    public function addUse(string $name, ?string $alias = null, string $of = NettePhpNamespace::NameNormal): static
    {
        $this->element->addUse($name, $alias, $of);

        return $this;
    }

    /**
     * Adds declare(strict_types=1) to output.
     */
    public function setStrictTypes(bool $on = true): static
    {
        $this->element->setStrictTypes($on);

        return $this;
    }

    public function hasStrictTypes(): bool
    {
        return $this->element->hasStrictTypes();
    }

    /**
     * @internal
     */
    public static function fromElement(PhpFile $element): static
    {
        $file = new static();

        $file->element = $element;

        return $file;
    }

    /**
     * @internal
     */
    public function getElement(): PhpFile
    {
        return $this->element;
    }

    public function render(): string
    {
        return $this->__toString();
    }
}
