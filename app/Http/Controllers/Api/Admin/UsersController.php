<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Helpers\SerializeLengthAwarePaginator;
use App\Models\Repositories\UserRepository;
use App\Models\Supplier;
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
        $this->validate($request, [
           'name'           => 'required|max:255',
           'password'       => 'sometimes|nullable|confirmed|min:8',
           'email'          => ['required', $unique, 'email', 'max:128'],
           'role'           => 'required',
           'supplier.id'    => 'nullable|required_if:role,supplier|exists:suppliers,id'
        ], [], [
            'supplier.id' => 'Supplier'
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

        if ($request->input('role') == 'supplier') {
            $user->password = User::USER_LOCKED;
            $user->supplier()->associate(
                Supplier::query()->findOrFail($request->input('supplier.id'))
            );
            $user->factories()->sync($request->input('factories.*.id'));
        } else {
            $user->supplier()->dissociate();
            $user->factories()->detach();
        }

        $user->save();

        return response()->json(
            fractal($user->fresh(), new UserTransformer())
        );
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $required = $request->input('role') !== 'supplier'?'required|confirmed|min:8':'nullable';
        $this->validate($request, [
            'name'          => 'required|max:255',
            'password'      => $required,
            'supplier.id'   => $request->input('role') === 'supplier'?'required|exists:suppliers,id':'nullable',
            'email'         => ['required', Rule::unique('users','email'), 'max:128'],
            'role'          => 'required'
        ]);

        $user = new User();
        $user->fill($request->only([
            'name',
            'email'
        ]));
        $user->password = bcrypt($request->input('password'));
        $user->save();
        if ($request->input('role') == 'supplier') {
            $user->password = User::USER_LOCKED;
            $user->supplier()->associate(
                Supplier::query()->findOrFail($request->input('supplier.id'))
            );
            $user->factories()->sync($request->input('factories.*.id'));
        }

        $user->syncRoles($request->input('role'));
        $user->save();

        return response(fractal($user->fresh(), new UserTransformer));
    }

    public function destroy(User $user)
    {
        $user->password = User::USER_LOCKED;
        $user->save();

        return response()->json(fractal($user, new UserTransformer));
    }
}
