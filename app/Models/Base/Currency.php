<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\Country;
use App\Models\ProductLandedCost;
use App\Models\SalesOrder;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Currency
 * 
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $code
 * @property string $name
 * @property float|null $default_fx_to_gbp
 * 
 * @property Collection|Country[] $countries_currency
 * @property Collection|ProductLandedCost[] $product_landed_costs_currency
 * @property Collection|SalesOrder[] $sales_orders_currency
 * @property Collection|Supplier[] $suppliers_currency
 *
 * @package App\Models\Base
 */
class Currency extends Model
{
	protected $table = 'currencies';

	protected $casts = [
		'default_fx_to_gbp' => 'float'
	];

	public function countries_currency()
	{
		return $this->hasMany(Country::class);
	}

	public function product_landed_costs_currency()
	{
		return $this->hasMany(ProductLandedCost::class);
	}

	public function sales_orders_currency()
	{
		return $this->hasMany(SalesOrder::class);
	}

	public function suppliers_currency()
	{
		return $this->hasMany(Supplier::class);
	}
}
