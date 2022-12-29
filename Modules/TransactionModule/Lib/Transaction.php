<?php

use Illuminate\Support\Facades\DB;
use Modules\ProviderManagement\Entities\Provider;
use Modules\TransactionModule\Entities\Account;
use Modules\TransactionModule\Entities\Transaction;
use Modules\UserManagement\Entities\User;

if (!function_exists('place_booking_transaction')) {
    function place_booking_transaction($booking)
    {
        if ($booking['payment_method'] != 'cash_after_service') {
            $admin_user_id = User::where('user_type', ADMIN_USER_TYPES[0])->first()->id;
            DB::transaction(function () use ($booking, $admin_user_id) {
                //Admin account update
                $account = Account::where('user_id', $admin_user_id)->first();
                $account->balance_pending += $booking['total_booking_amount'];
                $account->save();

                //Admin transaction
                Transaction::create([
                    'ref_trx_id' => null,
                    'booking_id' => $booking['id'],
                    'trx_type' => 'booking_amount',
                    'debit' => 0,
                    'credit' => $booking['total_booking_amount'],
                    'balance' => $account->balance_pending,
                    'from_user_id' => $booking->customer_id,
                    'to_user_id' => $admin_user_id,
                    'from_user_account' => null,
                    'to_user_account' => ACCOUNT_STATES[0]['value']
                ]);
            });
        }
    }
}

if (!function_exists('digital_payment_booking_transaction')) {
    function digital_payment_booking_transaction($booking)
    {
        $admin_user_id = User::where('user_type', ADMIN_USER_TYPES[0])->first()->id;

        $provider = Provider::find($booking['provider_id']);
//        dd($booking);
        $commission_percentage = $provider->commission_status == 1 ? $provider->commission_percentage : (business_config('default_commission', 'business_information'))->live_values;
        $admin_commission = ($booking['total_booking_amount']*$commission_percentage)/100;
        $booking_amount_without_commission = $booking['total_booking_amount'] - $admin_commission;

        $provider_user_id = get_user_id($booking['provider_id'], PROVIDER_USER_TYPES[0]);

        DB::transaction(function () use ($booking, $admin_user_id, $admin_commission, $booking_amount_without_commission, $provider_user_id) {

            $account = Account::where('user_id', $admin_user_id)->first();
            $account->balance_pending -= $booking['total_booking_amount'];
            $account->save();

            //Admin transaction
            $primary_transaction = Transaction::create([
                'ref_trx_id' => null,
                'booking_id' => $booking['id'],
                'trx_type' => 'booking_amount',
                'debit' => $booking['total_booking_amount'],
                'credit' => 0,
                'balance' => $account->balance_pending,
                'from_user_id' => $admin_user_id,
                'to_user_id' => $provider_user_id,
                'from_user_account' => ACCOUNT_STATES[0]['value'],
                'to_user_account' => null
            ]);

            //Provider transactions
            $account = Account::where('user_id', $provider_user_id)->first();
            $account->account_receivable += $booking_amount_without_commission;
            $account->save();

            Transaction::create([
                'ref_trx_id' => $primary_transaction['id'],
                'booking_id' => $booking['id'],
                'trx_type' => 'received_payment',
                'debit' => 0,
                'credit' => $booking_amount_without_commission,
                'balance' => $account->account_receivable,
                'from_user_id' => $admin_user_id,
                'to_user_id' => $provider_user_id,
                'from_user_account' => null,
                'to_user_account' => ACCOUNT_STATES[3]['value']
            ]);

            //Admin transactions (for commission)
            $account = Account::where('user_id', $admin_user_id)->first();
            $account->received_balance += $admin_commission;
            $account->save();

            Transaction::create([
                'ref_trx_id' => $primary_transaction['id'],
                'booking_id' => $booking['id'],
                'trx_type' => 'received_commission',
                'debit' => 0,
                'credit' => $admin_commission,
                'balance' => $account->received_balance,
                'from_user_id' => $admin_user_id,
                'to_user_id' => $admin_user_id,
                'from_user_account' => ACCOUNT_STATES[1]['value'],
                'to_user_account' => null
            ]);

            //Admin transactions (for commission)
            $account = Account::where('user_id', $admin_user_id)->first();
            $account->account_payable += $booking_amount_without_commission;
            $account->save();

            Transaction::create([
                'ref_trx_id' => $primary_transaction['id'],
                'booking_id' => $booking['id'],
                'trx_type' => 'payable_payment',
                'debit' => 0,
                'credit' => $booking_amount_without_commission,
                'balance' => $account->account_payable,
                'from_user_id' => $admin_user_id,
                'to_user_id' => $admin_user_id,
                'from_user_account' => ACCOUNT_STATES[2]['value'],
                'to_user_account' => null
            ]);
        });
    }
}

if (!function_exists('cash_after_service_booking_transaction')) {
    function cash_after_service_booking_transaction($booking)
    {
        $admin_user_id = User::where('user_type', ADMIN_USER_TYPES[0])->first()->id;

        $provider = Provider::find($booking['provider_id']);
        $commission_percentage = $provider->commission_status == 1 ? $provider->commission_percentage : (business_config('default_commission', 'business_information'))->live_values;
        $admin_commission = ($booking['total_booking_amount']*$commission_percentage)/100;
        $booking_amount_without_commission = $booking['total_booking_amount'] - $admin_commission;

        $provider_user_id = get_user_id($booking['provider_id'], PROVIDER_USER_TYPES[0]);

        DB::transaction(function () use ($booking, $admin_commission, $booking_amount_without_commission, $provider_user_id, $admin_user_id) {

            //Provider transactions
            $account = Account::where('user_id', $provider_user_id)->first();
            $account->received_balance += $booking_amount_without_commission;
            $account->save();

            $primary_transaction = Transaction::create([
                'ref_trx_id' => null,
                'booking_id' => $booking['id'],
                'trx_type' => 'received_payment',
                'debit' => 0,
                'credit' => $booking_amount_without_commission,
                'balance' => $account->received_balance,
                'from_user_id' => $provider_user_id,
                'to_user_id' => $provider_user_id,
                'from_user_account' => null,
                'to_user_account' => ACCOUNT_STATES[1]['value']
            ]);

            //Provider transactions (for commission)
            $account = Account::where('user_id', $provider_user_id)->first();
            $account->account_payable += $admin_commission;
            $account->save();

            Transaction::create([
                'ref_trx_id' => $primary_transaction['id'],
                'booking_id' => $booking['id'],
                'trx_type' => 'payable_commission',
                'debit' => 0,
                'credit' => $admin_commission,
                'balance' => $account->account_payable,
                'from_user_id' => $provider_user_id,
                'to_user_id' => $provider_user_id,
                'from_user_account' => ACCOUNT_STATES[2]['value'],
                'to_user_account' => null
            ]);

            //Provider transactions (for commission)
            $account = Account::where('user_id', $admin_user_id)->first();
            $account->account_receivable += $admin_commission;
            $account->save();

            Transaction::create([
                'ref_trx_id' => $primary_transaction['id'],
                'booking_id' => $booking['id'],
                'trx_type' => 'received_commission',
                'debit' => 0,
                'credit' => $admin_commission,
                'balance' => $account->account_receivable,
                'from_user_id' => $provider_user_id,
                'to_user_id' => $admin_user_id,
                'from_user_account' => ACCOUNT_STATES[3]['value'],
                'to_user_account' => null
            ]);
        });
    }
}

if (!function_exists('collect_cash_transaction')) {
    function collect_cash_transaction($provider_id, $collect_amount) {
        $admin_user_id = User::where('user_type', ADMIN_USER_TYPES[0])->first()->id;
        $provider_user_id = get_user_id($provider_id, PROVIDER_USER_TYPES[0]);

        DB::transaction(function () use ($collect_amount, $admin_user_id, $provider_user_id) {

            $account = Account::where('user_id', $provider_user_id)->first();
            $account->account_payable -= $collect_amount;
            $account->save();

            //Provider transactions
            $primary_transaction = Transaction::create([
                'ref_trx_id' => null,
                'booking_id' => null,
                'trx_type' => 'paid_commission',
                'debit' => $collect_amount,
                'credit' => 0,
                'balance' => $account->account_payable,
                'from_user_id' => $provider_user_id,
                'to_user_id' => $admin_user_id,
                'from_user_account' => ACCOUNT_STATES[2]['value'],
                'to_user_account' => null
            ]);

            //Admin transactions
            $account = Account::where('user_id', $admin_user_id)->first();
            $account->received_balance += $collect_amount;
            $account->save();

            Transaction::create([
                'ref_trx_id' => $primary_transaction['id'],
                'booking_id' => null,
                'trx_type' => 'received_commission',
                'debit' => 0,
                'credit' => $collect_amount,
                'balance' => $account->received_balance,
                'from_user_id' => $provider_user_id,
                'to_user_id' => $admin_user_id,
                'from_user_account' => null,
                'to_user_account' => ACCOUNT_STATES[1]['value']
            ]);
        });
    }
}

if (!function_exists('withdraw_request_transaction')) {
    function withdraw_request_transaction($provider_user_id, $withdrawal_amount) {

        DB::transaction(function () use ($withdrawal_amount, $provider_user_id) {

            //Provider transactions
            $account = Account::where('user_id', $provider_user_id)->first();
            $account->account_receivable -= $withdrawal_amount;
            $account->save();

            $primary_transaction = Transaction::create([
                'ref_trx_id' => null,
                'booking_id' => null,
                'trx_type' => 'withdrawable_payment',
                'debit' => $withdrawal_amount,
                'credit' => 0,
                'balance' => $account->account_receivable,
                'from_user_id' => $provider_user_id,
                'to_user_id' => $provider_user_id,
                'from_user_account' => ACCOUNT_STATES[3]['value'],
                'to_user_account' => null
            ]);

            //Provider transactions
            $account = Account::where('user_id', $provider_user_id)->first();
            $account->balance_pending += $withdrawal_amount;
            $account->save();

            Transaction::create([
                'ref_trx_id' => $primary_transaction['id'],
                'booking_id' => null,
                'trx_type' => 'pending_payment',
                'debit' => 0,
                'credit' => $withdrawal_amount,
                'balance' => $account->balance_pending,
                'from_user_id' => $provider_user_id,
                'to_user_id' => $provider_user_id,
                'from_user_account' => null,
                'to_user_account' => ACCOUNT_STATES[0]['value']
            ]);
        });
    }
}

if (!function_exists('withdraw_request_accept_transaction')) {
    function withdraw_request_accept_transaction($provider_user_id, $withdrawal_amount) {
        $admin_user_id = User::where('user_type', ADMIN_USER_TYPES[0])->first()->id;

        DB::transaction(function () use ($admin_user_id, $withdrawal_amount, $provider_user_id) {

            //Provider transactions
            $account = Account::where('user_id', $provider_user_id)->first();
            $account->balance_pending -= $withdrawal_amount;
            $account->save();

            $primary_transaction = Transaction::create([
                'ref_trx_id' => null,
                'booking_id' => null,
                'trx_type' => null,
                'debit' => $withdrawal_amount,
                'credit' => 0,
                'balance' => $account->balance_pending,
                'from_user_id' => $provider_user_id,
                'to_user_id' => $provider_user_id,
                'from_user_account' => ACCOUNT_STATES[0]['value'],
                'to_user_account' => null
            ]);

            //Provider transactions
            $account = Account::where('user_id', $provider_user_id)->first();
            $account->total_withdrawn += $withdrawal_amount;
            $account->save();

            Transaction::create([
                'ref_trx_id' => $primary_transaction['id'],
                'booking_id' => null,
                'trx_type' => 'received_payment',
                'debit' => 0,
                'credit' => $withdrawal_amount,
                'balance' => $account->total_withdrawn,
                'from_user_id' => $provider_user_id,
                'to_user_id' => $admin_user_id,
                'from_user_account' => ACCOUNT_STATES[4]['value'],
                'to_user_account' => null
            ]);

            //Admin transactions
            $account = Account::where('user_id', $admin_user_id)->first();
            $account->account_payable -= $withdrawal_amount;
            $account->save();

            Transaction::create([
                'ref_trx_id' => $primary_transaction['id'],
                'booking_id' => null,
                'trx_type' => null,
                'debit' => $withdrawal_amount,
                'credit' => 0,
                'balance' => $account->account_payable,
                'from_user_id' => $provider_user_id,
                'to_user_id' => $admin_user_id,
                'from_user_account' => null,
                'to_user_account' => ACCOUNT_STATES[2]['value']
            ]);
        });
    }
}

if (!function_exists('withdraw_request_deny_transaction')) {
    function withdraw_request_deny_transaction($provider_user_id, $withdrawal_amount) {

        DB::transaction(function () use ($withdrawal_amount, $provider_user_id) {

            //Provider transactions
            $account = Account::where('user_id', $provider_user_id)->first();
            $account->account_receivable += $withdrawal_amount;
            $account->save();

            $primary_transaction = Transaction::create([
                'ref_trx_id' => null,
                'booking_id' => null,
                'trx_type' => null,
                'debit' => $withdrawal_amount,
                'credit' => 0,
                'balance' => $account->account_receivable,
                'from_user_id' => $provider_user_id,
                'to_user_id' => $provider_user_id,
                'from_user_account' => ACCOUNT_STATES[3]['value'],
                'to_user_account' => null
            ]);

            //Provider transactions
            $account = Account::where('user_id', $provider_user_id)->first();
            $account->balance_pending -= $withdrawal_amount;
            $account->save();

            Transaction::create([
                'ref_trx_id' => $primary_transaction['id'],
                'booking_id' => null,
                'trx_type' => null,
                'debit' => 0,
                'credit' => $withdrawal_amount,
                'balance' => $account->balance_pending,
                'from_user_id' => $provider_user_id,
                'to_user_id' => $provider_user_id,
                'from_user_account' => null,
                'to_user_account' => ACCOUNT_STATES[0]['value']
            ]);
        });
    }
}
