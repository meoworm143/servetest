@extends('layouts.blank')

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-body">
                    @if(session()->has('error'))
                        <div class="alert alert-danger" role="alert">
                            {{session('error')}}
                        </div>
                    @endif
                    <div class="mar-ver pad-btm text-center">
                        <h1 class="h3">Purchase Code</h1>
                        <p>
                            Provide your codecanyon purchase code.<br>
                            <a href="https://help.market.envato.com/hc/en-us/articles/202822600-Where-Is-My-Purchase-Code"
                               class="text-info">Where to get purchase code?</a>
                        </p>
                    </div>
                    <div class="text-muted mt-3">
                        <form method="POST" action="{{ route('purchase.code',['token'=>bcrypt('step_3')]) }}">
                            @csrf
                            <div class="mb-30">
                                <div class="form-floating">
                                    <input type="text" class="form-control" name="username"
                                           placeholder="{{translate('ex_:_nipon_doe')}} *"
                                           required="" value="{{env('BUYER_USERNAME')}}">
                                    <label>{{translate('your_codecanyon_username')}} *</label>
                                </div>
                            </div>

                            <div class="mb-30">
                                <div class="form-floating">
                                    <input type="text" class="form-control" name="purchase_key"
                                           placeholder="ex : 1v23f-2333-32rr-22323rc *"
                                           required="" value="{{env('PURCHASE_CODE')}}">
                                    <label>{{translate('purchase_code')}} *</label>
                                </div>
                            </div>

                            <div class="text-center">
                                <button type="submit" class="btn btn--primary">Continue</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
