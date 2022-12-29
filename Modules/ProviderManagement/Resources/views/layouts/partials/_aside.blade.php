<?php
$booking = \Modules\BookingModule\Entities\Booking::where('provider_id', auth()->user()->provider->id)->get();

$subscribed_sub_category_ids = \Modules\ProviderManagement\Entities\SubscribedService::where(['provider_id' => auth()->user()->provider->id])->ofSubscription(1)->pluck('sub_category_id')->toArray();
$pending_booking_count = \Modules\BookingModule\Entities\Booking::ofBookingStatus('pending')->whereIn('sub_category_id', $subscribed_sub_category_ids)->count();
$logo = business_config('business_logo', 'business_information');
?>

@php($provider = auth()->user()->provider)
<aside class="aside">
    <!-- Aside Header -->
    <div class="aside-header">
        <!-- Logo -->
        <a href="{{route('admin.dashboard')}}" class="logo d-flex gap-2">
            <img src="{{asset('storage/app/public/business')}}/{{$logo->live_values??""}}"
                 onerror="this.src='{{asset('public/assets/placeholder.png')}}'"
                 style="max-height: 50px" alt=""
                 class="main-logo">
            <img src="{{asset('storage/app/public/business')}}/{{$logo->live_values??""}}"
                 onerror="this.src='{{asset('public/assets/placeholder.png')}}'"
                 style="max-height: 40px" alt=""
                 class="mobile-logo">
        </a>
        <!-- End Logo -->

        <!-- Aside Toggle Menu Button -->
        <button class="toggle-menu-button aside-toggle border-0 bg-transparent p-0 dark-color">
            <span class="material-icons">menu</span>
        </button>
        <!-- End Aside Toggle Menu Button -->
    </div>
    <!-- End Aside Header -->

    <!-- Aside Body -->
    <div class="aside-body" data-trigger="scrollbar">
        <div class="user-profile media gap-3 align-items-center my-3">
            <div class="avatar">
                <img class="avatar-img rounded-circle"
                     src="{{asset('storage/app/public/provider/logo')}}/{{ $provider->logo }}"
                     onerror="this.src='{{asset('public/assets/provider-module')}}/img/user2x.png'"
                     alt="">
            </div>
            <div class="media-body ">
                <h5 class="card-title">{{ Str::limit($provider->company_email, 30) }}</h5>
                <span class="card-text">{{ Str::limit($provider->company_name, 30) }}</span>
            </div>
        </div>

        <!-- Nav -->
        <ul class="nav">
            <li class="nav-category">{{translate('main')}}</li>
            <li>
                <a href="{{route('provider.dashboard')}}"
                   class="{{request()->is('provider/dashboard')?'active-menu':''}}">
                    <span class="material-icons" title="{{translate('dashboard')}}">dashboard</span>
                    <span class="link-title">{{translate('dashboard')}}</span>
                </a>
            </li>

            <li class="nav-category"
                title="{{translate('Service_Management')}}">{{translate('Service_Management')}}</li>
            <li>
                <a href="{{route('provider.service.available')}}"
                   class="{{request()->is('provider/service/available')?'active-menu':''}}">
                    <span class="material-icons" title="{{translate('available_services')}}">home_repair_service</span>
                    <span class="link-title">{{translate('available_services')}}</span>
                </a>
            </li>
            <li>
                <a href="{{route('provider.sub_category.subscribed', ['status'=>'all'])}}"
                   class="{{request()->is('provider/sub-category/subscribed*')?'active-menu':''}}">
                    <span class="material-icons" title="{{translate('my_Subscriptions')}}">subscriptions</span>
                    <span class="link-title">{{translate('my_subscriptions')}}</span>
                </a>
            </li>


            <li class="has-sub-item {{request()->is('provider/serviceman/*')?'sub-menu-opened':''}}">
                <a href="#" class="{{request()->is('provider/serviceman/*')?'active-menu':''}}">
                    <span class="material-icons" title="{{translate('Service_Man')}}">man</span>
                    <span class="link-title">{{translate('Service_Man')}}</span>
                </a>
                <!-- Sub Menu -->
                <ul class="nav sub-menu">
                    <li>
                        <a href="{{route('provider.serviceman.list', ['status'=>'all'])}}"
                           class="{{request()->is('provider/serviceman/list')?'active-menu':''}}">
                            {{translate('Serviceman_List')}}
                        </a>
                    </li>
                    <li>
                        <a href="{{route('provider.serviceman.create')}}"
                           class="{{request()->is('provider/serviceman/create')?'active-menu':''}}">
                            {{translate('add_new_serviceman')}}
                        </a>
                    </li>
                </ul>
                <!-- End Sub Menu -->
            </li>


            <li class="nav-category" title="{{translate('booking_management')}}">
                {{translate('booking_management')}}
            </li>
            <li class="has-sub-item {{request()->is('provider/booking/*')?'sub-menu-opened':''}}">
                <a href="#" class="{{request()->is('provider/booking/*')?'active-menu':''}}">
                    <span class="material-icons" title="{{translate('bookings')}}">shopping_cart</span>
                    <span class="link-title">{{translate('bookings')}}</span>
                </a>

                <!-- Sub Menu -->
                <ul class="nav sub-menu">
                    <li>
                        <a href="{{route('provider.booking.list', ['booking_status'=>'pending'])}}"
                           class="{{request()->is('provider/booking/list') && request()->query('booking_status')=='pending'?'active-menu':''}}">
                            <span class="link-title">{{translate('Booking_Requests')}}
                                <span class="count">{{$pending_booking_count}}</span>
                            </span>
                        </a>
                    </li>
                    <li>
                        <a href="{{route('provider.booking.list', ['booking_status'=>'accepted'])}}"
                           class="{{request()->is('provider/booking/list') && request()->query('booking_status')=='accepted'?'active-menu':''}}">
                            <span class="link-title">{{translate('Accepted')}}
                                <span class="count">{{$booking->where('booking_status', 'accepted')->count()}}</span>
                            </span>
                        </a>
                    </li>
                    <li>
                        <a href="{{route('provider.booking.list', ['booking_status'=>'ongoing'])}}"
                           class="{{request()->is('provider/booking/list') && request()->query('booking_status')=='ongoing'?'active-menu':''}}">
                            <span class="link-title">{{translate('Ongoing')}}
                                <span class="count">{{$booking->where('booking_status', 'ongoing')->count()}}</span>
                            </span>
                        </a>
                    </li>
                    <li>
                        <a href="{{route('provider.booking.list', ['booking_status'=>'completed'])}}"
                           class="{{request()->is('provider/booking/list') && request()->query('booking_status')=='completed'?'active-menu':''}}">
                            <span class="link-title">{{translate('Completed')}}
                                <span class="count">{{$booking->where('booking_status', 'completed')->count()}}</span>
                            </span>
                        </a>
                    </li>
                    <li>
                        <a href="{{route('provider.booking.list', ['booking_status'=>'canceled'])}}"
                           class="{{request()->is('provider/booking/list') && request()->query('booking_status')=='canceled'?'active-menu':''}}">
                            <span class="link-title">{{translate('Canceled')}}
                                <span class="count">{{$booking->where('booking_status', 'canceled')->count()}}</span>
                            </span>
                        </a>
                    </li>
                </ul>
                <!-- End Sub Menu -->
            </li>

            <li class="nav-category" title="{{translate('account')}}">{{translate('account_management')}}</li>
            <li>
                <a href="{{route('provider.account_info', ['page_type'=>'overview'])}}" class="{{request()->is('provider/account-info*') || request()->is('provider/withdraw') ?'active-menu':''}}">
                    <span class="material-icons" title="{{translate('Account_Information')}}">account_circle</span>
                    <span class="link-title">{{translate('Account_Information')}}</span>
                </a>
            </li>
            <li>
                <a href="{{route('provider.bank_info')}}" class="{{request()->is('provider/bank-info*')?'active-menu':''}}">
                    <span class="material-icons" title="{{translate('bank_information')}}">account_balance</span>
                    <span class="link-title">{{translate('bank_information')}}</span>
                </a>
            </li>
        </ul>
        <!-- End Nav -->
    </div>
    <!-- End Aside Body -->
</aside>
