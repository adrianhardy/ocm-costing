<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\Country;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class DailyPricing
 * 
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property Carbon $submitted_at
 * @property int|null $submitter_id
 * @property int $product_id
 * @property int $country_id
 * @property float|null $suggested_price
 * @property float|null $fba_fee
 * @property Carbon|null $approved_at
 * @property int|null $approver_id
 * @property bool $is_viable
 * @property float|null $margin
 * @property float|null $profit
 * 
 * @property User|null $approver
 * @property Country $country
 * @property Product $product
 * @property User|null $submitter
 *
 * @package App\Models\Base
 */
class DailyPricing extends Model
{
	use SoftDeletes;
	protected $table = 'daily_pricing';

	protected $casts = [
		'submitter_id' => 'int',
		'product_id' => 'int',
		'country_id' => 'int',
		'suggested_price' => 'float',
		'fba_fee' => 'float',
		'approver_id' => 'int',
		'is_viable' => 'bool',
		'margin' => 'float',
		'profit' => 'float'
	];

	protected $dates = [
		'submitted_at',
		'approved_at'
	];

	public function approver()
	{
		return $this->belongsTo(User::class, 'approver_id');
	}

	public function country()
	{
		return $this->belongsTo(Country::class);
	}

	public function product()
	{
		return $this->belongsTo(Product::class);
	}

	public function submitter()
	{
		return $this->belongsTo(User::class, 'submitter_id');
	}
}
