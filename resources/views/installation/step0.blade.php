@extends('layouts.blank')
@section('content')
    <!-- Wrapper -->

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
                        <h1 class="h3">Demandium Software Installation</h1>
                        <p>Provide required information.</p>
                    </div>
                    <ol class="list-group mt-3">
                        <li class="list-group-item text-semibold">
                            <i class="material-icons">check</i>
                            <span>Database Name</span>
                        </li>
                        <li class="list-group-item text-semibold">
                            <i class="material-icons">check</i>
                            <span>Database UserName</span>
                        </li>
                        <li class="list-group-item text-semibold">
                            <i class="material-icons">check</i>
                            <span>Database Password</span>
                        </li>
                        <li class="list-group-item text-semibold">
                            <i class="material-icons">check</i>
                            <span>Database Host</span>
                        </li>
                    </ol>
                    <br>
                    <div class="text-center">
                        <a href="{{ route('step1',['token'=>bcrypt('step_1')]) }}"
                           class="btn btn--primary text-light">
                            Installation Start <i class="fa fa-forward"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- End wrapper -->
@endsection
