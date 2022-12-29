<?php

namespace Modules\BusinessSettingsModule\Http\Controllers\Web\Admin;

use App\Traits\ActivationClass;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Modules\BusinessSettingsModule\Entities\BusinessSettings;

class BusinessInformationController extends Controller
{
    use ActivationClass;

    private BusinessSettings $business_setting;

    public function __construct(BusinessSettings $business_setting)
    {
        $this->business_setting = $business_setting;

        if (request()->isMethod('get')) {
            $response = $this->actch();
            $data = json_decode($response->getContent(), true);
            if (!$data['active']) {
                return Redirect::away(base64_decode('aHR0cHM6Ly82YW10ZWNoLmNvbS9zb2Z0d2FyZS1hY3RpdmF0aW9u'))->send();
            }
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function business_information_get(Request $request): Factory|View|Application
    {
        $web_page = $request->has('web_page') ? $request['web_page'] : 'business_setup';
        if ($web_page == 'business_setup') {
            $data_values = $this->business_setting->where('settings_type', 'business_information')->get();
        } elseif ($web_page == 'service_setup') {
            $data_values = $this->business_setting->where('settings_type', 'service_setup')->get();
        }

        return view('businesssettingsmodule::admin.business', compact('data_values', 'web_page'));
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function business_information_set(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'business_name' => 'required',
            'business_phone' => 'required',
            'business_email' => 'required',
            'business_address' => 'required',
            'country_code' => 'required',
            'language_code' => 'array',
            'currency_code' => 'required',
            'currency_symbol_position' => 'required',
            'currency_decimal_point' => 'required',
            'time_zone' => 'required',
            'time_format' => '',
            'business_favicon' => 'image|mimes:jpeg,jpg,png,gif|max:10000',
            'business_logo' => 'image|mimes:jpeg,jpg,png,gif|max:10000',
            'default_commission' => 'required',
            'pagination_limit' => 'required',
            'footer_text' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        foreach ($validator->validated() as $key => $value) {

            if ($key == 'business_logo') {
                $file = $this->business_setting->where('key_name', 'business_logo')->first();
                $value = file_uploader('business/', 'png', $request->file('business_logo'), !empty($file->live_values) ? $file->live_values : '');
            }
            if ($key == 'business_favicon') {
                $file = $this->business_setting->where('key_name', 'business_favicon')->first();
                $value = file_uploader('business/', 'png', $request->file('business_favicon'), !empty($file->live_values) ? $file->live_values : '');
            }

            $this->business_setting->updateOrCreate(['key_name' => $key], [
                'key_name' => $key,
                'live_values' => $value,
                'test_values' => $value,
                'settings_type' => 'business_information',
                'mode' => 'live',
                'is_active' => 1,
            ]);
        }

        session()->forget('pagination_limit');

        return response()->json(response_formatter(DEFAULT_UPDATE_200), 200);
    }


    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function service_setup_set(Request $request): JsonResponse
    {
        $request[$request['key']] = $request['value'];

        $validator = Validator::make($request->all(), [
            'schedule_booking' => 'in:1,0',
            'provider_can_cancel_booking' => 'in:1,0',
            'service_man_can_cancel_booking' => 'in:1,0',
            'admin_order_notification' => 'in:1,0',
            'sms_verification' => 'in:1,0',
            'email_verification' => 'in:1,0',
            'provider_self_registration' => 'in:1,0',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        foreach ($validator->validated() as $key => $value) {
            $this->business_setting->updateOrCreate(['key_name' => $key, 'settings_type' => 'service_setup'], [
                'key_name' => $key,
                'live_values' => $value,
                'test_values' => $value,
                'is_active' => $value,
                'settings_type' => 'service_setup',
                'mode' => 'live',
            ]);
        }

        return response()->json(response_formatter(DEFAULT_UPDATE_200), 200);
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Application|Factory|View
     */
    public function pages_setup_get(Request $request): View|Factory|Application
    {
        $web_page = $request->has('web_page') ? $request['web_page'] : 'about_us';
        $data_values = $this->business_setting->where('settings_type', 'pages_setup')->orderBy('key_name')->get();
        return view('businesssettingsmodule::admin.page-settings', compact('data_values', 'web_page'));
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function pages_setup_set(Request $request): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'page_name' => 'required|in:about_us,privacy_policy,terms_and_conditions,refund_policy,cancellation_policy',
            'page_content' => ''
        ]);

        $this->business_setting->updateOrCreate(['key_name' => $request['page_name'], 'settings_type' => 'pages_setup'], [
            'key_name' => $request['page_name'],
            'live_values' => $request['page_content'],
            'test_values' => null,
            'settings_type' => 'pages_setup',
            'mode' => 'live',
            'is_active' => $request['is_active'] ?? 0,
        ]);

        if (in_array($request['page_name'], ['privacy_policy', 'terms_and_conditions'])) {
            $message = translate('page_information_has_been_updated') . '!';
            topic_notification('customer', $request['page_name'], $message, 'def.png', null);
            topic_notification('provider-admin', $request['page_name'], $message, 'def.png', null);
        }

        Toastr::success(DEFAULT_UPDATE_200['message']);
        return back();
    }
}
