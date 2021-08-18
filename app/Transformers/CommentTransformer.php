<?php

namespace App\Transformers;

use BeyondCode\Comments\Comment;
use League\Fractal\TransformerAbstract;

class CommentTransformer extends TransformerAbstract
{
    /**
     * List of resources to automatically include
     *
     * @var array
     */
    protected $defaultIncludes = [
        //
    ];
    
    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected $availableIncludes = [
        'commentator'
    ];


    /**
     * @param Comment $comment
     * @return \League\Fractal\Resource\Item|\League\Fractal\Resource\NullResource
     */
    public function includeCommentator(Comment $comment)
    {
        if (!$comment->commentator) {
            return $this->null();
        }
        return $this->item($comment->commentator, new UserTransformer());
    }

    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Comment $comment)
    {
        return $comment->only([
            'id',
            'created_at',
            'updated_at',
            'comment'
        ]);
    }
}
