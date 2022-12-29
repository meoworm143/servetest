<?php

namespace Modules\PaymentModule\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Config;
use KingFlamez\Rave\Facades\Rave as Flutterwave;
use Modules\UserManagement\Entities\User;

class FlutterwaveController extends Controller
{
    public function __construct()
    {
        //configuration initialization
        $config = business_config('flutterwave', 'payment_config');

        $values = null;
        if (!is_null($config) && $config->mode == 'live') {
            $values = $config->live_values;
        } elseif (!is_null($config) && $config->mode == 'test') {
            $values = $config->test_values;
        }

        if ($values) {
            $config = array(
                'publicKey' => env('FLW_PUBLIC_KEY', $values['public_key']),
                'secretKey' => env('FLW_SECRET_KEY', $values['secret_key']),
                'secretHash' => env('FLW_SECRET_HASH', $values['hash']),
            );
            Config::set('flutterwave', $config);
        }
    }

    public function initialize(Request $request)
    {

        $token = 'access_token=' . $request['user']->id;
        $token .= $request->has('callback') ? '&&callback=' . $request['callback'] : '';
        $token .= '&&zone_id=' . $request['zone_id'] . '&&service_schedule=' . $request['service_schedule'] . '&&service_address_id=' . $request['service_address_id'];

        $token = base64_encode($token);
        $user_data = User::find($request['user']->id);

        //This generates a payment reference
        $reference = Flutterwave::generateReference();

        // Enter the details of the payment
        $data = [
            'payment_options' => 'card,banktransfer',
            'amount' => cart_total($request->user->id),
            'email' => $user_data['email'],
            'tx_ref' => $reference,
            'currency' => currency_code(),
            'redirect_url' => route('flutterwave.callback', ['token'=>$token]),
            'customer' => [
                'email' => $user_data['email'],
                "phone_number" => $user_data['phone'],
                "name" => $user_data['first_name'] . ' ' . $user_data['last_name'],
            ],

            "customizations" => [
                "title" => (business_config('business_name', 'business_information'))->live_values ?? null,
                "description" => '',
            ]
        ];

        $payment = Flutterwave::initializePayment($data);

        if ($payment['status'] !== 'success') {
            if ($request->has('callback')) {
                return redirect($request['callback'].'?payment_status=fail');
            } else {
                return response()->json(response_formatter(DEFAULT_204), 200);
            }
        }

        return redirect($payment['data']['link']);
    }

    public function callback(Request $request)
    {

        $params = explode('&&', base64_decode($request['token']));

        $callback = null;
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

        $transaction_reference = $request['transaction_reference'];
        $status = $request['status'];

        //If payment is successful
        if ($status == 'successful') {
            $tran_id = $transaction_reference;
            $request['payment_method'] = 'flutterwave';
            $request['service_address_id'] = $service_address_id;
            $response = place_booking_request($access_token, $request, $tran_id);

            //If place booking successful
            if ($response['flag'] == 'success') {
                if (isset($callback)) {
                    return redirect($callback.'?payment_status=success');
                } else {
                    return response()->json(response_formatter(DEFAULT_200), 200);
                }

            } else {
                if (isset($callback)) {
                    return redirect($callback.'?payment_status=failed');
                } else {
                    return response()->json(response_formatter(DEFAULT_204), 200);
                }
            }


        } elseif ($status ==  'cancelled') {
            if (isset($callback)) {
                return redirect($callback.'?payment_status=cancelled');
            }
            return response()->json(response_formatter(DEFAULT_204), 200);

        } else{
            return response()->json(response_formatter(DEFAULT_204), 200);
        }

    }
}
