<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta id="csrf-token" name="csrf-token" content="{{ csrf_token() }}">
    <meta name="author" content="Yeneta School">

    <title> @yield('page_title') | {{ config('app.name') }} </title>
    
    <style>
        /* --- Global Green Theme (#568204) --- */
    
        /* 1. Global Tab Highlights */
        .nav-tabs-highlight .nav-link.active:before {
            background-color: #568204 !important;
        }
    
        .nav-tabs-highlight .nav-link.active {
            color: #568204 !important;
            font-weight: 600;
        }
    
        /* 2. Global Button Color Override */
        /* This will change every button using bg-success-800 across the site */
        .bg-success-800 {
            background-color: #568204 !important;
            border-color: #568204 !important;
        }
        
        .bg-success-800:hover {
            background-color: #456903 !important;
        }
    
        /* 3. Global Table Shrink (Fixes the "huge" text issue) */
        .table {
            font-size: 13px !important;
        }
    
        .table thead th {
            font-size: 12px;
            text-transform: uppercase;
            background-color: #f8f9fa; /* Light grey header background */
        }
    
        /* 4. Global Sidebar Active Link (Optional) */
        /* If you want your sidebar active items to match your green theme too */
        .sidebar-dark .nav-sidebar .nav-item > .nav-link.active {
            background-color: #568204 !important;
        }
        
        /* Custom Navbar Styling */
        .navbar-main-custom {
            background-color: #568204 !important; /* Deep Blue Grey */
            border-right: 1px solid rgba(255,255,255,0.1);
        }
        
        /* Make the active links pop with a vibrant color */
        .navbar-main-custom .nav-item > .nav-link.active {
            background-color: #26a69a !important; /* Teal accent */
            color: #fff;
        }
        
        /* Darken the user profile section slightly for separation */
        .sidebar-user {
            background: rgba(0,0,0,0.15);
            margin-bottom: 5px;
        }
        
        /* Custom Sidebar Styling */
        .sidebar-main-custom {
            background-color: #6f9940 !important; /* Deep Blue Grey */
            border-right: 1px solid rgba(255,255,255,0.1);
        }
        
        /* Make the active links pop with a vibrant color */
        .sidebar-main-custom .nav-sidebar .nav-item > .nav-link.active {
            background-color: #7bb367 !important; /* Teal accent */
            color: #fff;
        }
        
        /* Darken the user profile section slightly for separation */
        .sidebar-user {
            background: #6f9940;
            margin-bottom: 5px; 
        }
        
        .btn {
            background-color: #6f9940 !important;
            color: #fff;
            border-color: #6f9940 !important;   
        }
    </style>
    @include('partials.inc_top')
    <script src="https://js.pusher.com/8.4.0/pusher.min.js"></script>
</head>

<body class="{{ in_array(Route::currentRouteName(), ['payments.invoice', 'marks.tabulation', 'marks.show', 'ttr.manage', 'ttr.show']) ? 'sidebar-xs' : '' }}">

@include('partials.top_menu')
<div class="page-content">
    @include('partials.menu')
    <div class="content-wrapper">
        @include('partials.header')

        <div class="content">
            {{--Error Alert Area--}}
            @if($errors->any())
                <div class="alert alert-danger border-0 alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                        @foreach($errors->all() as $er)
                            <span><i class="icon-arrow-right5"></i> {{ $er }}</span> <br>
                        @endforeach
                </div>
            @endif
            <div id="ajax-alert" style="display: none"></div>
            @yield('content')
        </div>
    </div>
</div>

@include('partials.inc_bottom')
@yield('scripts')
</body>
</html>
