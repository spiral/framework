<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Reactor\Generators;

use Doctrine\Common\Inflector\Inflector;
use Spiral\Core\Controller;
use Spiral\Http\Exceptions\ClientException;
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
        $this->class->setParent('Controller');
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
        $retrieve = $this->class->method('retrieve')->setComment([
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

        //Let's generate some fun!
        $show = $this->class->method('show')->setComment([
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

        //Create new entity form
        $create = $this->class->method('show')->setComment([
            "Create new entity using view '{$plural}/create'.",
            "",
            "@return string"
        ]);

        $create->setSource([
            "return \$this->views->render('{$plural}/create', ['entity' => \$this->{$plural}->create()]);"
        ]);

        //Edit existed entity form
        $edit = $this->class->method('show')->setComment([
            "Edit existed entity form using view '{$plural}/edit'.",
            "",
            "@param string \$id",
            "@return string"
        ]);

        $edit->setSource([
            "if (empty(\$entity = \$this->{$plural}->findByPK(\$id))) {",
            "    throw new ClientException(ClientException::NOT_FOUND);",
            "}",
            "",
            "return \$this->views->render('{$plural}/edit', compact('entity'));"
        ]);

        //Let's generate some fun!
        $save = $this->class->method('save')->setComment([
            "Update existed or create new entity using {$serviceClass}.",
            "",
            "@param string \$id",
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
                "if (!empty(\$id) && empty(\$entity = \$this->{$plural}->findByPK(\$id))) {",
                "    throw new ClientException(ClientException::NOT_FOUND);",
                "} else {",
                "    \$entity =  $this->{$plural}->create();",
                "}",
                "",
                "if (!\${$request}->isValid()) {",
                "    return [",
                "        'status' => ClientException::BAD_DATA,",
                "        'errors' => \${$request}->getErrors()",
                "    ];",
                "}",
                "",
                "\$entity->setFields(\${$request});",
                "if (!\$this->{$plural}->save(\$entity, true, \$errors)) {",
                "    return [",
                "        'status' => ClientException::BAD_DATA,",
                "        'errors' => \$errors",
                "    ];",
                "}",
                "",
                "if (empty(\$id)) {",
                "    return [",
                "        'status' => 201,",
                "        'message' => 'Created'",
                "        'action'  => [",
                "            'redirect' => (string)\$this->router->createUri('" . $this->getName() . "::edit', ['id' => \$entity->primaryKey()]),",
                "            'delay'    => 5",
                "        ]",
                "    ];",
                "}",
                "",
                "return ['status' => 204, 'message' => 'Updated'];"
            ]);
        } else {
            $save->setSource([
                "if (!empty(\$id) && empty(\$entity = \$this->{$plural}->findByPK(\$id))) {",
                "    throw new ClientException(ClientException::NOT_FOUND);",
                "} else {",
                "    \$entity =  $this->{$plural}->create();",
                "}",
                "",
                "\$entity->setFields(\$this->input->data);",
                "if (!\$this->{$plural}->save(\$entity, true, \$errors)) {",
                "    return [",
                "        'status' => ClientException::BAD_DATA,",
                "        'errors' => \$errors",
                "    ];",
                "}",
                "",
                "if (empty(\$id)) {",
                "    return [",
                "        'status' => 201,",
                "        'message' => 'Created'",
                "        'action'  => [",
                "            'redirect' => (string)\$this->router->createUri('" . $this->getName() . "::edit', ['id' => \$entity->primaryKey()]),",
                "            'delay'    => 5",
                "        ]",
                "    ];",
                "}",
                "",
                "return ['status' => 204, 'message' => 'Updated'];"
            ]);
        }

        //Let's generate some fun!
        $delete = $this->class->method('delete')->setComment([
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
            "if (!\$this->{$plural}->delete(\$entity)) {",
            "    throw new ClientException(ClientException::ERROR);",
            "}",
            "",
            "return ['status' => 204, 'message' => 'Deleted'];"
        ]);
    }
}