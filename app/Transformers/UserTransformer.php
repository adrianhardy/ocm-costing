<?php

namespace App\Transformers;

use App\Models\User;
use League\Fractal\TransformerAbstract;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserTransformer extends TransformerAbstract
{
    /**
     * List of resources to automatically include
     *
     * @var array
     */
    protected $defaultIncludes = [
        //
    ];

    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected $availableIncludes = [
        'pivot', 'roles', 'permissions', 'api_token'
    ];

    /**
     * @param User $user
     * @return array
     */
    public function transform(User $user)
    {
        $json = $user->only([
            'id',
            'name',
            'email',
            'created_at',
            'last_seen_at',
            'is_locked'
        ]);
        $json['me'] = \Auth::id() == $user->id;
        $pivot = $user->getAttribute('pivot');
        if ($pivot) {
            $json["pivot"] = $pivot;
        }
        return $json;
    }

    public function includeApiToken(User $user)
    {
        return $this->primitive($user->api_token);
    }

    public function includeRoles(User $user)
    {
        $roles = Role::all();
        $roles = $roles->mapWithKeys(function($role) use ($user) {
            return [$role->name => $user->hasRole($role)];
        });
        return $this->primitive($roles);
    }

    public function includePermissions(User $user)
    {
        $all = Permission::all();
        $matrix = $all->mapWithKeys(function(Permission $permission) use ($user) {
            return [$permission->name => $user->can($permission->name)];
        });
            return $this->primitive($matrix);
    }

}
