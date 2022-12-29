<?php

namespace Modules\PaymentModule\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Modules\UserManagement\Entities\User;

class SenangPayController extends Controller
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
        $order_amount = cart_total($request['user']->id);

        $customer = User::find($request['user']->id);

        return view('paymentmodule::senang-pay', compact('token', 'order_amount', 'customer'));
    }

    public function return_senang_pay(Request $request)
    {
        if ($request['status_id'] == 1) {
            //success
            if ($request->has('callback')) {
                return redirect($request['callback'].'?payment_status=success');
            } else {
                return response()->json(response_formatter(DEFAULT_200), 200);
            }
        }

        //fail
        return response()->json(response_formatter(DEFAULT_204), 200);
    }
}
