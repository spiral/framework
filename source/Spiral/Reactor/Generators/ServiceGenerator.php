<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Reactor\Generators;

use Spiral\Core\Service;
use Spiral\ODM\Document;
use Spiral\ODM\Entities\Collection;
use Spiral\ORM\Entities\Selector;
use Spiral\ORM\Record;
use Spiral\Reactor\Generators\Prototypes\AbstractService;
use Spiral\Validation\ValidatesInterface;

/**
 * Generate service class and some of it's methods. Allows to create singleton services. In future
 * more complex patterns must be implemented.
 */
class ServiceGenerator extends AbstractService
{
    /**
     * {@inheritdoc}
     */
    protected function generate()
    {
        $this->file->addUse(Service::class);
        $this->class->setParent('Service');
    }

    /**
     * Associate model and generate set of model related methods.
     *
     * @param string $name
     * @param string $class
     */
    public function associateModel($name, $class)
    {
        $this->file->addUse($class);
        $this->file->addUse(ValidatesInterface::class);

        $reflection = new \ReflectionClass($class);
        $shortClass = $reflection->getShortName();

        $selection = "{$shortClass}[]";
        if ($reflection->isSubclassOf(Record::class)) {
            $this->file->addUse(Selector::class);
            $selection .= "|Selector";
        } elseif ($reflection->isSubclassOf(Document::class)) {
            $this->file->addUse(Collection::class);
            $selection .= "|Collection";
        }

        /**
         * Create new entity method.
         */
        $create = $this->class->method('create');
        $create->setComment([
            "Create new {$shortClass}. You must save entity using save method..",
            "",
            "@param array|\\Traversable \$fields Initial set of fields.",
            "@return {$shortClass}"
        ]);
        $create->parameter('fields')->setOptional(true, []);
        $create->setSource([
            "return {$shortClass}::create(\$fields);"
        ]);

        /**
         * Save entity method.
         */
        $save = $this->class->method('save');
        $save->setComment([
            "Save {$shortClass} instance.",
            "",
            "@param {$shortClass} \${$name}",
            "@param bool  \$validate",
            "@param array \$errors Will be populated if save fails.",
            "@return bool"
        ]);

        $save->parameter($name)->setType($shortClass);
        $save->parameter("validate")->setOptional(true, true);
        $save->parameter("errors")->setOptional(true, null)->setPBR(true);
        $save->setSource([
            "if (\${$name}->save(\$validate)) {",
            "    return true;",
            "}",
            "",
            "\$errors = \${$name}->getErrors();",
            "",
            "return false;"
        ]);

        /**
         * Delete entity method.
         */
        $delete = $this->class->method('delete');
        $delete->setComment([
            "Delete {$shortClass}.",
            "",
            "@param {$shortClass} \${$name}",
            "@return bool"
        ]);
        $delete->parameter($name)->setType($shortClass);
        $delete->setSource("return \${$name}->delete();");

        /**
         * Find entity by it's primary key.
         */
        $findPrimary = $this->class->method('findByPK');
        $findPrimary->setComment([
            "Find {$shortClass} it's primary key.",
            "",
            "@param mixed \$primaryKey",
            "@return {$shortClass}|null"
        ]);

        $findPrimary->parameter("primaryKey");
        $findPrimary->setSource("return {$shortClass}::findByPK(\$primaryKey);");

        /**
         * Find entity using where conditions.
         */
        $find = $this->class->method('find');
        $find->setComment([
            "Find {$shortClass} using set of where conditions.",
            "",
            "@param array \$where",
            "@return $selection"
        ]);

        $find->parameter("where")->setType('array')->setOptional(true, []);
        $find->setSource("return {$shortClass}::find(\$where);");
    }
}