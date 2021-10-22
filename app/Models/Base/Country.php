<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\Currency;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Country
 * 
 * @property int $id
 * @property string $name
 * @property string $iso_2_code
 * @property float $import_duty
 * @property float $export_duty
 * @property float $vat_rate
 * @property int|null $currency_id
 * @property float|null $gbp_fx_rate
 * 
 * @property Currency|null $currency
 *
 * @package App\Models\Base
 */
class Country extends Model
{
	protected $table = 'countries';
	public $timestamps = false;

	protected $casts = [
		'import_duty' => 'float',
		'export_duty' => 'float',
		'vat_rate' => 'float',
		'currency_id' => 'int',
		'gbp_fx_rate' => 'float'
	];

	public function currency()
	{
		return $this->belongsTo(Currency::class);
	}
}
