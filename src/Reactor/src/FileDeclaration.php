<?php

declare(strict_types=1);

namespace Spiral\Reactor;

use Nette\PhpGenerator\ClassLike;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\EnumType;
use Nette\PhpGenerator\GlobalFunction;
use Nette\PhpGenerator\InterfaceType;
use Nette\PhpGenerator\PhpNamespace as NettePhpNamespace;
use Nette\PhpGenerator\Factory;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\TraitType;
use Spiral\Reactor\Aggregator\Elements;
use Spiral\Reactor\Aggregator\Functions;
use Spiral\Reactor\Aggregator\Namespaces;
use Spiral\Reactor\Partial\PhpNamespace;
use Spiral\Reactor\Traits;

/**
 * Provides ability to render file content.
 */
class FileDeclaration implements \Stringable, DeclarationInterface
{
    use Traits\CommentAware;
    use Traits\EnumAware;
    use Traits\ClassAware;
    use Traits\InterfaceAware;
    use Traits\TraitAware;

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

    public static function fromReflection(\ReflectionClass $reflection): static
    {
        return self::fromElement((new Factory())->fromCode(\file_get_contents($reflection->getFileName())));
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

    public function getElements(): Elements
    {
        return new Elements(\array_map(
            static fn (ClassLike $element) => match (true) {
                $element instanceof ClassType => ClassDeclaration::fromElement($element),
                $element instanceof InterfaceType => InterfaceDeclaration::fromElement($element),
                $element instanceof TraitType => TraitDeclaration::fromElement($element),
                $element instanceof EnumType => EnumDeclaration::fromElement($element)
            },
            $this->element->getClasses()
        ));
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
