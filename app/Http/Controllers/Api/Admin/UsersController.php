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
        $paginator = $userRepository->paginate();
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
        $request->validate([
           'name'     => 'required|max:255',
           'password' => 'sometimes|nullable|confirmed|password|min:8',
           'email'    => ['required', Rule::unique('users','email')->ignore($request->input(['id'])), 'max:128'],
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

        $user->save();

        return response()->json(
            fractal($user->fresh(), new UserTransformer())
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|max:255',
            'password' => 'required|confirmed|min:8',
            'email'    => ['required', Rule::unique('users','email'), 'max:128'],
        ]);

        $user = new User();
        $user->fill($request->only([
            'name',
            'email',
            ]));

        $user->password = bcrypt($request->input('password'));
        $user->save();

        return response(fractal($user->fresh(), new UserTransformer));
    }
}
