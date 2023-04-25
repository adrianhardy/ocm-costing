<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleTransformer extends TransformerAbstract
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
        'permissions'
    ];


    public function includePermissions(Role $role){
        $permissions = Permission::all();

        $rolePermissions = $role->getAllPermissions();

        $json = [];

        foreach($permissions as $permission) {
            $hasPermission = false;
            foreach($rolePermissions as $rolePermission) {
                if($permission->id === $rolePermission->id) {
                    $hasPermission = true;
                }
            }

            $json[] = [
                'id' => $permission->id,
                'name' => $permission->name,
                'has_permission' => $hasPermission
            ];
        }

        return $this->primitive($json);
    }




    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Role $role)
    {
        return $role->only([
            'id', 'name', 'is_protected'
        ]);
    }
}
