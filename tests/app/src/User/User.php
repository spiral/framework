<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\App\User;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\HasMany;
use Cycle\Annotated\Annotation\Table;
use Cycle\Annotated\Annotation\Table\Index;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @Entity(repository="UserRepository")
 * @Table(indexes={@Index(columns={"name"},unique=true)})
 */
class User
{
    /** @Column(type="primary") */
    public $id;

    /** @Column(type="string") */
    protected $name;

    /** @HasMany(target="Role") */
    public $roles;

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->roles = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}