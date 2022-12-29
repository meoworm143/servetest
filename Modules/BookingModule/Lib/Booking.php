<?php

use Illuminate\Support\Facades\DB;
use Modules\BookingModule\Entities\Booking;
use Modules\BookingModule\Entities\BookingDetail;
use Modules\BookingModule\Entities\BookingScheduleHistory;
use Modules\BookingModule\Entities\BookingStatusHistory;
use Modules\BookingModule\Events\BookingRequested;
use Modules\CartModule\Entities\Cart;
use Modules\ServiceManagement\Entities\Service;

if (!function_exists('place_booking_request')) {
    function place_booking_request($user_id, $request, $transaction_id)
    {
        $cart_data = Cart::where(['customer_id' => $user_id])->get();

        if ($cart_data->count() == 0) {
            return [
                'flag' => 'failed',
                'message' => 'no data found'
            ];
        }

        $booking_ids = [];
        foreach ($cart_data->pluck('sub_category_id')->unique() as $sub_category) {

            $booking = new Booking();

            DB::transaction(function () use ($sub_category, $booking, $transaction_id, $request, $cart_data, $user_id) {
                $cart_data = $cart_data->where('sub_category_id', $sub_category);

                $booking->customer_id = $user_id;
                $booking->category_id = $cart_data->first()->category_id;
                $booking->sub_category_id = $sub_category;
                $booking->zone_id = config('zone_id') == 'no_id_added' ? $request['zone_id'] : config('zone_id');
                $booking->booking_status = 'pending';
                $booking->is_paid = ($request->has('payment_method') && $request['payment_method'] == 'cash_after_service') ? 0 : 1;
                $booking->payment_method = $request['payment_method'];
                $booking->transaction_id = ($request->has('payment_method') && $request['payment_method'] == 'cash_after_service') ? 'cash-payment' : $transaction_id;
                $booking->total_booking_amount = $cart_data->sum('total_cost');
                $booking->total_tax_amount = $cart_data->sum('tax_amount');
                $booking->total_discount_amount = $cart_data->sum('discount_amount');
                $booking->total_campaign_discount_amount = $cart_data->sum('campaign_discount');
                $booking->total_coupon_discount_amount = $cart_data->sum('coupon_discount');
                $booking->service_schedule = $request->service_schedule ?? now()->addHours(5);
                $booking->service_address_id = $request->service_address_id ?? '';
                $booking->save();

                foreach ($cart_data->all() as $datum) {
                    $detail = new BookingDetail();
                    $detail->booking_id = $booking->id;
                    $detail->service_id = $datum['service_id'];
                    $detail->service_name = Service::find($datum['service_id'])->name ?? 'service-not-found';
                    $detail->variant_key = $datum['variant_key'];
                    $detail->quantity = $datum['quantity'];
                    $detail->service_cost = $datum['service_cost'];
                    $detail->discount_amount = $datum['discount_amount'];
                    $detail->campaign_discount_amount = $datum['campaign_discount'];
                    $detail->overall_coupon_discount_amount = $datum['coupon_discount'];
                    $detail->tax_amount = $datum['tax_amount'];
                    $detail->total_cost = $datum['total_cost'];
                    $detail->save();
                }

                $schedule = new BookingScheduleHistory();
                $schedule->booking_id = $booking->id;
                $schedule->changed_by = $user_id;
                $schedule->schedule = $request->service_schedule ?? now()->addHours(5);
                $schedule->save();

                $status_history = new BookingStatusHistory();
                $status_history->changed_by = $booking->id;
                $status_history->booking_id = $user_id;
                $status_history->booking_status = 'pending';
                $status_history->save();
            });
            $booking_ids[] = $booking->id;
        }

        cart_clean($user_id);
        event(new BookingRequested($booking));

        return [
            'flag' => 'success',
            'booking_id' => $booking_ids
        ];
    }
}

