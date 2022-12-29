<?php

namespace Modules\PromotionManagement\Http\Controllers\Api\V1\Customer;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Modules\CartModule\Entities\Cart;
use Modules\PromotionManagement\Entities\Coupon;
use Modules\PromotionManagement\Entities\Discount;
use Modules\PromotionManagement\Entities\DiscountType;
use Modules\ServiceManagement\Entities\Service;

class CouponController extends Controller
{
    protected $discount, $coupon, $discountType, $cart, $service;

    public function __construct(Coupon $coupon, Discount $discount, DiscountType $discountType, Cart $cart, Service $service)
    {
        $this->discount = $discount;
        $this->discountQuery = $discount->ofPromotionTypes('coupon');
        $this->coupon = $coupon;
        $this->discountType = $discountType;
        $this->cart = $cart;
        $this->service = $service;
    }


    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required|numeric|min:1|max:200',
            'offset' => 'required|numeric|min:1|max:100000'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $coupons = $this->coupon->with(['discount'])->ofStatus(1)
            ->paginate($request['limit'], ['*'], 'offset', $request['offset'])->withPath('');

        return response()->json(response_formatter(DEFAULT_200, $coupons), 200);
    }

    /**
     * Show the form for creating a new resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function apply_coupon(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'coupon_code' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $cart_items = $this->cart->where(['customer_id' => $request->user()->id])->get();
        $type_wise_id = [];
        foreach ($cart_items as $item) {
            $type_wise_id[] = $item['service_id'];
            $type_wise_id[] = $item['category_id'];
        }

        $coupon = $this->coupon->where(['coupon_code' => $request['coupon_code']])
            ->whereHas('discount', function ($query) {
                $query->where(['promotion_type' => 'coupon'])
                    ->whereDate('start_date', '<=', now())
                    ->whereDate('end_date', '>=', now())
                    ->where('is_active', 1);
            })->whereHas('discount.discount_types', function ($query) {
                $query->where(['discount_type' => 'zone', 'type_wise_id' => config('zone_id')]);
            })->with('discount.discount_types', function ($query) use ($type_wise_id) {
                $query->whereIn('type_wise_id', array_unique($type_wise_id));
            })->latest()->first();

        $discounted_ids = [];
        if (isset($coupon) && isset($coupon->discount) && $coupon->discount->discount_types->count() > 0) {
            $discounted_ids = $coupon->discount->discount_types->pluck('type_wise_id')->toArray();
        }

        $applied = 0;
        if (isset($coupon)) {
            foreach ($cart_items as $item) {
                if (in_array($item->service_id, $discounted_ids) || in_array($item->category_id, $discounted_ids)) {
                    $cart_item = $this->cart->where('id', $item['id'])->first();
                    $service = $this->service->find($cart_item['service_id']);
                    $coupon_discount_amount = booking_discount_calculator($coupon->discount, $cart_item->service_cost * $cart_item['quantity']);

                    $basic_discount = $cart_item->discount_amount;
                    $campaign_discount = $cart_item->campaign_discount;
                    $subtotal = round($cart_item->service_cost * $cart_item['quantity'], 2);
                    $tax = round((($cart_item->service_cost / 100) * $service['tax']) * $cart_item['quantity'], 2);

                    $cart_item->coupon_discount = $coupon_discount_amount;
                    $cart_item->coupon_code = $coupon->coupon_code;

                    $cart_item->total_cost = round($subtotal - $basic_discount - $campaign_discount - $coupon_discount_amount + $tax, 2);
                    $cart_item->save();
                    $applied = 1;
                }
            }
            if ($applied) {
                return response()->json(response_formatter(DEFAULT_200), 200);
            }
            return response()->json(response_formatter(COUPON_NOT_VALID_FOR_CART), 200);
        }

        return response()->json(response_formatter(DEFAULT_404), 200);
    }

}
