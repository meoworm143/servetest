<?php

namespace Modules\ProviderManagement\Http\Controllers\Web\Provider;

use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Modules\ProviderManagement\Entities\Provider;
use Modules\ProviderManagement\Entities\WithdrawRequest;
use Modules\TransactionModule\Entities\Transaction;
use Modules\UserManagement\Entities\User;
use Rap2hpoutre\FastExcel\FastExcel;

class WithdrawController extends Controller
{
    protected User $user;
    protected Provider $provider;
    protected WithdrawRequest $withdraw_request;
    protected Transaction $transaction;

    public function __construct(User $user, Provider $provider, WithdrawRequest $withdraw_request, Transaction $transaction)
    {
        $this->user = $user;
        $this->provider = $provider;
        $this->withdraw_request = $withdraw_request;
        $this->transaction = $transaction;
    }

    /**
     *
     * @param Request $request
     * @return Renderable
     */
    public function index(Request $request)
    {
        Validator::make($request->all(), [
            'search' => 'string',
        ]);

        $search = $request->has('search') ? $request['search'] : '';
        $page_type = 'overview';
        $query_param = ['search' => $search, 'page_type' => $page_type];

        $withdraw_requests = $this->withdraw_request
            ->with(['user.account', 'request_updater.account'])
            ->where('user_id', $request->user()->id)
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                foreach ($keys as $key) {
                    $query->orWhere('amount', 'LIKE', '%' . $key . '%')
                        ->orWhere('request_status', 'LIKE', '%' . $key . '%')
                        ->orWhere('note', 'LIKE', '%' . $key . '%');
                }
            })
            ->latest()
            ->paginate(pagination_limit())->appends($query_param);

        $total_collected_cash = $this->transaction
            ->where('from_user_id', $request->user()->id)
            ->where('trx_type', TRANSACTION_TYPE[1]['key'])
            ->sum('debit');

        return view('providermanagement::provider.account.withdraw', compact('withdraw_requests', 'total_collected_cash', 'search', 'page_type'));
    }

    /**
     * withdraw amount
     * @param Request $request
     * @return RedirectResponse
     */
    public function withdraw(Request $request): RedirectResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'note' => 'max:255'
        ]);

        $provider_user = $this->user->with(['account'])->find($request->user()->id);

        if ($request['amount'] > $provider_user->account->account_receivable) {
            Toastr::error(DEFAULT_400['message']);
            return back();
        }

        withdraw_request_transaction($request->user()->id, $request['amount']);

        $this->withdraw_request->create([
            'user_id' => $request->user()->id,
            'request_updated_by' => $request->user()->id,
            'amount' => $request['amount'],
            'request_status' => 'pending',
            'is_paid' => 0,
            'note' => $request['note']
        ]);

        Toastr::success(DEFAULT_200['message']);
        return back();
    }

    public function download(Request $request)
    {
        $keys = explode(' ', $request['search']);
        $items = $this->withdraw_request
            ->where('user_id', $request->user()->id)
            ->where(function ($query) use ($keys) {
                foreach ($keys as $key) {
                    $query->orWhere('amount', 'LIKE', '%' . $key . '%')
                        ->orWhere('request_status', 'LIKE', '%' . $key . '%')
                        ->orWhere('note', 'LIKE', '%' . $key . '%');
                }
            })->get();
        return (new FastExcel($items))->download(time() . '-file.xlsx');
    }
}
