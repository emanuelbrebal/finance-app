<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StoreAccountRequest;
use App\Http\Requests\V1\UpdateAccountRequest;
use App\Http\Resources\V1\AccountResource;
use App\Models\Account;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Response;

class AccountController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $accounts = auth()->user()->accounts()
            ->active()
            ->get();

        return AccountResource::collection($accounts);
    }

    public function store(StoreAccountRequest $request)
    {
        $account = auth()->user()->accounts()->create(
            $request->validated()
        );

        return AccountResource::make($account)->response()->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Account $account)
    {
        $this->authorize('view', $account);

        return AccountResource::make($account);
    }

    public function update(UpdateAccountRequest $request, Account $account)
    {
        $this->authorize('update', $account);

        $account->update($request->validated());

        return AccountResource::make($account);
    }

    public function destroy(Account $account)
    {
        $this->authorize('delete', $account);

        // Soft archive
        $account->update(['archived_at' => now()]);

        return response()->noContent();
    }
}
