<?php

namespace Modules\TransactionModule\Http\Controllers\Web\Admin;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Modules\ProviderManagement\Entities\WithdrawRequest;
use Modules\TransactionModule\Entities\Account;
use Modules\TransactionModule\Entities\Transaction;
use Modules\UserManagement\Entities\User;

class WithdrawnController extends Controller
{
    protected User $user;
    protected Account $account;
    protected WithdrawRequest $withdraw_request;
    protected Transaction $transaction;

    public function __construct(User $user, Account $account, WithdrawRequest $withdraw_request, Transaction $transaction)
    {
        $this->user = $user;
        $this->account = $account;
        $this->withdraw_request = $withdraw_request;
        $this->transaction = $transaction;
    }


    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        Validator::make($request->all(), [
            'status' => 'required|in:pending,approved,denied,all',
        ]);

        $search = $request['search']??"";
        $status = $request['status']??'all';
        $query_param = ['search' => $request['search'], 'status' => $status];

        $withdraw_requests = $this->withdraw_request->with(['user.provider.bank_detail', 'request_updater'])
            ->when($request->has('status') && $request['status'] != 'all', function ($query) use ($request) {
                return $query->where('request_status', $request->status);
            })
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                return $query->where(function ($query) use ($keys) {
                    foreach ($keys as $key) {
                        $query->orWhere('amount', 'LIKE', '%' . $key . '%')
                            ->orWhere('note', 'LIKE', '%' . $key . '%')
                            ->orWhere('request_status', 'LIKE', '%' . $key . '%');
                    }
                });
            })
            ->latest()
            ->paginate(pagination_limit())->appends($query_param);

        return View('transactionmodule::admin.withdraw.list', compact('withdraw_requests', 'status', 'search'));
    }


    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function update_status(Request $request, string $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:approved,denied',
            'note' => 'max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(DEFAULT_400['message'], 200);
        }


        $withdraw_request = $this->withdraw_request::find($id);
        if (isset($withdraw_request) && $withdraw_request['request_status'] != 'pending') {
            return response()->json(DEFAULT_400['message'], 200);
        }

        if ($request['status'] == 'approved') {
            withdraw_request_accept_transaction($withdraw_request['request_updated_by'], $withdraw_request['amount']);

            $withdraw_request->request_status = 'approved';
            $withdraw_request->request_updated_by = $request->user()->id;
            $withdraw_request->admin_note = $request->note;
            $withdraw_request->is_paid = 1;
            $withdraw_request->save();

        } else {
            withdraw_request_deny_transaction($withdraw_request['request_updated_by'], $withdraw_request['amount']);

            $withdraw_request->request_status = 'denied';
            $withdraw_request->request_updated_by = $request->user()->id;
            $withdraw_request->admin_note = $request->note;
            $withdraw_request->is_paid = 0;
            $withdraw_request->save();

        }

        return response()->json(DEFAULT_UPDATE_200['message'], 200);
    }
}
