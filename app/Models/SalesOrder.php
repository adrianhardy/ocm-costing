<?php

namespace App\Models;

use App\Models\Base\SalesOrder as BaseSalesOrder;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Mpociot\Versionable\VersionableTrait;
use Ramsey\Uuid\Uuid;
use Spatie\ModelStatus\HasStatuses;

/**
 * Class SalesOrder
 * @package App\Models
 * @property SalesOrderLine[] $lines
 * @property Consignment[] $consignments
 * @property-read boolean $is_finalised
 * @property-read boolean $is_sent
 * @property SalesOrderAuthentication[] $authenticated_users
 * @property $sales_order_fx_rate
 * @property-read $status
 * @proeprty-read $status_description
 * @property Invoice[] $invoices
 * @property-read $oa_eta
 * @property-read Boolean $has_unmatched_products
 */
class SalesOrder extends BaseSalesOrder
{
    use SoftDeletes;
    use HasStatuses;

    const STATUS_DRAFT = 'draft';
    const STATUS_QUOTE_REQUEST = 'quote_request';
    const STATUS_QUOTE = 'quote';
    const STATUS_SALES_ORDER = 'sales_order';
    const STATUS_CONFIRMED_SALES_ORDER = 'confirmed_sales_order';

    const STATUSES = [
        self::STATUS_DRAFT => 'Draft',
        self::STATUS_QUOTE_REQUEST => 'Quote Request',
        self::STATUS_QUOTE => 'Quote',
        self::STATUS_SALES_ORDER => 'Sales Order',
        self::STATUS_CONFIRMED_SALES_ORDER => 'Confirmed Sales Order',
    ];

    const OA_STATUS_CLOSED = 'closed';
    const OA_STATUS_ACTIVE = 'active';
    const OA_STATUS_AWAITING_DELIVERY = 'awaiting_delivery';
    const OA_STATUS_PARTIALLY_RECEIVED = 'partially_received';
    const OA_STATUS_FULLY_RECEIVED = 'fully_received';
    const OA_STATUS_UNMATCHED_PRODUCTS = 'unmatched_products';

    const OA_STATUSES = [
        self::STATUS_DRAFT => 'Draft',
        self::OA_STATUS_ACTIVE => 'Active',
        self::OA_STATUS_UNMATCHED_PRODUCTS => 'Draft',
        self::OA_STATUS_AWAITING_DELIVERY => 'Awaiting Delivery',
        self::OA_STATUS_PARTIALLY_RECEIVED => 'Active',
        self::OA_STATUS_FULLY_RECEIVED => 'Fully Received',
        self::STATUS_CONFIRMED_SALES_ORDER => 'Active',
        self::OA_STATUS_CLOSED => 'Closed',
    ];

	protected $fillable = [
		'sent_at',
        'reference',
		'total',
		'total_gbp',
        'finalised_at',
        'sent_at',
		'fx_rate',
		'supplier_id',
        'sales_order_notes',
        'shipping_cost_per_cubic_foot',
        'supplier_reference',
        'supplier_finalised_at',
        'sales_order_fx_rate',
	];

	public function setSalesOrderFxRateAttribute($value)
    {
        $this->attributes['fx_rate'] = round(1/$value, 4);
    }

	public function getSalesOrderFxRateAttribute()
    {
        return round(1 / $this->fx_rate,2);
    }

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

    public function getIsSupplierFinalisedAttribute()
    {
        return $this->supplier_finalised_at !== null;
    }


    public function getIsSentAttribute()
    {
        return $this->sent_at !== null;
    }

    /**
     * @return string
     */
    public function getStatusAttribute()
    {
        $status = $this->latestStatus();
        return $status?$status->name:null;

    }

    public function getHasUnmatchedProductsAttribute(){
        return $this->lines()->whereNull('product_id')->exists();
    }


    /**
     * @return string
     */
    public function getStatusDescriptionAttribute()
    {

        $description = 'Unknown';

        if ($this->is_order_acknowledgement) {
            if (array_key_exists($this->status, self::OA_STATUSES)) {
                $description = self::OA_STATUSES[$this->status];
            }
        } else {
            if (array_key_exists($this->status, self::STATUSES)) {
                $description = self::STATUSES[$this->status];
            }
        }

        switch(true) {
            case $this->is_sent:
                $description .= ' : Sent';
                break;
            case $this->is_finalised:
                $description .= ' : Finalised';
                break;
        }

        return $description;
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

    public function getConsignmentsAttribute()
    {
        return Consignment::query()
            ->whereHas('sales_order_lines', function($query) {
                $query->where('sales_order_id', $this->id);
            })
            ->get();
    }

    public function invoices(){
        return $this->invoices_sales_order;
    }

    public function getOaEtaAttribute(){
        if(count($this->lines()->whereNotNull('oa_eta')->get()) >= 1) {
            return SalesOrderLine::query()
                ->where('sales_order_id', $this->id)
                ->whereNotNull('oa_eta')
                ->orderBy('oa_eta')
                ->first()->oa_eta;
        }
        return null;
    }


}
