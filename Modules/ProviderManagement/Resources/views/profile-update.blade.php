@extends('providermanagement::layouts.master')

@section('title',translate('Profile_Update'))

@push('css_or_js')

@endpush

@section('content')
    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3">
                        <h2 class="page-title">{{translate('Update_Profile')}}</h2>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <form action="{{ route('provider.profile_update') }}" method="post" enctype="multipart/form-data">
                                @csrf

                                <div class="row">
                                    <div class="col-md-6">
                                        <h4 class="c1 mb-30">{{translate('General_Information')}}</h4>
                                        <div class="form-floating mb-30">
                                            <input type="text" class="form-control" name="company_name" value="{{ $provider->company_name }}"
                                                    placeholder="{{translate('Company_/_Individual_Name')}}">
                                            <label>{{translate('Company_/_Individual_Name')}}</label>
                                        </div>
                                        <div class="form-floating mb-30">
                                            <input type="email" class="form-control" name="company_email" value="{{ $provider->company_email }}"
                                                    placeholder="{{translate('Company_Email')}}">
                                            <label>{{translate('Company_Email')}}</label>
                                        </div>
                                        <div class="form-floating mb-30">
                                            <input oninput="this.value = this.value.replace(/[^+\d]+$/g, '').replace(/(\..*)\./g, '$1');" type="tel" class="form-control" name="company_phone" value="{{ $provider->company_phone }}"
                                                    placeholder="{{translate('Company_Phone')}}">
                                            <label>{{translate('Company_Phone')}}</label>
                                            <small class="d-block mt-1 text-danger">* ( {{translate('Country_Code_Required')}} )</small>
                                        </div>
                                        <div class="form-floating mb-30">
                                            <select class="select-zone theme-input-style w-100" name="zone_id" required>
                                                <option selected disabled>{{translate('Select_Zone')}}</option>
                                                @foreach($zones as $zone)
                                                        <option value="{{$zone->id}}" {{ $provider->zone->id == $zone->id ? 'selected' : '' }}>{{$zone->name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-floating mb-30">
                                            <textarea class="form-control" name="company_address"
                                                        placeholder="{{translate('Company_Address')}}">{!! $provider->company_address !!}</textarea>
                                            <label>{{translate('Company_Address')}}</label>
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
                                                            src="{{asset('storage/app/public/provider/logo')}}/{{ $provider->logo }}"
                                                            onerror="this.src='{{asset('public/assets/provider-module')}}/img/media/upload-file.png'"
                                                            alt="">
                                                    </div>
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

                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <h4 class="c1 mb-30">{{translate('Account_Information')}}</h4>
                                        <div class="row gx-2">
                                            <div class="col-lg-6">
                                                <div class="form-floating mb-30">
                                                    <input type="text" class="form-control" name="account_first_name" value="{{ $provider->owner->first_name }}"
                                                            placeholder="{{translate('First_Name')}}">
                                                    <label>{{translate('First_Name')}}</label>
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="form-floating mb-30">
                                                    <input type="text" class="form-control" name="account_last_name" value="{{ $provider->owner->last_name }}"
                                                            placeholder="{{translate('Last_Name')}}">
                                                    <label>{{translate('Last_Name')}}</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-floating mb-30">
                                            <span class="form-control opacity-50"
                                                  data-bs-toggle="tooltip" data-bs-placement="top"
                                                  data-bs-custom-class="custom-tooltip"
                                                  data-bs-title="{{translate('Not_editable')}}">
                                                {{ $provider->owner->email }}
                                            </span>
                                            <label>{{translate('Email')}}</label>
                                        </div>
                                        <div class="form-floating mb-30">
                                            <span class="form-control opacity-50"
                                                  data-bs-toggle="tooltip" data-bs-placement="top"
                                                  data-bs-custom-class="custom-tooltip"
                                                  data-bs-title="{{translate('Not_editable')}}">{{$provider->owner->phone}}</span>
                                            <label>{{translate('Phone')}}</label>
                                        </div>
                                        <div class="row gx-2">
                                            <div class="col-lg-6">
                                                <div class="form-floating mb-30">
                                                    <input type="password" class="form-control" name="password"
                                                            placeholder="{{translate('Password')}}"
                                                            autocomplete="off">
                                                    <label>{{translate('Password')}}</label>
                                                    <span class="material-icons togglePassword">visibility_off</span>
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="form-floating mb-30">
                                                    <input type="password" class="form-control" name="confirm_password"
                                                            placeholder="{{translate('Confirm_Password')}}"
                                                            autocomplete="off">
                                                    <label>{{translate('Confirm_Password')}}</label>
                                                    <span class="material-icons togglePassword">visibility_off</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex flex-wrap justify-content-between gap-3 mb-30">
                                            <h4 class="c1">{{translate('Contact_Person')}}</h4>

{{--                                                    <div class="custom-checkbox">--}}
{{--                                                        <input type="checkbox" class="custom-checkbox__input"--}}
{{--                                                               id="same-as-general" checked>--}}
{{--                                                        <label class="custom-checkbox__label"--}}
{{--                                                               for="same-as-general">{{translate('Same_as_General_Information')}}</label>--}}
{{--                                                    </div>--}}
                                        </div>
                                        <div class="form-floating mb-30">
                                            <input type="text" class="form-control" name="contact_person_name" value="{{ $provider->contact_person_name }}"
                                                    placeholder="{{translate('Name')}}">
                                            <label>{{translate('Name')}}</label>
                                        </div>
                                        <div class="form-floating mb-30">
                                            <input oninput="this.value = this.value.replace(/[^+\d]+$/g, '').replace(/(\..*)\./g, '$1');" type="tel" class="form-control" name="contact_person_phone" value="{{ $provider->contact_person_phone }}"
                                                    placeholder="{{translate('Phone')}}">
                                            <label>{{translate('Phone')}}</label>
                                            <small class="d-block mt-1 text-danger">* ( {{translate('Country_Code_Required')}} )</small>
                                        </div>
                                        <div class="form-floating mb-30">
                                            <input type="email" class="form-control" name="contact_person_email" value="{{ $provider->contact_person_email }}"
                                                    placeholder="{{translate('Business_Email')}}">
                                            <label>{{translate('Business_Email')}}</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex gap-4 flex-wrap justify-content-end mt-20">
                                    <button type="reset" class="btn btn--secondary">{{translate('Reset')}}</button>
                                    <button type="submit" class="btn btn--primary demo_check">{{translate('Update')}}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Main Content -->
@endsection

@push('script')
    <script src="{{asset('public/assets/provider-module')}}/js/spartan-multi-image-picker.js"></script>
    <script>
        $("#multi_image_picker").spartanMultiImagePicker({
                fieldName: 'identity_image[]',
                maxCount: 2,
                rowHeight: '10%',
                groupClassName: 'col-3',
                //maxFileSize: '',
                dropFileLabel : "{{translate('Drop_here')}}",
                placeholderImage: {
                    image: '{{asset('public/assets/provider-module')}}/img/media/upload-file.png',
                    width: '75%',
                },

                onRenderedPreview : function(index){
                    toastr.success('{{translate('Image_added')}}', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                },
                onRemoveRow : function(index){
                    console.log(index);
                },
                onExtensionErr : function(index, file){
                    toastr.error('{{translate('Please_only_input_png_or_jpg_type_file')}}', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                },
                onSizeErr : function(index, file){
                    toastr.error('{{translate('File_size_too_big')}}', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                }
            }
        );
        //tooltip enable
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
    </script>
@endpush
