@extends('layouts.blank')
@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-body">
                    <div class="pad-btm text-center">
                        <h1 class="h3">All Done, Great Job.</h1>
                        <p>Your software is ready to run.</p>
                        <div class="row">
                            <div class="col-sm-12 col-sm-offset-2">
                                <div class="panel bord-all mar-top panel-info">
                                    <div class="panel-heading">
                                        <h3 class="panel-title">
                                            Configure the following setting to run the system properly.
                                        </h3>
                                    </div>
                                    <div class="panel-body mt-3">
                                        <ul class="list-group mar-no mar-top bord-no">
                                            <li class="list-group-item">Business Setting</li>
                                            <li class="list-group-item">MAIL Setting</li>
                                            <li class="list-group-item">Payment Gateway Configuration</li>
                                            <li class="list-group-item">SMS Gateway Configuration</li>
                                            <li class="list-group-item">3rd Party API's</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="text-center pt-3">
                        <a href="{{ env('APP_URL') }}/admin/auth/login" target="_blank" class="btn btn--primary">Admin Panel</a>
                        <a href="{{ env('APP_URL') }}" target="_blank" class="btn btn--secondary">Landing Page</a>
                    </div>
                </div>
            </div>
        </div>
@endsection
