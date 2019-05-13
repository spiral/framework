<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\App\User;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @entity(repository=UserRepository)
 * @table(indexes={(columns={name},unique=true)})
 */
class User
{
    /** @column(type=primary) */
    public $id;

    /** @column(type=string) */
    protected $name;

    /** @hasMany(target=Role) */
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