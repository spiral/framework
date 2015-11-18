<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Reactor\Generators;

use Doctrine\Common\Inflector\Inflector;
use Spiral\Core\Controller;
use Spiral\Http\Exceptions\ClientException;
use Spiral\Reactor\AbstractElement;
use Spiral\Reactor\Generators\Prototypes\AbstractService;

/**
 * Generates controller classes.
 */
class ControllerGenerator extends AbstractService
{
    /**
     * {@inheritdoc}
     */
    protected function generate()
    {
        $this->file->addUse(Controller::class);
        $this->class->setExtends('Controller');
    }

    /**
     * Generate action method.
     *
     * @param string $action
     */
    public function addAction($action)
    {
        $retrieve = $this->class->method(
            Controller::ACTION_PREFIX . $action . Controller::ACTION_POSTFIX
        )->setComment([
            "@return mixed"
        ]);

        $retrieve->setAccess(AbstractElement::ACCESS_PROTECTED);
    }

    /**
     * Generate GRUD methods using data entity service.
     *
     * @param string $name
     * @param string $serviceClass
     * @param string $request
     * @param string $requestClass
     */
    public function createCRUD($name, $serviceClass, $request = '', $requestClass = '')
    {
        $this->file->addUse(ClientException::class);

        $plural = Inflector::pluralize($name);
        $this->addDependency($name, $serviceClass);
        $this->class->property('defaultAction', "@var string")->setDefault(true, 'retrieve');

        //Let's generate some fun!
        $retrieve = $this->class->method('retrieveAction')->setComment([
            "Retrieve all entities selected from {$serviceClass} and render them using view '{$plural}/list'.",
            "",
            "@return string"
        ]);

        //Let's include pagination
        $retrieve->setSource([
            "return \$this->views->render('{$plural}/list', [",
            "    'list' => \$this->{$plural}->find()->paginate(50)",
            "]);"
        ]);

        $retrieve->setAccess(AbstractElement::ACCESS_PROTECTED);

        //Let's generate some fun!
        $show = $this->class->method('showAction')->setComment([
            "Fetch one entity from {$serviceClass} and render it using view '{$plural}/show'.",
            "",
            "@param string \$id",
            "@return string"
        ]);

        $show->parameter('id');
        $show->setSource([
            "if (empty(\$entity = \$this->{$plural}->findByPK(\$id))) {",
            "    throw new ClientException(ClientException::NOT_FOUND);",
            "}",
            "",
            "return \$this->views->render('{$plural}/show', compact('entity'));"
        ]);

        $show->setAccess(AbstractElement::ACCESS_PROTECTED);

        //Create new entity form
        $create = $this->class->method('createAction')->setComment([
            "Create new entity using view '{$plural}/create'.",
            "",
            "@return string"
        ]);

        $create->setSource([
            "return \$this->views->render('{$plural}/create', ['entity' => \$this->{$plural}->create()]);"
        ]);

        $create->setAccess(AbstractElement::ACCESS_PROTECTED);

        //Edit existed entity form
        $edit = $this->class->method('editAction')->setComment([
            "Edit existed entity form using view '{$plural}/edit'.",
            "",
            "@param string \$id",
            "@return string"
        ]);

        $edit->parameter('id');
        $edit->setSource([
            "if (empty(\$entity = \$this->{$plural}->findByPK(\$id))) {",
            "    throw new ClientException(ClientException::NOT_FOUND);",
            "}",
            "",
            "return \$this->views->render('{$plural}/edit', compact('entity'));"
        ]);

        $edit->setAccess(AbstractElement::ACCESS_PROTECTED);

        //Let's generate some fun!
        $save = $this->class->method('saveAction')->setComment([
            "Update existed or create new entity using {$serviceClass}. JSON will be returned.",
            "",
            "@param string \$id"
        ]);

        //We are going to fetch entity id from route parameters
        $save->parameter('id')->setOptional(true, null);

        if (!empty($request)) {
            $this->file->addUse($requestClass);
            $reflection = new \ReflectionClass($requestClass);
            $save->parameter($request, $reflection->getShortName())->setType(
                $reflection->getShortName()
            );

            $save->setSource([
                "if (!empty(\$id)) {",
                "    \$entity = \$this->{$plural}->findByPK(\$id);",
                "    if (empty(\$entity)) {",
                "        throw new ClientException(ClientException::NOT_FOUND);",
                "    }",
                "} else {",
                "    \$entity = \$this->{$plural}->create();",
                "}",
                "",
                "if (!\${$request}->populate(\$entity)) {",
                "    return [",
                "        'status' => ClientException::BAD_DATA,",
                "        'errors' => \${$request}->getErrors()",
                "    ];",
                "}",
                "",
                "//Entity must be validated by request at this point",
                "if (!\$this->{$plural}->save(\$entity, true)) {",
                "    return [",
                "        'status' => ClientException::ERROR,",
                "        'error'  => 'Unable to save entity.'",
                "    ];",
                "}",
                "",
                "if (empty(\$id)) {",
                "    return [",
                "        'status'  => 201,",
                "        'message' => 'Created, redirecting...',",
                "        'action'  => [",
                "            'delay'    => 2000,",
                "            'redirect' => \$this->router->createUri(",
                "                '{$this->getName()}::edit', ['id' => (string)\$entity->primaryKey()]",
                "            )",
                "        ]",
                "    ];",
                "}",
                "",
                "return ['status' => 200, 'message' => 'Updated'];"
            ]);
        } else {
            $save->setSource([
                "if (!empty(\$id)) {",
                "    \$entity = \$this->{$plural}->findByPK(\$id);",
                "    if (empty(\$entity)) {",
                "        throw new ClientException(ClientException::NOT_FOUND);",
                "    }",
                "} else {",
                "    \$entity = \$this->{$plural}->create();",
                "}",
                "",
                "//Use true as second parameter to bypass non fillable flag",
                "\$entity->setFields(\$this->input->data);",
                "if (!\$this->{$plural}->save(\$entity, true, \$errors)) {",
                "    return [",
                "        'status' => ClientException::BAD_DATA,",
                "        'errors' => \$errors",
                "    ];",
                "}",
                "",
                "if (!empty(\$id)) {",
                "   return ['status' => 200, 'message' => 'Updated'];",
                "}",
                "",
                "return [",
                "    'status'  => 201,",
                "    'message' => 'Created, redirecting...',",
                "    'action'  => [",
                "        'delay'    => 2000,",
                "        'redirect' => \$this->router->createUri(",
                "            '{$this->getName()}::edit', ['id' => (string)\$entity->primaryKey()]",
                "        )",
                "    ]",
                "];",
            ]);
        }

        $save->setComment("@return array", true);
        $save->setAccess(AbstractElement::ACCESS_PROTECTED);

        //Let's generate some fun!
        $delete = $this->class->method('deleteAction')->setComment([
            "Delete one entity using it's primary key and service {$serviceClass}. JSON will be returned.",
            "",
            "@param string \$id",
            "@return array"
        ]);

        $delete->parameter('id');
        $delete->setSource([
            "if (empty(\$entity = \$this->{$plural}->findByPK(\$id))) {",
            "    throw new ClientException(ClientException::NOT_FOUND);",
            "}",
            "",
            "\$this->{$plural}->delete(\$entity);",
            "",
            "return ['status' => 200, 'message' => 'Deleted'];"
        ]);

        $delete->setAccess(AbstractElement::ACCESS_PROTECTED);
    }
}