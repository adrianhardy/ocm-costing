<?php

namespace App\Models;

use App\Models\Base\Comment as BaseComment;

class Comment extends BaseComment
{
	protected $fillable = [
		'commentable_type',
		'commentable_id',
		'comment',
		'is_approved',
		'user_id'
	];
}
