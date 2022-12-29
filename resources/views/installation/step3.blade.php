@extends('layouts.blank')
@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-body">
                <div class="mar-ver pad-btm text-center">
                    <h1 class="h3">Configure Database</h1>
                    <p>Provide database information correctly.</p>
                </div>

                @if (isset($error) || session()->has('error'))
                    <div class="row" style="margin-top: 20px;">
                        <div class="col-md-12">
                            <div class="alert alert-danger">
                                <strong>Invalid Database Credentials or Host!! </strong>Please check your database credentials
                                carefully
                            </div>
                        </div>
                    </div>
                @elseif(session()->has('success'))
                    <div class="row" style="margin-top: 20px;">
                        <div class="col-md-12">
                            <div class="alert alert-success">
                                <strong>{{session('success')}}</strong>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="text-muted mt-3">
                    <form method="POST" action="{{ route('install.db',['token'=>bcrypt('step_4')]) }}">
                        @csrf

                        <div class="mb-30">
                            <div class="form-floating">
                                <input type="text" class="form-control" name="DB_HOST" required="">
                                <label>Database Host *</label>
                            </div>
                        </div>

                        <div class="mb-30">
                            <div class="form-floating">
                                <input type="text" class="form-control" name="DB_DATABASE" required="">
                                <label>Database Name *</label>
                            </div>
                        </div>

                        <div class="mb-30">
                            <div class="form-floating">
                                <input type="text" class="form-control" name="DB_USERNAME" required="">
                                <label>Database Username *</label>
                            </div>
                        </div>

                        <div class="mb-30">
                            <div class="form-floating">
                                <input type="text" class="form-control" name="DB_PASSWORD" required="">
                                <label>Database Password *</label>
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
