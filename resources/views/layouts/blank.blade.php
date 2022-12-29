<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>
        @yield('title')
    </title>
    <!-- SEO Meta Tags-->
    <meta name="description" content="">
    <meta name="keywords" content="">
    <meta name="author" content="">
    <!-- Viewport-->
    <meta name="_token" content="{{csrf_token()}}">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- ======= BEGIN GLOBAL MANDATORY STYLES ======= -->
    <link href="{{asset('public/assets/admin-module')}}/css/material-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="{{asset('public/assets/admin-module')}}/css/bootstrap.min.css"/>
    <link rel="stylesheet"
          href="{{asset('public/assets/admin-module')}}/plugins/perfect-scrollbar/perfect-scrollbar.min.css"/>
    <!-- ======= END BEGIN GLOBAL MANDATORY STYLES ======= -->

    <!-- Favicon and Touch Icons-->
    <link rel="apple-touch-icon" sizes="180x180" href="">
    <link rel="icon" type="image/png" sizes="32x32" href="">
    <!-- Main Theme Styles + Bootstrap-->
    <link rel="stylesheet" href="{{asset('public/assets/admin-module')}}/css/style.css"/>
    <link rel="stylesheet" href="{{asset('public/assets/admin-module')}}/css/toastr.css"/>
    <style>
        .list-group-item {
            padding-top: 18px !important;
            padding-bottom: 18px !important;
            background-color: transparent;
        }
        .dropdown-menu, .card {
            background-color: #ffffffe3;
            border: none;
        }
        .form-control {
            background-color: #ffffff;
            color: var(--title-color);
        }
    </style>
</head>
<!-- Body-->
<body>

<!-- Page Content-->
<main class="main-area"
      style="padding-right: 330px;padding-left: 330px; background-image:url('{{asset('public/assets/background.png')}}'); background-size: cover;">
    @yield('content')
</main>

<script src={{asset("public/assets/admin-module/js/jquery-3.6.0.min.js")}}></script>
<script src={{asset("public/assets/admin-module/js/main.js")}}></script>
<script src={{asset("public/assets/admin-module/js/toastr.js")}}></script>
{!! Toastr::message() !!}

</body>
</html>
