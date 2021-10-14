<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\Product;
use App\Models\SalesOrderLine;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class ProductPricing
 * 
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property int $product_id
 * @property float $est_landed_cost_per_unit_gbp
 * @property float|null $marketplace_commission_pc
 * @property float|null $unit_shipping_gbp
 * @property float|null $unit_price_gbp
 * @property float|null $gross_unit_cost_gbp
 * @property Carbon|null $approved_at
 * @property int|null $approver_id
 * @property Carbon|null $superseded_at
 * @property int|null $sales_order_line_id
 * @property float|null $uk_vat
 * @property float|null $fr_vat
 * @property float|null $de_vat
 * @property float|null $it_vat
 * @property float|null $nl_vat
 * @property float|null $es_vat
 * @property float|null $unit_fr_price
 * @property float|null $unit_de_price
 * @property float|null $unit_it_price
 * @property float|null $unit_nl_price
 * @property float|null $unit_es_price
 * @property float|null $unit_fr_shipping
 * @property float|null $unit_de_shipping
 * @property float|null $unit_it_shipping
 * @property float|null $unit_nl_shipping
 * @property float|null $unit_es_shipping
 * @property float|null $unit_usd_shipping
 * @property float|null $unit_cad_shipping
 * @property float|null $unit_mxn_shipping
 * @property float|null $unit_usd_price
 * @property float|null $unit_cad_price
 * @property float|null $unit_mxn_price
 * @property float|null $eur_fx_rate
 * @property float|null $usd_fx_rate
 * @property float|null $cad_fx_rate
 * @property float|null $mxn_fx_rate
 * 
 * @property User|null $approver
 * @property Product $product
 * @property SalesOrderLine|null $sales_order_line
 *
 * @package App\Models\Base
 */
class ProductPricing extends Model
{
	use SoftDeletes;
	protected $table = 'product_pricing';

	protected $casts = [
		'product_id' => 'int',
		'est_landed_cost_per_unit_gbp' => 'float',
		'marketplace_commission_pc' => 'float',
		'unit_shipping_gbp' => 'float',
		'unit_price_gbp' => 'float',
		'gross_unit_cost_gbp' => 'float',
		'approver_id' => 'int',
		'sales_order_line_id' => 'int',
		'uk_vat' => 'float',
		'fr_vat' => 'float',
		'de_vat' => 'float',
		'it_vat' => 'float',
		'nl_vat' => 'float',
		'es_vat' => 'float',
		'unit_fr_price' => 'float',
		'unit_de_price' => 'float',
		'unit_it_price' => 'float',
		'unit_nl_price' => 'float',
		'unit_es_price' => 'float',
		'unit_fr_shipping' => 'float',
		'unit_de_shipping' => 'float',
		'unit_it_shipping' => 'float',
		'unit_nl_shipping' => 'float',
		'unit_es_shipping' => 'float',
		'unit_usd_shipping' => 'float',
		'unit_cad_shipping' => 'float',
		'unit_mxn_shipping' => 'float',
		'unit_usd_price' => 'float',
		'unit_cad_price' => 'float',
		'unit_mxn_price' => 'float',
		'eur_fx_rate' => 'float',
		'usd_fx_rate' => 'float',
		'cad_fx_rate' => 'float',
		'mxn_fx_rate' => 'float'
	];

	protected $dates = [
		'approved_at',
		'superseded_at'
	];

	public function approver()
	{
		return $this->belongsTo(User::class, 'approver_id');
	}

	public function product()
	{
		return $this->belongsTo(Product::class);
	}

	public function sales_order_line()
	{
		return $this->belongsTo(SalesOrderLine::class);
	}
}
