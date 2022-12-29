@extends('adminmodule::layouts.master')

@section('title',translate('provider_update'))

@push('css_or_js')
    {{--  Int ph  --}}
    <!-- CSS -->
    {{--<link href='https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css' rel='stylesheet' type='text/css'>--}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.3/css/intlTelInput.min.css" />
    <!-- JS -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.3/js/intlTelInput.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.3/js/utils.min.js"></script>

@endpush

@section('content')
    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3">
                        <h2 class="page-title">{{translate('Update_Provider')}}</h2>
                    </div>
                    <div class="pb-4">
                        <form action="{{route('admin.provider.update', [$provider->id])}}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h4 class="c1 mb-20">{{translate('General_Information')}}</h4>
                                            <div class="form-floating mb-30">
                                                <input type="text" class="form-control" value="{{$provider->company_name}}"
                                                        name="company_name" required
                                                        placeholder="{{translate('Company_/_Individual_Name')}}">
                                                <label>{{translate('Company_/_Individual_Name')}}</label>
                                            </div>
                                            <div class="form-floating mb-30">
                                                <input type="text" class="form-control"
                                                        name="company_phone" value="{{$provider->company_phone}}"
                                                        placeholder="{{translate('Phone')}}" required>
                                                <label>
                                                    {{translate('Phone')}}
                                                </label>
                                                <small class="text-danger d-flex mt-1">* ( {{translate('country_code_required')}} )</small>
                                            </div>
                                            <div class="form-floating mb-30">
                                                <input type="email" class="form-control"
                                                       name="company_email" value="{{$provider->company_email}}"
                                                       placeholder="{{translate('Email')}}" required>
                                                <label>{{translate('Email')}}</label>
                                            </div>
                                            <div class="form-floating mb-30">
                                                <select class="select-identity theme-input-style w-100" name="zone_id" required>
                                                    <option>{{translate('Select_Zone')}}</option>
                                                    @foreach($zones as $zone)
                                                        <option value="{{$zone->id}}"
                                                            {{$provider->zone_id == $zone->id ? 'selected': ''}}>
                                                            {{$zone->name}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-floating mb-30">
                                                <textarea class="form-control" placeholder="{{translate('Address')}}"
                                                    name="company_address" required>{{$provider->company_address}}</textarea>
                                                <label>{{translate('Address')}}</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="d-flex flex-column align-items-center gap-3">
                                                <h3 class="mb-0">{{translate('Company_Logo')}}</h3>
                                                <div>
                                                    <div class="upload-file">
                                                        <input type="file" class="upload-file__input" name="logo">
                                                        <div class="upload-file__img">
                                                            <img
                                                                src="{{asset('storage/app/public/provider/logo')}}/{{$provider->logo}}"
                                                                onerror="this.src='{{asset('public/assets/admin-module')}}/img/media/upload-file.png'"
                                                                alt="">
                                                        </div>
                                                        <span class="upload-file__edit">
                                                            <span class="material-icons">edit</span>
                                                        </span>
                                                    </div>
                                                </div>
                                                <p class="opacity-75 max-w220 mx-auto">Image format - jpg, png,
                                                    jpeg,
                                                    gif Image
                                                    Size -
                                                    maximum size 2 MB Image Ratio - 1:1</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row gx-2 mt-2">
                                <div class="col-md-6">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <h4 class="c1 mb-20">{{translate('Business Information')}}</h4>
                                            <div class="mb-30">
                                                <select class="select-identity theme-input-style w-100" name="identity_type" required>
                                                    <option>{{translate('Select_Identity_Type')}}</option>
                                                    <option value="passport"
                                                        {{$provider->owner->identification_type == 'passport' ? 'selected': ''}}>
                                                        {{translate('Passport')}}</option>
                                                    <option value="driving_license"
                                                        {{$provider->owner->identification_type == 'driving_license' ? 'selected': ''}}>
                                                        {{translate('Driving_License')}}</option>
                                                    <option value="company_id"
                                                        {{$provider->owner->identification_type == 'company_id' ? 'selected': ''}}>
                                                        {{translate('Company_Id')}}</option>
                                                    <option value="nid"
                                                        {{$provider->owner->identification_type == 'nid' ? 'selected': ''}}>
                                                        {{translate('nid')}}</option>
                                                    <option value="trade_license"
                                                        {{$provider->owner->identification_type == 'trade_license' ? 'selected': ''}}>
                                                        {{translate('Trade_License')}}</option>
                                                </select>
                                            </div>
                                            <div class="form-floating mb-30">
                                                <input type="text" class="form-control" name="identity_number"
                                                        value="{{$provider->owner->identification_number}}"
                                                        placeholder="{{translate('Identity_Number')}}" required>
                                                <label>{{translate('Identity_Number')}}</label>
                                            </div>

                                            <div class="upload-file w-100">
                                                <h3 class="mb-3">{{translate('Identification_Image')}}</h3>
                                                <div id="multi_image_picker">
                                                    <div class="item mb-2">
                                                        @foreach($provider->owner->identification_image as $img)
                                                            <img class="p-1" height="150"
                                                                 src="{{asset('storage/app/public/provider/identity').'/'.$img}}"
                                                                 onerror="this.src='{{asset('public/assets/admin-module')}}/img/media/provider-id.png'">
                                                            <br/>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <div class="d-flex flex-wrap justify-content-between gap-3 mb-20">
                                                <h4 class="c1">{{translate('Contact_Person')}}</h4>

{{--                                                <div class="custom-checkbox">--}}
{{--                                                    <input type="checkbox" class="custom-checkbox__input"--}}
{{--                                                            id="same-as-general" checked>--}}
{{--                                                    <label class="custom-checkbox__label"--}}
{{--                                                            for="same-as-general">--}}
{{--                                                        {{translate('Same_as_General_Information')}}</label>--}}
{{--                                                </div>--}}
                                            </div>
                                            <div class="form-floating mb-30">
                                                <input type="text" class="form-control" name="contact_person_name"
                                                        value="{{$provider->contact_person_name}}" placeholder="name"
                                                        required>
                                                <label>{{translate('Name')}}</label>
                                            </div>
                                            <div class="row gx-2">
                                                <div class="col-lg-6">
                                                    <div class="form-floating mb-30">
                                                        <input type="tel" class="form-control" name="contact_person_phone"
                                                                value="{{$provider->contact_person_phone}}" placeholder="{{translate('Phone')}}"
                                                                required>
                                                        <label>{{translate('Phone')}}</label>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="form-floating mb-30">
                                                        <input type="email" class="form-control" name="contact_person_email"
                                                                value="{{$provider->contact_person_email}}" placeholder="{{translate('Email')}}"
                                                                required>
                                                        <label>{{translate('Email')}}</label>
                                                    </div>
                                                </div>
                                            </div>

                                            <h4 class="c1 mb-20">{{translate('Account_Information')}}</h4>
                                            <div class="row gx-2">
                                                <div class="col-lg-6">
                                                    <div class="form-floating mb-30">
                                                        <input type="text" class="form-control" name="account_first_name"
                                                                value="{{$provider->owner->first_name}}"
                                                                placeholder="{{translate('first_name')}}"
                                                                required>
                                                        <label>{{translate('First_Name')}}</label>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="form-floating mb-30">
                                                        <input type="text" class="form-control" name="account_last_name"
                                                                value="{{$provider->owner->last_name}}"
                                                                placeholder="{{translate('last_name')}}"
                                                                required>
                                                        <label>{{translate('Last_Name')}}</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-floating mb-30">
                                                <input type="email" class="form-control" name="account_email"
                                                        value="{{$provider->owner->email}}"
                                                        placeholder="{{translate('Email')}}"
                                                        required>
                                                <label>{{translate('Email_*')}}</label>
                                            </div>
                                            <div class="form-floating mb-30">
                                                <input type="tel" class="form-control" name="account_phone"
                                                       value="{{$provider->owner->phone}}"
                                                       placeholder="{{translate('Phone')}}"
                                                       required>
                                                <label>{{translate('Phone')}}</label>
                                            </div>
                                            <div class="row gx-2">
                                                <div class="col-lg-6">
                                                    <div class="form-floating mb-30">
                                                        <input type="password" class="form-control" name="password"
                                                                placeholder="{{translate('Password')}}">
                                                        <label>{{translate('Password')}}</label>
                                                        <span class="material-icons togglePassword">visibility_off</span>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="form-floating mb-30">
                                                        <input type="password" class="form-control" name="confirm_password"
                                                                placeholder="{{translate('Confirm_Password')}}">
                                                        <label>{{translate('Confirm_Password')}}</label>
                                                        <span class="material-icons togglePassword">visibility_off</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex gap-4 flex-wrap justify-content-end mt-20">
                                <button type="reset" class="btn btn--secondary">{{translate('Reset')}}</button>
                                <button type="submit" class="btn btn--primary">{{translate('Submit')}}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Main Content -->
@endsection

@push('script')

    <script src="{{asset('public/assets/provider-module')}}/js//tags-input.min.js"></script>
    <script src="{{asset('public/assets/provider-module')}}/js/spartan-multi-image-picker.js"></script>
    <script>
        $("#multi_image_picker").spartanMultiImagePicker({
            fieldName: 'identity_images[]',
            maxCount: 2,
            rowHeight: 'auto',
            groupClassName: 'item',
            //maxFileSize: '',
            dropFileLabel: "{{translate('Drop_here')}}",
            placeholderImage: {
                image: '{{asset('public/assets/admin-module')}}/img/media/banner-upload-file.png',
                width: '100%',
            },

            onRenderedPreview: function (index) {
                toastr.success('{{translate('Image_added')}}', {
                    CloseButton: true,
                    ProgressBar: true
                });
            },
            onRemoveRow: function (index) {
                console.log(index);
            },
            onExtensionErr: function (index, file) {
                toastr.error('{{translate('Please_only_input_png_or_jpg_type_file')}}', {
                    CloseButton: true,
                    ProgressBar: true
                });
            },
            onSizeErr: function (index, file) {
                toastr.error('{{translate('File_size_too_big')}}', {
                    CloseButton: true,
                    ProgressBar: true
                });
            }

        });
    </script>

    <script>
        var input = document.querySelector("#phone");
        intlTelInput(input, {
            preferredCountries: ['bd', 'us'],
            initialCountry: "auto",
            geoIpLookup: function (success, failure) {
                $.get("https://ipinfo.io", function () { }, "jsonp").always(function (resp) {
                    var countryCode = (resp && resp.country) ? resp.country : "bd";
                    success(countryCode);
                });
            },
        });
    </script>

@endpush
