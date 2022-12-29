@extends('layouts.blank')
@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-body">
                <div class="mar-ver pad-btm text-center mb-3">
                    <h1 class="h3">Admin Account Settings <i class="fa fa-cogs"></i></h1>
                    <p>Provide your information.</p><br>
                </div>
                <div class="text-muted">
                    <form method="POST" action="{{ route('system_settings',['token'=>bcrypt('step_6')]) }}">
                        @csrf
                        <div class="row">
                            <div class="col-6">
                                <div class="mb-30">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" name="first_name" required="">
                                        <label>First Name *</label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="mb-30">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" name="last_name" required="">
                                        <label>Last Name *</label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="mb-30">
                                    <div class="form-floating">
                                        <input type="email" class="form-control" name="email" required="">
                                        <label>Admin Login Email *</label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="mb-30">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" name="phone" required="">
                                        <label>Phone Number *</label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="mb-30">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" name="password" required="">
                                        <label>Password *</label>
                                    </div>
                                </div>
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
@endsection
