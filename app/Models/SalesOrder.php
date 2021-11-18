<?php

namespace App\Models;

use App\Models\Base\SalesOrder as BaseSalesOrder;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Mpociot\Versionable\VersionableTrait;
use Ramsey\Uuid\Uuid;

/**
 * Class SalesOrder
 * @package App\Models
 * @property SalesOrderLine[] $lines
 * @property-read boolean $is_finalised
 * @property-read boolean $is_sent
 * @property SalesOrderAuthentication[] $authenticated_users
 */
class SalesOrder extends BaseSalesOrder
{
    use SoftDeletes;

	protected $fillable = [
		'sent_at',
        'reference',
		'total',
		'total_gbp',
        'finalised_at',
        'sent_at',
		'fx_rate',
		'supplier_id'
	];

	public function getStageDescriptionAttribute()
    {
        if ($this->finalised_at) {
            return 'Sales Order';
        }
        return 'Quote';
    }

    public function getIsFinalisedAttribute()
    {
        return $this->finalised_at !== null;
    }

	public function getIsSentAttribute()
    {
        return $this->sent_at !== null;
    }

    public function getStatusAttribute()
    {
        switch(true)
        {
            case $this->is_sent:
                return 'Sent';
                break;
            case $this->is_finalised:
                return 'Finalised';
                break;
            default:
                return 'Quote';
                break;
        }
    }

    public function getSalesOrderNumberAttribute()
    {
        return str_pad($this->id, 8, '0', \STR_PAD_LEFT);
    }

	public function lines()
    {
        return $this->sales_order_lines_sales_order();
    }

    public function history()
    {
        return $this->morphMany(
            Version::class,
            'versionable'
        );
    }

    /**
     * @param User $user
     * @param int $hours
     * @param null $password
     * @return SalesOrderAuthentication
     * @throws \Exception
     */
    public function invite(User $user, $hours = 24, $password = null)
    {
        $this->tokens()
            ->where('user_id', $user->id)
            ->delete();

        $token = new SalesOrderAuthentication([
            'token' => Uuid::uuid4(),
            'password' => $password,
            'expires_at' => Carbon::now()->addHours($hours)
        ]);
        $token->sales_order()->associate($this);
        $token->user()->associate($user);
        $this->tokens()->save($token);
        return $token;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tokens()
    {
        return $this->hasMany(
            SalesOrderAuthentication::class
        )->where(function($query) {
            $query
                ->whereNull('expires_at')
                ->orWhere('expires_at', '>', Carbon::now());
        });
    }
}
