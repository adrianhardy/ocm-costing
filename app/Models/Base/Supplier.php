<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Supplier
 * 
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $name
 * 
 * @property Collection|Product[] $products_supplier
 *
 * @package App\Models\Base
 */
class Supplier extends Model
{
	protected $table = 'suppliers';

	public function products_supplier()
	{
		return $this->hasMany(Product::class);
	}
}
