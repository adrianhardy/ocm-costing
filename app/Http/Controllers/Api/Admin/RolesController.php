<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Transformers\PermissionTransformer;
use App\Transformers\RoleTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesController extends Controller
{
    public function index(Request $request){
        $roles = Role::query()
            ->orderBy('is_protected', 'desc')
            ->orderBy('name');

        $roles = $roles->get();

        // helps with dropdown, permissions are not necessary there
        if($request->input('select')) {
            return response()->json([
                'roles' => fractal($roles, new RoleTransformer)
            ]);
        }

        $permissions = Permission::all();

        return response()->json([
            'roles' => fractal($roles, new RoleTransformer())->parseIncludes('permissions'),
            'permissions' => fractal($permissions, new PermissionTransformer())
        ]);
    }

    public function updateAll(Request $request){
        $roles = json_decode(json_encode($request->input()));

        foreach ($roles as $role) {
            /** @var Role $dataRole */
            $dataRole = Role::findByName($role->name, 'web');
            $permissions = [];
            foreach ($role->permissions as $permission) {
                if($permission->has_permission) {
                    $permissions[] = $permission->name;
                }
            }
            $dataRole->syncPermissions($permissions);
            $dataRole->save();
        }

        return response()->json($roles);
    }

    public function store(Request $request)
    {
//        $this->authorize('create', Role::class);

        $this->validate($request, [
            'name' => 'required',
        ]);

        $name = Str::slug($request->input('name'), '_');

        $role = new Role(['name' => $name, 'guard_name' => 'web']);
        $role->save();

        return response()->json(fractal($role, new RoleTransformer()));
    }

    public function destroy(Request $request, Role $role)
    {
//        $this->authorize('delete', $role);
        if($role->protected) {
            throw new \Exception("Cannot delete a protected role");
        }

        $role->delete();
        return response()->json(fractal($role, new RoleTransformer()));
    }
}
