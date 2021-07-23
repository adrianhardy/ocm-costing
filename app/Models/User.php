<?php

namespace App\Models;

use App\Models\Base\User as BaseUser;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\User as Authenticatable;
use LasseRafn\Initials\Initials;
use Spatie\Permission\Traits\HasPermissions;
use Spatie\Permission\Traits\HasRoles;

/**
 * Class User
 * @package App\Models
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string $api_token
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 */
class User extends Authenticatable
{
    use HasRoles;

    const USER_LOCKED = 'LOCKED';

	protected $hidden = [
		'password',
		'api_token',
		'remember_token'
	];

	protected $dates = [
        'last_seen_at',
        'updated_at',
        'created_at'
    ];

	protected $fillable = [
		'name',
		'email',
		'email_verified_at',
		'password',
		'api_token',
		'onesignal_player_id',
		'remember_token',
        'last_seen_at',
        'onesignal_user_id',
        'onesignal_token_id'
    ];


    public function routeNotificationForOneSignal()
    {
        return $this->onesignal_user_id;
    }

    /**
     * @return string
     */
	public function getInitialsAttribute()
    {
        return (new Initials())->name($this->name)->getUrlfriendlyInitials();
    }

    public function getIsLockedAttribute()
    {
        return $this->password == self::USER_LOCKED;
    }

    public function lock()
    {
        $this->password = self::USER_LOCKED;
        $this->save();
    }


}
