<?php

namespace Modules\PaymentModule\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Modules\UserManagement\Entities\User;
use Razorpay\Api\Api;

class RazorPayController extends Controller
{
    public function __construct()
    {
        $config = business_config('razor_pay', 'payment_config');
        if (!is_null($config) && $config->mode == 'live') {
            $razor = $config->live_values;
        } elseif (!is_null($config) && $config->mode == 'test') {
            $razor = $config->test_values;
        }

        if ($razor) {
            $config = array(
                'api_key' => $razor['api_key'],
                'api_secret' => $razor['api_secret']
            );
            Config::set('razor_config', $config);
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

        return view('paymentmodule::razor-pay', compact('token', 'order_amount', 'customer'));
    }

    public function payment(Request $request)
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

        $tran_id = Str::random(6) . '-' . rand(1, 1000);;
        $request['payment_method'] = 'razor_pay';
        $request['service_address_id'] = $service_address_id;
        $response = place_booking_request($access_token, $request, $tran_id);

        if($response['flag'] == 'failed') {
            if ($callback) {
                return redirect($callback.'?payment_status=failed');
            } else {
                return response()->json(response_formatter(DEFAULT_204), 200);
            }
        }

        $input = $request->all();
        $api = new Api(config('razor_config.api_key'), config('razor_config.api_secret'));
        $payment = $api->payment->fetch($input['razorpay_payment_id']);

        if(count($input)  && !empty($input['razorpay_payment_id'])) {
            try {
                $response = $api->payment->fetch($input['razorpay_payment_id'])->capture(array('amount'=>$payment['amount']));

            } catch (\Exception $e) {
                //error
            }
        }

        if ($callback) {
            return redirect($callback.'?payment_status=success');
        } else {
            return response()->json(response_formatter(DEFAULT_200), 200);
        }
    }
}
