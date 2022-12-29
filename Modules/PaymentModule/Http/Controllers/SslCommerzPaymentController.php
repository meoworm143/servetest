<?php

namespace Modules\PaymentModule\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;

class SslCommerzPaymentController extends Controller
{
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

        $config = business_config('sslcommerz', 'payment_config');

        if (!is_null($config) && $config->mode == 'live') {
            $values = $config->live_values;
        } elseif (!is_null($config) && $config->mode == 'test') {
            $values = $config->test_values;
        }

        $cart_total = cart_total($request['user']->id);

        $post_data = array();
        $post_data['store_id'] = $values['store_id'];
        $post_data['store_passwd'] = $values['store_password'];
        $post_data['total_amount'] = round($cart_total, 2);
        $post_data['currency'] = currency_code();
        $post_data['tran_id'] = uniqid();

        $encoded_user_id = 'access_token=' . base64_encode($request['user']->id);
        $callback = $request->has('callback') ? '&&callback=' . $request['callback'] : '';
        $addition = '&&zone_id=' . $request['zone_id'] . '&&service_schedule=' . $request['service_schedule'] . '&&service_address_id=' . $request['service_address_id'];

        $post_data['success_url'] = url('/') . '/payment/sslcommerz/success?' . $encoded_user_id . $callback . $addition;
        $post_data['fail_url'] = url('/') . '/payment/sslcommerz/failed?' . $encoded_user_id . $callback;
        $post_data['cancel_url'] = url('/') . '/payment/sslcommerz/canceled?' . $encoded_user_id . $callback;

        # CUSTOMER INFORMATION
        $post_data['cus_name'] = $request['user']->first_name . ' ' . $request['user']->last_name;
        $post_data['cus_email'] = $request['user']->email;
        $post_data['cus_add1'] = 'N/A';
        $post_data['cus_add2'] = "";
        $post_data['cus_city'] = "";
        $post_data['cus_state'] = "";
        $post_data['cus_postcode'] = "";
        $post_data['cus_country'] = "";
        $post_data['cus_phone'] = $request['user']->phone??'0000000000';
        $post_data['cus_fax'] = "";

        # SHIPMENT INFORMATION
        $post_data['ship_name'] = "N/A";
        $post_data['ship_add1'] = "N/A";
        $post_data['ship_add2'] = "N/A";
        $post_data['ship_city'] = "N/A";
        $post_data['ship_state'] = "N/A";
        $post_data['ship_postcode'] = "N/A";
        $post_data['ship_phone'] = "";
        $post_data['ship_country'] = "N/A";

        $post_data['shipping_method'] = "NO";
        $post_data['product_name'] = "N/A";
        $post_data['product_category'] = "N/A";
        $post_data['product_profile'] = "service";

        # OPTIONAL PARAMETERS
        $post_data['value_a'] = "ref001";
        $post_data['value_b'] = "ref002";
        $post_data['value_c'] = "ref003";
        $post_data['value_d'] = "ref004";

        # REQUEST SEND TO SSLCOMMERZ
        if ($config->mode == 'live') {
            $direct_api_url = "https://securepay.sslcommerz.com/gwprocess/v4/api.php";
            $host = false;
        } else {
            $direct_api_url = "https://sandbox.sslcommerz.com/gwprocess/v4/api.php";
            $host = true;
        }

        $handle = curl_init();
        curl_setopt($handle, CURLOPT_URL, $direct_api_url);
        curl_setopt($handle, CURLOPT_TIMEOUT, 30);
        curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($handle, CURLOPT_POST, 1);
        curl_setopt($handle, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, $host); # KEEP IT FALSE IF YOU RUN FROM LOCAL PC

        $content = curl_exec($handle);

        $code = curl_getinfo($handle, CURLINFO_HTTP_CODE);

        if ($code == 200 && !(curl_errno($handle))) {
            curl_close($handle);
            $sslcommerzResponse = $content;
        } else {
            curl_close($handle);
            return back();
        }

        $sslcz = json_decode($sslcommerzResponse, true);
        if (isset($sslcz['GatewayPageURL']) && $sslcz['GatewayPageURL'] != "") {
            echo "<meta http-equiv='refresh' content='0;url=" . $sslcz['GatewayPageURL'] . "'>";
            exit;
        } else {
            return response()->json(response_formatter(DEFAULT_204), 200);
        }
    }

    public function success(Request $request): \Illuminate\Http\JsonResponse|\Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse|\Illuminate\Contracts\Foundation\Application
    {
        $tran_id = $request->input('tran_id');
        $request['payment_method'] = 'ssl_commerz';
        $response = place_booking_request($request->user->id, $request, $tran_id);

        if ($response['flag'] == 'success') {
            if ($request->has('callback')) {
                return redirect($request['callback'].'?payment_status=success');
            } else {
                return response()->json(response_formatter(DEFAULT_200), 200);
            }
        }

        return response()->json(response_formatter(DEFAULT_204), 200);
    }

    public function failed(Request $request): \Illuminate\Http\JsonResponse|\Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse|\Illuminate\Contracts\Foundation\Application
    {
        if ($request->has('callback')) {
            return redirect($request['callback'].'?payment_status=failed');
        }

        return response()->json(response_formatter(DEFAULT_204), 200);
    }

    public function canceled(Request $request): \Illuminate\Http\JsonResponse|\Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse|\Illuminate\Contracts\Foundation\Application
    {
        if ($request->has('callback')) {
            return redirect($request['callback'].'?payment_status=canceled');
        }

        return response()->json(response_formatter(DEFAULT_204), 200);
    }
}
