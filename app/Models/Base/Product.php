<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\QuoteLine;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Product
 * 
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $item_number
 * @property string $article_number
 * @property string $name
 * @property int $supplier_id
 * 
 * @property Supplier $supplier
 * @property Collection|QuoteLine[] $quote_lines_product
 *
 * @package App\Models\Base
 */
class Product extends Model
{
	protected $table = 'products';

	protected $casts = [
		'supplier_id' => 'int'
	];

	public function supplier()
	{
		return $this->belongsTo(Supplier::class);
	}

	public function quote_lines_product()
	{
		return $this->hasMany(QuoteLine::class);
	}
}
