<?php

namespace Modules\ServicemanModule\Http\Controllers\Web\Provider;

use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\UserManagement\Entities\Serviceman;
use Modules\UserManagement\Entities\User;
use Rap2hpoutre\FastExcel\FastExcel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ServicemanController extends Controller
{
    private User $employee;
    private User $serviceman_user;
    private Serviceman $serviceman;

    public function __construct(Serviceman $serviceman, User $serviceman_user, User $employee)
    {
        $this->serviceman = $serviceman;
        $this->employee = $employee;
        $this->serviceman_user = $serviceman_user;
    }


    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Renderable
     */
    public function index(Request $request): Renderable
    {
        $request->validate([
            'status' => 'in:active,inactive,all',
        ]);

        $search = $request->has('search') ? $request['search'] : '';
        $status = $request->has('status') ? $request['status'] : 'all';
        $query_param = ['status' => $status, 'search' => $search];

        $servicemen = $this->serviceman_user->with(['serviceman'])
            ->when($request->has('search'), function ($query) use ($request) {
                $query->where(function ($query) use ($request) {
                    $keys = explode(' ', $request['search']);
                    foreach ($keys as $key) {
                        $query->orWhere('first_name', 'LIKE', '%' . $key . '%')
                            ->orWhere('last_name', 'LIKE', '%' . $key . '%')
                            ->orWhere('email', 'LIKE', '%' . $key . '%')
                            ->orWhere('phone', 'LIKE', '%' . $key . '%')
                            ->orWhere('identification_number', 'LIKE', '%' . $key . '%');
                    }
                });
            })
            ->when($request['status'] != 'all', function ($query) use ($request) {
                $query->where('is_active', ($request['status'] == 'active') ? 1 : 0);
            })
            ->whereHas('serviceman', function ($query) use ($request) {
                $query->where('provider_id', $request->user()->provider->id);
            })
            ->where(['user_type' => 'provider-serviceman'])
            ->latest()
            ->paginate(pagination_limit())->appends($query_param);

        return view('servicemanmodule::Provider.Serviceman.list', compact('servicemen', 'search', 'status'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function create(Request $request): Renderable
    {
        return view('servicemanmodule::Provider.Serviceman.create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'phone' => 'required|unique:users,phone',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'confirm_password' => 'required|same:password',
            'profile_image' => 'required|image|mimes:jpeg,jpg,png,gif|max:10000',
            'identity_type' => 'required|in:passport,driving_license,company_id,nid,trade_license',
            'identity_number' => 'required',
            'identity_image' => 'required|array',
            'identity_image.*' => 'image|mimes:jpeg,jpg,png,gif|max:10000',
        ]);

        if (!$request->has('identity_image') || count($request->identity_image) < 1) {
            Toastr::error(translate('Identification_image_is_required'));
            return back();
        }

        $identity_images = [];
        foreach ($request->identity_image as $image) {
            $identity_images[] = file_uploader('serviceman/identity/', 'png', $image);
        }


        DB::transaction(function () use ($request, $identity_images) {
            $employee = $this->employee;
            $employee->first_name = $request->first_name;
            $employee->last_name = $request->last_name;
            $employee->email = $request->email;
            $employee->phone = $request->phone;
            $employee->profile_image = file_uploader('serviceman/profile/', 'png', $request->file('profile_image'));
            $employee->identification_number = $request->identity_number;
            $employee->identification_type = $request->identity_type;
            $employee->identification_image = $identity_images;
            $employee->password = bcrypt($request->password);
            $employee->user_type = 'provider-serviceman';
            $employee->is_active = 1;
            $employee->save();

            $serviceman = $this->serviceman;
            $serviceman->provider_id = $request->user()->provider->id;
            $serviceman->user_id = $employee->id;
            $serviceman->save();
        });

        Toastr::success(SERVICE_STORE_200['message']);
        return back();
    }

    /**
     * Show the specified resource.
     * @param string $id
     * @return Application|Factory|View
     */
    public function show(string $id): View|Factory|Application
    {
        $serviceman = $this->serviceman::with(['user'])->find($id);
        return view('servicemanmodule::Provider.Serviceman.edit', compact('serviceman'));
    }

    /**
     * Show the specified resource.
     * @param string $id
     * @return Application|Factory|View
     */
    public function edit(string $id): Application|Factory|View
    {
        $serviceman = $this->serviceman::with(['user'])->find($id);
        return view('servicemanmodule::Provider.Serviceman.edit', compact('serviceman'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param string $id
     * @return RedirectResponse
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $employee = $this->employee::whereHas('serviceman', function ($query) use ($id) {
            $query->where(['id' => $id]);
        })->first();

        if (!isset($employee)) {
            Toastr::error(translate('you_can _not_change_this_user_info'));
            return back();
        }

        $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'phone' => 'required|unique:users,phone,' . $employee->id,
            'email' => 'required|email|unique:users,email,' . $employee->id,
            'password' => '',
            'confirm_password' => !is_null($request->password) ? 'required|min:8|same:password' : '',
            'profile_image' => 'image|mimes:jpeg,jpg,png,gif|max:10000',
            'identity_type' => 'in:passport,driving_license,company_id,nid,trade_license',
            'identity_number' => 'required',
            'identity_image' => 'array',
            'identity_image.*' => 'image|mimes:jpeg,jpg,png,gif|max:10000',
        ]);

        //$identity_images = (array)$employee->identification_image;
        $identity_images = [];
        if ($request->has('identity_image')) {
            foreach ($request['identity_image'] as $image) {
                $identity_images[] = file_uploader('serviceman/identity/', 'png', $image);
            }
        }

        DB::transaction(function () use ($request, $identity_images, $employee) {
            $employee->first_name = $request->first_name;
            $employee->last_name = $request->last_name;
            $employee->email = $request->email;
            $employee->phone = $request->phone;
            if ($request->has('profile_image')) {
                $employee->profile_image = file_uploader('serviceman/profile/', 'png', $request->file('profile_image'));
            }
            $employee->identification_number = $request->identity_number;
            $employee->identification_type = $request->identity_type;
            $employee->identification_image = $identity_images;
            if (!is_null($request->password)) {
                $employee->password = bcrypt($request->password);
            }
            $employee->user_type = 'provider-serviceman';
            $employee->save();
        });

        Toastr::success(DEFAULT_UPDATE_200['message']);
        return back();
    }

    /**
     * Remove the specified resource from storage.
     * @param Request $request
     * @param $id
     * @return RedirectResponse
     */
    public function destroy(Request $request, $id): RedirectResponse
    {
        $serviceman = $this->serviceman->find($id);
        $serviceman->delete();

        Toastr::success(DEFAULT_DELETE_200['message']);
        return back();
    }

    /**
     * * Bulk status update
     * @param Request $request
     * @return JsonResponse
     */


    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function status_update(Request $request, $id): JsonResponse
    {
        $serviceman = $this->employee->where('id', $id)->first();
        $this->employee->where('id', $id)->update(['is_active' => !$serviceman->is_active]);

        return response()->json(DEFAULT_STATUS_UPDATE_200, 200);
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return string|StreamedResponse
     */
    public function download(Request $request): string|StreamedResponse
    {
        $request->validate([
            'status' => 'in:active,inactive,all',
        ]);

        $items = $this->serviceman_user->with(['serviceman'])
            ->when($request->has('search'), function ($query) use ($request) {
                $query->where(function ($query) use ($request) {
                    $keys = explode(' ', $request['search']);
                    foreach ($keys as $key) {
                        $query->orWhere('first_name', 'LIKE', '%' . $key . '%')
                            ->orWhere('last_name', 'LIKE', '%' . $key . '%')
                            ->orWhere('email', 'LIKE', '%' . $key . '%')
                            ->orWhere('phone', 'LIKE', '%' . $key . '%')
                            ->orWhere('identification_number', 'LIKE', '%' . $key . '%');
                    }
                });
            })
            ->when($request['status'] != 'all', function ($query) use ($request) {
                $query->where('is_active', ($request['status'] == 'active') ? 1 : 0);
            })
            ->whereHas('serviceman', function ($query) use ($request) {
                $query->where('provider_id', $request->user()->provider->id);
            })
            ->where(['user_type' => 'provider-serviceman'])
            ->latest()
            ->get();

        return (new FastExcel($items))->download(time() . '-file.xlsx');
    }
}
