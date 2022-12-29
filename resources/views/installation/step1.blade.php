@extends('layouts.blank')
@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-body">
                <div class="mar-ver pad-btm text-center">
                    <h1 class="h3">Installation Progress Started!</h1>
                    <p>We are checking file permissions.</p>
                </div>

                <ul class="list-group mt-3">
                    <li class="list-group-item text-semibold">
                        Php version 8.0 +

                        @php
                            $phpVersion = number_format((float)phpversion(), 2, '.', '');
                        @endphp
                        @if ($phpVersion >= 8.0)
                            <i class="material-icons">check</i>
                        @else
                            <i class="material-icons">cancel</i>
                        @endif
                    </li>
                    <li class="list-group-item text-semibold">
                        Curl Enabled

                        @if ($permission['curl_enabled'])
                            <i class="material-icons">check</i>
                        @else
                            <i class="material-icons">cancel</i>
                        @endif
                    </li>
                    <li class="list-group-item text-semibold">
                        <b>.env</b> File Permission

                        @if ($permission['db_file_write_perm'])
                            <i class="material-icons">check</i>
                        @else
                            <i class="material-icons">cancel</i>
                        @endif
                    </li>
                    <li class="list-group-item text-semibold">
                        <b>RouteServiceProvider.php</b> File Permission

                        @if ($permission['routes_file_write_perm'])
                            <i class="material-icons">check</i>
                        @else
                            <i class="material-icons">cancel</i>
                        @endif
                    </li>
                </ul>

                <p class="text-center pt-3">
                    @if ($permission['curl_enabled'] == 1 && $permission['db_file_write_perm'] == 1 && $permission['routes_file_write_perm'] == 1 && $phpVersion >= 8.0)
                        <a href="{{ route('step2',['token'=>bcrypt('step_2')]) }}" class="btn btn--primary">Next <i class="fa fa-forward"></i></a>
                    @endif
                </p>
            </div>
        </div>
    </div>
@endsection
