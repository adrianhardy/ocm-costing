<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Helpers\SerializeLengthAwarePaginator;
use App\Models\Repositories\UserRepository;
use App\Models\User;
use App\Transformers\UserTransformer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UsersController extends Controller
{
    public function index(Request $request, UserRepository $userRepository)
    {
        $limit = $request->input('all')?PHP_INT_MAX:null;
        $limit = $request->input('per_page', $limit);

        $paginator = $userRepository;
        if ($request->input('role')) {
            $paginator->role([$request->input('role')]);
        }
        $paginator = $paginator->paginate($limit);

        return response()->json(new SerializeLengthAwarePaginator(
            $paginator, new UserTransformer()
        ));
    }

    public function show(User $user)
    {
        return response()->json(fractal($user, new UserTransformer));
    }

    public function update(Request $request, User $user)
    {
        $unique = Rule::unique('users','email')->ignoreModel($user);
        $request->validate([
           'name'     => 'required|max:255',
           'password' => 'sometimes|nullable|confirmed|password|min:8',
           'email'    => ['required', $unique, 'email', 'max:128'],
           'role'     => 'required|in:super,admin,supplier'
        ]);

        $user->fill($request->only([
            'name',
            'email'
        ]));

        if($request->input('password')) {
            $user->password = bcrypt($request->input('password'));
        }
        if ($request->input('role')) {
            $user->syncRoles($request->input('role'));
        }

        $user->syncRoles([$request->input('role')]);
        $user->save();

        return response()->json(
            fractal($user->fresh(), new UserTransformer())
        );
    }

    public function store(Request $request)
    {
        $required = $request->input('role') !== 'supplier'?'required|confirmed|password|min:8':'nullable';
        $request->validate([
            'name'     => 'required|max:255',
            'password' => 'required|confirmed|min:8',
            'email'    => [$required, Rule::unique('users','email'), 'max:128'],
            'role'     => 'required|in:super,admin,supplier'
        ]);

        $user = new User();
        $user->fill($request->only([
            'name',
            'email',
        ]));
        $user->syncRoles($request->input('role'));
        $user->password = bcrypt($request->input('password'));
        $user->save();

        return response(fractal($user->fresh(), new UserTransformer));
    }
}
