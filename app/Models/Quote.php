<?php

namespace App\Models;

use App\Models\Base\Quote as BaseQuote;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class Quote
 *
 * @property Collection|QuoteLine[] $lines
 * @package App\Models
 */
class Quote extends BaseQuote
{
	protected $fillable = [
		'reference'
	];

	public function lines()
    {
        return $this->hasMany(QuoteLine::class, 'quote_id', 'id');
    }
}
