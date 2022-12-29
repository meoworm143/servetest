<?php

namespace Modules\PaymentModule\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Modules\UserManagement\Entities\User;
use Unicodeveloper\Paystack\Facades\Paystack;

class PaystackController extends Controller
{
    public function __construct()
    {
        //configuration initialization
        $config = business_config('paystack', 'payment_config');

        if (!is_null($config) && $config->mode == 'live') {
            $values = $config->live_values;
        } elseif (!is_null($config) && $config->mode == 'test') {
            $values = $config->test_values;
        }

        if ($values) {
            $config = array(
                'publicKey' => env('PAYSTACK_PUBLIC_KEY', $values['public_key']),
                'secretKey' => env('PAYSTACK_SECRET_KEY', $values['secret_key']),
                'paymentUrl' => env('PAYSTACK_PAYMENT_URL', $values['callback_url']),
                'merchantEmail' => env('MERCHANT_EMAIL', $values['merchant_email']),
            );
            Config::set('paystack', $config);
        }
    }


    public function index(Request $request)
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
        $order_amount = cart_total($request['user']->id);

        $customer = User::find($request['user']->id);

        return view('paymentmodule::paystack', compact('token', 'order_amount', 'customer'));
    }

    public function redirectToGateway(Request $request)
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

        \session()->put('callback', $callback);
        \session()->put('access_token', $access_token);

        try {
            return Paystack::getAuthorizationUrl()->redirectNow();

        } catch (\Exception $e) {
            //If payment fail
            if (isset($callback)) {
                return redirect($callback.'?payment_status=failed');
            } else {
                return response()->json(response_formatter(DEFAULT_204), 200);
            }
        }
    }

    public function handleGatewayCallback(Request $request)
    {
        $callback = session('callback');
        $access_token = session('access_token');

        $paymentDetails = Paystack::getPaymentData();

        //token string generate
        $transaction_reference = $paymentDetails['data']['reference'];

        if ($paymentDetails['status'] == true) {
            //If payment success
            $tran_id = $transaction_reference;
            $request['payment_method'] = 'paystack';
            $response = place_booking_request($access_token, $request, $tran_id);

            //If booking place is successful
            if ($response['flag'] == 'success') {
                if (isset($callback)) {
                    return redirect($callback.'?payment_status=success');
                } else {
                    return response()->json(response_formatter(DEFAULT_200), 200);
                }
            }

        } else {
            //If payment fail
            if (isset($callback)) {
                return redirect($callback.'?payment_status=failed');
            } else {
                return response()->json(response_formatter(DEFAULT_204), 200);
            }
        }
    }

    public static function generate_transaction_Referance()
    {

        return Paystack::genTranxRef();
    }
}
