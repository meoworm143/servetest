@extends('providermanagement::layouts.master')

@section('title',translate('Withdraw'))

@push('css_or_js')

@endpush

@section('content')
    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <div class="page-title-wrap mb-3">
                <h2 class="page-title">{{translate('Account_Information')}}</h2>
            </div>

            <!-- Nav Tabs -->
            <div class="mb-3">
                <ul class="nav nav--tabs nav--tabs__style2">
                    <li class="nav-item">
                        <a class="nav-link {{$page_type=='overview'?'active':''}}"
                           href="{{route('provider.account_info', ['page_type'=>'overview'])}}">{{translate('Overview')}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{$page_type=='commission-info'?'active':''}}"
                           href="{{route('provider.account_info', ['page_type'=>'commission-info'])}}">{{translate('Commission_Info')}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{$page_type=='review'?'active':''}}"
                           href="{{route('provider.account_info', ['page_type'=>'review'])}}">{{translate('Reviews')}}</a>
                    </li>
                </ul>
            </div>
            <!-- End Nav Tabs -->

            <!-- Tab Content -->
            <div class="tab-content">
                <div class="tab-pane fade show active" id="overview-tab-pane">
                    <div class="card mb-30">
                        <div class="card-body p-30">
                            <form action="{{route('provider.withdraw.store')}}" method="POST">
                                @csrf
                                <div class="row g-4">
                                    <div class="col-12">
                                        <div class="form-floating">
                                            <input type="number" min="1" step="any" class="form-control" placeholder="{{translate('Amount')}}"
                                                   name="amount" required>
                                            <label>{{translate('Amount_*')}}</label>
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <textarea type="text" class="form-control" placeholder="{{translate('Note')}}"
                                              name="note" maxlength="255"></textarea>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end mt-30">
                                    <button type="submit" class="btn btn--primary">{{translate('submit_request')}}</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="data-table-top d-flex flex-wrap gap-10 justify-content-between">
                                <form action="{{url()->current()}}"
                                      class="search-form search-form_style-two"
                                      method="GET">
                                    @csrf
                                    <div class="input-group search-form__input_group">
                                            <span class="search-form__icon">
                                                <span class="material-icons">search</span>
                                            </span>
                                        <input type="search" class="theme-input-style search-form__input"
                                               value="{{$search}}" name="search"
                                               placeholder="{{translate('search_here')}}">
                                    </div>
                                    <button type="submit" class="btn btn--primary">
                                        {{translate('search')}}
                                    </button>
                                </form>
                            </div>

                            <div class="table-responsive">
                                <table id="example" class="table align-middle">
                                    <thead class="text-nowrap">
                                        <tr>
                                            <th>SL</th>
                                            <th>{{translate('Note')}}</th>
                                            <th>{{translate('Requested_Amount')}}</th>
                                            <th>{{translate('Admin_Note')}}</th>
                                            <th>{{translate('Status')}}</th>
                                            <th>{{translate('Requested_at')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($withdraw_requests as $key=>$withdraw_request)
                                        <tr>
                                            <td>{{$withdraw_requests->firstitem()+$key}}</td>
                                            <td>{{$withdraw_request->note}}</td>
                                            <td>{{$withdraw_request->amount}}</td>
                                            <td>{{$withdraw_request->admin_note}}</td>
                                            <td>{{translate($withdraw_request->request_status)}}</td>
                                            <td>{{date('d-M-y H:i a',strtotime($withdraw_request->created_at))}}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-end">
                                {!! $withdraw_requests->links() !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Tab Content -->
        </div>
    </div>
    <!-- End Main Content -->
@endsection

@push('script')


@endpush
