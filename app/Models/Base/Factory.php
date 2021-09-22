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
 * Class Factory
 * 
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $name
 * @property string|null $delivery_address
 * @property string|null $contact_name
 * @property string|null $contact_email
 * @property string|null $contact_phone
 * @property string|null $country
 * 
 * @property Collection|Product[] $products_factory
 *
 * @package App\Models\Base
 */
class Factory extends Model
{
	protected $table = 'factories';

	public function products_factory()
	{
		return $this->hasMany(Product::class);
	}
}
