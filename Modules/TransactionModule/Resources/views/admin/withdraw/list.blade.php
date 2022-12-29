@extends('adminmodule::layouts.master')

@section('title',translate('withdraw_request_list'))

@push('css_or_js')

@endpush

@section('content')
    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div
                        class="page-title-wrap d-flex justify-content-between flex-wrap align-items-center gap-3 mb-3">
                        <h2 class="page-title">{{translate('Withdraw_List')}}</h2>
                    </div>

                    <div
                        class="d-flex flex-wrap justify-content-between align-items-center border-bottom mx-lg-4 mb-10 gap-3">
                        <ul class="nav nav--tabs">
                            <li class="nav-item">
                                <a class="nav-link {{$status=='all'?'active':''}}"
                                   href="{{url()->current()}}?status=all">
                                    {{translate('All')}}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{$status=='pending'?'active':''}}"
                                   href="{{url()->current()}}?status=pending">
                                    {{translate('Pending')}}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{$status=='approved'?'active':''}}"
                                   href="{{url()->current()}}?status=approved">
                                    {{translate('Approved')}}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{$status=='denied'?'active':''}}"
                                   href="{{url()->current()}}?status=denied">
                                    {{translate('Denied')}}
                                </a>
                            </li>
                        </ul>

                        <div class="d-flex gap-2 fw-medium">
                            <span class="opacity-75">{{translate('total_withdraw')}}:</span>
                            <span class="title-color">{{$withdraw_requests->total()}}</span>
                        </div>
                    </div>

                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="all-tab-pane">
                            <div class="card">
                                <div class="card-body">
                                    <div class="data-table-top d-flex flex-wrap gap-10 justify-content-between">
                                        <form action="{{url()->current()}}?status={{$status}}"
                                              class="search-form search-form_style-two"
                                              method="POST">
                                            @csrf
                                            <div class="input-group search-form__input_group">
                                            <span class="search-form__icon">
                                                <span class="material-icons">search</span>
                                            </span>
                                                <input type="search" class="theme-input-style search-form__input"
                                                       value="{{$search}}" name="search"
                                                       placeholder="{{translate('search_here')}}">
                                            </div>
                                            <button type="submit"
                                                    class="btn btn--primary">{{translate('search')}}</button>
                                        </form>
                                    </div>

                                    <div class="table-responsive">
                                        <table id="example" class="table align-middle">
                                            <thead class="text-nowrap">
                                            <tr>
                                                <th>{{translate('SL')}}</th>
                                                <th>{{translate('Provider')}}</th>
                                                <th>{{translate('Amount')}}</th>
                                                <th>{{translate('Provider_Note')}}</th>
                                                <th>{{translate('Admin_Note')}}</th>
                                                <th>{{translate('Request_Time')}}</th>
                                                <th class="text-center">{{translate('Status')}}</th>
                                                <th class="text-center">{{translate('Action')}}</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($withdraw_requests as $key=>$withdraw_request)
                                                <tr>
                                                    <td>{{$withdraw_requests->firstitem()+$key}}</td>
                                                    <td class="text-capitalize">{{Str::limit($withdraw_request->user?$withdraw_request->user->first_name.' '.$withdraw_request->user->last_name:'', 30)}}</td>
                                                    <td>{{with_currency_symbol($withdraw_request->amount)}}</td>
                                                    <td>{{Str::limit($withdraw_request->note, 100)}}</td>
                                                    <td>{{Str::limit($withdraw_request->admin_note, 100)}}</td>
                                                    <td>{{date('d-M-y H:iA', strtotime($withdraw_request->created_at))}}</td>
                                                    <td class="text-center">
                                                        @if($withdraw_request->request_status == 'pending')
                                                            <label class="badge badge-info">
                                                                {{translate('pending')}}
                                                            </label>
                                                        @elseif($withdraw_request->request_status == 'approved')
                                                            <label class="badge badge-success">
                                                                {{translate('approved')}}
                                                            </label>
                                                        @elseif($withdraw_request->request_status == 'denied')
                                                            <label class="badge badge-danger">
                                                                {{translate('denied')}}
                                                            </label>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="d-flex gap-2 justify-content-center">
                                                            @if($withdraw_request->request_status=='pending')
                                                                <a type="button" class="btn btn--danger"
                                                                id="button-{{$withdraw_request->id}}"
                                                                onclick="approve_alert('{{route('admin.withdraw.update_status',[$withdraw_request->id, 'status'=>'denied'])}}','{{translate('want_to_deny_the_withdraw_request')}}?')">
                                                                    <span class="material-icons">block</span>
                                                                    {{translate('Deny')}}
                                                                </a>
                                                                <a type="button" class="btn btn-success"
                                                                id="button-{{$withdraw_request->id}}"
                                                                onclick="approve_alert('{{route('admin.withdraw.update_status',[$withdraw_request->id, 'status'=>'approved'])}}','{{translate('want_to_approve_the_withdraw_request')}}?')">
                                                                    <span class="material-icons">done_outline</span>
                                                                    {{translate('Approve')}}
                                                                </a>
                                                            @elseif($withdraw_request->request_status=='approved')
                                                                <label class="btn btn-success">{{translate('already_approved')}}</label>
                                                            @elseif($withdraw_request->request_status=='denied')
                                                                <label class="btn btn--danger">{{translate('already_denied')}}</label>
                                                            @endif
                                                        </div>
                                                    </td>
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
                </div>
            </div>
        </div>
    </div>
    <!-- End Main Content -->
@endsection

@push('script')
    <script>
        function approve_alert(route, message) {
            (async () => {

                await Swal.fire({
                    html:
                        '{{translate("Leave_reason_for_the_action")}}',
                    input: 'textarea',
                    inputLabel: 'Message',
                    inputPlaceholder: '{{translate("Type note here...")}}',
                    inputAttributes: {
                        'aria-label': '{{translate("Type note here...")}}'
                    },
                    showCancelButton: true,
                    confirmButtonText: '{{translate("proceed")}}',
                    cancelButtonText: '{{translate("Terminate")}}',

                }).then(result => {

                    if (!result.dismiss) {
                        $.post({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            url: route,
                            dataType: 'json',
                            data: {
                                note: result.value
                            },
                            beforeSend: function () {
                                /*$('#loading').show();*/
                            },
                            success: function (data) {
                                location.reload();
                                toastr.success(data.message, {
                                    CloseButton: true,
                                    ProgressBar: true
                                });
                            },
                            complete: function () {
                                /*$('#loading').hide();*/
                            },
                        });
                    }
                    else {
                        Swal.fire('{{translate('Terminated')}}');
                    }
                })


            })()

        }
    </script>


@endpush
