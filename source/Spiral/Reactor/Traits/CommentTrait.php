<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Reactor\Traits;

use Spiral\Reactor\DocComment;

/**
 * Element can have doc comment.
 */
trait CommentTrait
{
    /**
     * @var DocComment
     */
    private $docComment = null;

    /**
     * Get associated file comment.
     *
     * @return DocComment
     */
    public function comment()
    {
        return $this->docComment;
    }
}