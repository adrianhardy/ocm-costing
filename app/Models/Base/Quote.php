<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\QuoteLine;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Quote
 * 
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $reference
 * 
 * @property Collection|QuoteLine[] $quote_lines_quote
 *
 * @package App\Models\Base
 */
class Quote extends Model
{
	protected $table = 'quotes';

	public function quote_lines_quote()
	{
		return $this->hasMany(QuoteLine::class);
	}
}
