<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Comment
 * 
 * @property int $id
 * @property string $commentable_type
 * @property int $commentable_id
 * @property string $comment
 * @property bool $is_approved
 * @property int|null $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models\Base
 */
class Comment extends Model
{
	protected $table = 'comments';

	protected $casts = [
		'commentable_id' => 'int',
		'is_approved' => 'bool',
		'user_id' => 'int'
	];
}
