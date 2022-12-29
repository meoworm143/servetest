<?php

namespace Modules\PaymentModule\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Stripe\Stripe;

class StripePaymentController extends Controller
{
    public function index(Request $request): \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory|\Illuminate\Http\JsonResponse|\Illuminate\Contracts\Foundation\Application
    {
        $validator = Validator::make($request->all(), [
            'zone_id' => 'required|uuid',
            'service_schedule' => 'required|date',
            'service_address_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $token = 'access_token=' . $request['user']->id;
        $token .= $request->has('callback') ? '&&callback=' . $request['callback'] : '';
        $token .= '&&zone_id=' . $request['zone_id'] . '&&service_schedule=' . $request['service_schedule'] . '&&service_address_id=' . $request['service_address_id'];

        $token = base64_encode($token);

        return view('paymentmodule::stripe',compact('token'));
    }


    public function payment_process_3d(Request $request): \Illuminate\Http\JsonResponse
    {
        $params = explode('&&', base64_decode($request['token']));

        foreach ($params as $param) {
            $data = explode('=', $param);
            if ($data[0] == 'access_token') {
                $access_token = $data[1];
            } elseif ($data[0] == 'callback') {
                $callback = $data[1];
            } elseif ($data[0] == 'zone_id') {
                $zone_id = $data[1];
            } elseif ($data[0] == 'service_schedule') {
                $service_schedule = $data[1];
            } elseif ($data[0] == 'service_address_id') {
                $service_address_id = $data[1];
            }
        }

        $booking_amount = cart_total($access_token);
        $config = business_config('stripe', 'payment_config');
        Stripe::setApiKey($config->live_values['api_key']);
        header('Content-Type: application/json');
        $currency_code = currency_code();

        $business_name = business_config('business_name', 'business_information');
        $business_logo = business_config('business_logo', 'business_information');

        $query_parameter = 'access_token=' . $access_token;
        $query_parameter .= isset($callback) ? '&&callback=' . $callback : '';
        $query_parameter .= '&&zone_id=' . $zone_id . '&&service_schedule=' . $service_schedule . '&&service_address_id=' . $service_address_id;

        $checkout_session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => $currency_code ?? 'usd',
                    'unit_amount' => round($booking_amount, 2) * 100,
                    'product_data' => [
                        'name' => $business_name->live_values,
                        'images' => [asset('storage/app/public/business') . '/' . $business_logo->live_values],
                    ],
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' =>  url('/') . '/payment/stripe/success?' . $query_parameter,
            'cancel_url' => url()->previous(),
        ]);
        return response()->json(['id' => $checkout_session->id]);
    }

    public function success(Request $request)
    {
        $tran_id = Str::random(6) . '-' . rand(1, 1000);;
        $request['payment_method'] = 'stripe';
        $response = place_booking_request($request['access_token'], $request, $tran_id);

        if ($response['flag'] == 'success') {
            if ($request->has('callback')) {
                return redirect($request['callback'].'?payment_status=success');
            } else {
                return response()->json(response_formatter(DEFAULT_200), 200);
            }
        }
        return response()->json(response_formatter(DEFAULT_204), 200);
    }
}
