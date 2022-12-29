@extends('layouts.blank')

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="card mt-3">
                <div class="card-body">
                    <div class="mar-ver pad-btm text-center mb-4">
                        <h1 class="h3">
                            Software Update
                        </h1>
                    </div>

                    <form method="POST" action="{{route('update-system')}}">
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
                            <button type="submit" class="btn btn--primary">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
