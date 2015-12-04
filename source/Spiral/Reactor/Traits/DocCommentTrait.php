<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Reactor\Traits;

/**
 * Element can have doc comment.
 */
trait DocCommentTrait
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