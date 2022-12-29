<?php

namespace App\Http\Controllers;

use App\CPU\Helpers;
use App\Model\AdminWallet;
use App\Traits\ActivationClass;
use App\Traits\UnloadedHelpers;
use App\User;
use App\Model\BusinessSetting;
use App\Model\Color;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Mockery\Exception;

class UpdateController extends Controller
{
    use UnloadedHelpers;
    use ActivationClass;

    public function update_software_index()
    {
        return view('update.update-software');
    }

    public function update_software(Request $request)
    {
        $this->setEnvironmentValue('SOFTWARE_ID', 'NDAyMjQ3NzI=');
        $this->setEnvironmentValue('BUYER_USERNAME', $request['username']);
        $this->setEnvironmentValue('PURCHASE_CODE', $request['purchase_key']);
        $this->setEnvironmentValue('SOFTWARE_VERSION', '1.1.1');
        $this->setEnvironmentValue('APP_ENV', 'live');

        $data = $this->actch();
        try{
            if (!$data->getData()->active) {
                return redirect(base64_decode('aHR0cHM6Ly82YW10ZWNoLmNvbS9zb2Z0d2FyZS1hY3RpdmF0aW9u'));
            }
        }catch (Exception $exception){
            Toastr::error('verification failed! try again');
            return back();
        }

        Artisan::call('migrate', ['--force' => true]);
        $previousRouteServiceProvier = base_path('app/Providers/RouteServiceProvider.php');
        $newRouteServiceProvier = base_path('app/Providers/RouteServiceProvider.txt');
        copy($newRouteServiceProvier, $previousRouteServiceProvier);

        Artisan::call('module:enable');

        Artisan::call('cache:clear');
        Artisan::call('view:clear');
        Artisan::call('config:cache');
        Artisan::call('config:clear');
        Artisan::call('optimize:clear');

        return redirect(env('APP_URL'));
    }
}
