<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\Currency;
use App\Models\Product;
use App\Models\SalesOrder;
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
 * @property string|null $delivery_address
 * @property string|null $contact_name
 * @property string|null $contact_email
 * @property string|null $contact_phone
 * @property string|null $country
 * @property int|null $currency_id
 * 
 * @property Currency|null $currency
 * @property Collection|Product[] $products_supplier
 * @property Collection|SalesOrder[] $sales_orders_supplier
 *
 * @package App\Models\Base
 */
class Supplier extends Model
{
	protected $table = 'suppliers';

	protected $casts = [
		'currency_id' => 'int'
	];

	public function currency()
	{
		return $this->belongsTo(Currency::class);
	}

	public function products_supplier()
	{
		return $this->hasMany(Product::class);
	}

	public function sales_orders_supplier()
	{
		return $this->hasMany(SalesOrder::class);
	}
}
