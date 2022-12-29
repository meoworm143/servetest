<?php

namespace Modules\BookingModule\Http\Controllers\Web\Admin;

use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\BookingModule\Entities\Booking;
use Modules\BookingModule\Entities\BookingScheduleHistory;
use Illuminate\Http\RedirectResponse;
use Modules\BookingModule\Entities\BookingStatusHistory;
use Modules\CategoryManagement\Entities\Category;
use Modules\ProviderManagement\Entities\Provider;
use Modules\ProviderManagement\Entities\SubscribedService;
use Modules\UserManagement\Entities\Serviceman;
use Modules\ZoneManagement\Entities\Zone;
use Rap2hpoutre\FastExcel\FastExcel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BookingController extends Controller
{

    private Booking $booking;
    private BookingStatusHistory $booking_status_history;
    private BookingScheduleHistory $booking_schedule_history;
    private $subscribed_sub_categories;
    private Category $category;
    private Zone $zone;
    private Serviceman $serviceman;
    private Provider $provider;

    public function __construct(Booking $booking, BookingStatusHistory $booking_status_history, BookingScheduleHistory $booking_schedule_history, SubscribedService $subscribedService, Category $category, Zone $zone, Serviceman $serviceman, Provider $provider)
    {
        $this->booking = $booking;
        $this->booking_status_history = $booking_status_history;
        $this->booking_schedule_history = $booking_schedule_history;
        $this->category = $category;
        $this->zone = $zone;
        $this->serviceman = $serviceman;
        $this->provider = $provider;
        try {
            $this->subscribed_sub_categories = $subscribedService->where(['is_subscribed' => 1])->pluck('sub_category_id')->toArray();
        } catch (\Exception $exception) {
            $this->subscribed_sub_categories = $subscribedService->pluck('sub_category_id')->toArray();
        }
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Renderable
     */
    public function index(Request $request)
    {
        $request->validate([
            'booking_status' => 'in:' . implode(',', array_column(BOOKING_STATUSES, 'key')) . ',all',
        ]);
        $request['booking_status'] = $request['booking_status'] ?? 'pending';

        $query_param = [];

        if ($request->has('zone_ids')) {
            $zone_ids = $request['zone_ids'];
            $query_param['zone_ids'] = $zone_ids;
        }

        if ($request->has('category_ids')) {
            $category_ids = $request['category_ids'];
            $query_param['category_ids'] = $category_ids;
        }

        if ($request->has('sub_category_ids')) {
            $sub_category_ids = $request['sub_category_ids'];
            $query_param['sub_category_ids'] = $sub_category_ids;
        }

        if ($request->has('start_date')) {
            $start_date = $request['start_date'];
            $query_param['start_date'] = $start_date;
        } else {
            $query_param['start_date'] = null;
        }

        if ($request->has('end_date')) {
            $end_date = $request['end_date'];
            $query_param['end_date'] = $end_date;
        } else {
            $query_param['end_date'] = null;
        }

        if ($request->has('search')) {
            $search = $request['search'];
            $query_param['search'] = $search;
        }

        if ($request->has('booking_status')) {
            $booking_status = $request['booking_status'];
            $query_param['booking_status'] = $booking_status;
        } else {
            $query_param['booking_status'] = 'pending';
        }

        $bookings = $this->booking->with(['customer'])
            ->when($request->has('search'), function ($query) use ($request) {
                $query->where(function ($query) use ($request) {
                    $keys = explode(' ', $request['search']);
                    foreach ($keys as $key) {
                        $query->orWhere('readable_id', 'LIKE', '%' . $key . '%');
                    }
                });
            })
            ->when($booking_status != 'all', function ($query) use ($booking_status) {
                $query->ofBookingStatus($booking_status);
            })
            ->when($request->has('zone_ids'), function ($query) use ($request) {
                $query->whereIn('zone_id', $request['zone_ids']);
            })->when($query_param['start_date'] != null && $query_param['end_date'] != null, function ($query) use ($request) {
                if ($request['start_date'] == $request['end_date']) {
                    $query->whereDate('created_at', $request['start_date']);
                } else {
                    $query->whereBetween('created_at', [$request['start_date'], $request['end_date']]);
                }
            })->when($request->has('sub_category_ids'), function ($query) use ($request) {
                $query->whereIn('sub_category_id', $request['sub_category_ids']);
            })->when($request->has('category_ids'), function ($query) use ($request) {
                $query->whereIn('category_id', $request['category_ids']);
            })
            ->latest()->paginate(pagination_limit())->appends($query_param);

        //for filter
        $zones = $this->zone->select('id', 'name')->get();
        $categories = $this->category->select('id', 'parent_id', 'name')->where('position', 1)->get();
        $sub_categories = $this->category->select('id', 'parent_id', 'name')->where('position', 2)->get();

        return view('bookingmodule::admin.booking.list', compact('bookings', 'zones', 'categories', 'sub_categories', 'query_param'));
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Renderable
     */
    public function check_booking()
    {
        $this->booking->where('is_checked', 0)->update(['is_checked' => 1]); //update the unseen bookings
    }

    /**
     * Display a listing of the resource.
     * @param $id
     * @param Request $request
     * @return Renderable
     */
    public function details($id, Request $request)
    {
        Validator::make($request->all(), [
            'web_page' => 'required|in:details,status',
        ]);
        $web_page = $request->has('web_page') ? $request['web_page'] : 'business_setup';

        if ($request->web_page == 'details') {

            $booking = $this->booking->with(['detail.service', 'detail.variation', 'customer', 'provider', 'service_address', 'serviceman', 'service_address', 'status_histories.user'])->find($id);

            $servicemen = $this->serviceman->with(['user'])
                ->where('provider_id', $booking->provider_id)
                ->whereHas('user', function ($query) {
                    $query->ofStatus(1);
                })
                ->latest()
                ->get();

            return view('bookingmodule::admin.booking.details', compact('booking', 'servicemen', 'web_page'));

        } elseif ($request->web_page == 'status') {
            $booking = $this->booking->with(['detail.service', 'customer', 'provider', 'service_address', 'serviceman.user', 'service_address', 'status_histories.user'])->find($id);
            return view('bookingmodule::admin.booking.status', compact('booking', 'web_page'));
        }
    }

    /**
     * Display a listing of the resource.
     * @param $booking_id
     * @param Request $request
     * @return JsonResponse
     */
    public function status_update($booking_id, Request $request): JsonResponse
    {
        Validator::make($request->all(), [
            'booking_status' => 'required|in:' . implode(',', array_column(BOOKING_STATUSES, 'key')),
        ]);

        $booking = $this->booking->where('id', $booking_id)->first();

        if (isset($booking)) {
            $booking->booking_status = $request['booking_status'];

            $booking_status_history = $this->booking_status_history;
            $booking_status_history->booking_id = $booking_id;
            $booking_status_history->changed_by = $request->user()->id;
            $booking_status_history->booking_status = $request['booking_status'];

            if ($booking->isDirty('booking_status')) {
                DB::transaction(function () use ($booking_status_history, $booking) {
                    $booking->save();
                    $booking_status_history->save();
                });

                return response()->json(DEFAULT_STATUS_UPDATE_200, 200);
            }
            return response()->json(NO_CHANGES_FOUND, 200);
        }
        return response()->json(DEFAULT_204, 200);
    }

    /**
     * Display a listing of the resource.
     * @param $booking_id
     * @param Request $request
     * @return JsonResponse
     */
    public function payment_update($booking_id, Request $request): JsonResponse
    {
        Validator::make($request->all(), [
            'payment_status' => 'required|in:paid,unpaid',
        ]);

        $booking = $this->booking->where('id', $booking_id)->first();

        if (isset($booking)) {
            $booking->is_paid = $request->payment_status == 'paid' ? 1 : 0;

            if ($booking->isDirty('is_paid')) {
                $booking->save();
                return response()->json(DEFAULT_STATUS_UPDATE_200, 200);
            }
            return response()->json(NO_CHANGES_FOUND, 200);
        }
        return response()->json(DEFAULT_204, 200);
    }

    /**
     * Display a listing of the resource.
     * @param $booking_id
     * @param Request $request
     * @return JsonResponse
     */
    public function schedule_upadte($booking_id, Request $request): JsonResponse
    {
        Validator::make($request->all(), [
            'service_schedule' => 'required',
        ]);

        $booking = $this->booking->where('id', $booking_id)->first();

        if (isset($booking)) {
            $booking->service_schedule = Carbon::parse($request->service_schedule)->toDateTimeString();

            //history
            $booking_schedule_history = $this->booking_schedule_history;
            $booking_schedule_history->booking_id = $booking_id;
            $booking_schedule_history->changed_by = $request->user()->id;
            $booking_schedule_history->schedule = $request['service_schedule'];

            if ($booking->isDirty('service_schedule')) {
                $booking->save();
                $booking_schedule_history->save();
                return response()->json(DEFAULT_STATUS_UPDATE_200, 200);
            }
            return response()->json(NO_CHANGES_FOUND, 200);
        }
        return response()->json(DEFAULT_204, 200);
    }

    /**
     * Display a listing of the resource.
     * @param $booking_id
     * @param Request $request
     * @return JsonResponse
     */
    public function serviceman_update($booking_id, Request $request): JsonResponse
    {
        Validator::make($request->all(), [
            'serviceman_id' => 'required|uuid',
        ]);

        $booking = $this->booking->where('id', $booking_id)->first();

        if (isset($booking)) {
            $booking->serviceman_id = $request->serviceman_id;

            if ($booking->isDirty('serviceman_id')) {
                $booking->save();
                return response()->json(DEFAULT_STATUS_UPDATE_200, 200);
            }
            return response()->json(NO_CHANGES_FOUND, 200);
        }
        return response()->json(DEFAULT_204, 200);
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return string|StreamedResponse
     */
    public function download(Request $request): string|StreamedResponse
    {
        $request->validate([
            'booking_status' => 'in:' . implode(',', array_column(BOOKING_STATUSES, 'key')) . ',all',
        ]);
        $request['booking_status'] = $request['booking_status'] ?? 'pending';

        $query_param = [];

        if ($request->has('zone_ids')) {
            $zone_ids = $request['zone_ids'];
            $query_param['zone_ids'] = $zone_ids;
        }

        if ($request->has('category_ids')) {
            $category_ids = $request['category_ids'];
            $query_param['category_ids'] = $category_ids;
        }

        if ($request->has('sub_category_ids')) {
            $sub_category_ids = $request['sub_category_ids'];
            $query_param['sub_category_ids'] = $sub_category_ids;
        }

        if ($request->has('start_date')) {
            $start_date = $request['start_date'];
            $query_param['start_date'] = $start_date;
        } else {
            $query_param['start_date'] = null;
        }

        if ($request->has('end_date')) {
            $end_date = $request['end_date'];
            $query_param['end_date'] = $end_date;
        } else {
            $query_param['end_date'] = null;
        }

        if ($request->has('search')) {
            $search = $request['search'];
            $query_param['search'] = $search;
        }

        if ($request->has('booking_status')) {
            $booking_status = $request['booking_status'];
            $query_param['booking_status'] = $booking_status;
        } else {
            $query_param['booking_status'] = 'pending';
        }

        $items = $this->booking->with(['customer'])
            ->when($request->has('search'), function ($query) use ($request) {
                $query->where(function ($query) use ($request) {
                    $keys = explode(' ', $request['search']);
                    foreach ($keys as $key) {
                        $query->orWhere('readable_id', 'LIKE', '%' . $key . '%');
                    }
                });
            })
            ->when($booking_status != 'all', function ($query) use ($booking_status) {
                $query->ofBookingStatus($booking_status);
            })
            ->when($request->has('zone_ids'), function ($query) use ($request) {
                $query->whereIn('zone_id', $request['zone_ids']);
            })->when($query_param['start_date'] != null && $query_param['end_date'] != null, function ($query) use ($request) {
                $query->whereBetween('created_at', [$request['start_date'], $request['end_date']]);
            })->when($request->has('sub_category_ids'), function ($query) use ($request) {
                $query->whereIn('sub_category_id', $request['sub_category_ids']);
            })->when($request->has('category_ids'), function ($query) use ($request) {
                $query->whereIn('category_id', $request['category_ids']);
            })
            ->latest()->get();


        return (new FastExcel($items))->download(time() . '-file.xlsx');
    }


    /**
     * Display a listing of the resource.
     * @param $id
     * @param Request $request
     * @return Renderable
     */
    public function invoice($id, Request $request): Renderable
    {
        $booking = $this->booking->with(['detail.service', 'customer', 'provider', 'service_address', 'serviceman', 'service_address', 'status_histories.user'])->find($id);
        return view('bookingmodule::admin.booking.invoice', compact('booking'));
    }
}
