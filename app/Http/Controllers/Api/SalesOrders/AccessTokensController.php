<?php

namespace App\Http\Controllers\Api\SalesOrders;

use App\Http\Controllers\Controller;
use App\Mail\SupplierSalesOrderInvitation;
use App\Models\SalesOrder;
use App\Models\SalesOrderAuthentication;
use App\Models\User;
use App\Transformers\SalesOrderAuthenticationTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class AccessTokensController extends Controller
{
    public function index(Request $request, SalesOrder $salesOrder)
    {
        // $this->authorize('view', $salesOrder);
        return response()->json([
            'items' => fractal($salesOrder->tokens, new SalesOrderAuthenticationTransformer())
        ]);
    }

    /**
     * @param Request $request
     * @param SalesOrder $salesOrder
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(Request $request, SalesOrder $salesOrder)
    {
        $this->validate($request, [
            'user.id' => 'required|exists:users,id'
        ]);
        // $this->authorize('view', $salesOrder);

        /** @var User $user */
        $user = User::query()->findOrFail($request->input('user.id'));
        DB::beginTransaction();
        $token = $salesOrder->invite($user);
        Mail::send((new SupplierSalesOrderInvitation($token))
            ->to($user->email, $user->name)
        );
        DB::commit();
        return response()->json([
            'items' => fractal(
                $salesOrder->tokens,
                new SalesOrderAuthenticationTransformer()
            )
        ]);
    }

    /**
     * @param SalesOrder $salesOrder
     * @param SalesOrderAuthentication $token
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function destroy(SalesOrder $salesOrder, SalesOrderAuthentication $token)
    {
        $token->delete();
        return response()->json([
            'items' => fractal(
                $salesOrder->tokens,
                new SalesOrderAuthenticationTransformer()
            )
        ]);
    }
}
