<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">

    <!-- Styles -->
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    <img style="max-height: 24px" src="{{ asset('/assets/img/raf.png') }}" />
                </a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    @include('layouts.partials.topnav')
                    @include('layouts.partials.topnav-right')
                </div>
            </div>
        </nav>
        @yield('layout.breadcrumbs')
        <main v-cloak class="mt-2">
            @yield('content')
        </main>
        <auto-toast></auto-toast>
    </div>
    @include('layouts.partials.vue-alerts')
    @auth()
    <script type="text/javascript">window.__state = {!! json_encode(array_merge(
        ['sockets' => [
            'broadcaster' => 'pusher',
            'key' => config('broadcasting.connections.pusher.key'),
            'wsHost' => config('broadcasting.connections.pusher.options.host'),
            'wsPort' => config('broadcasting.connections.pusher.options.port'),
            'disableStats' => true,
            'forceTLS' => false,
            'auth' => [
                'headers' => [
                    'Authorization' => 'Bearer ' . \Auth::user()->api_token,
                    'ContentType' => 'application/json'
                ]
            ]
        ]],
        \Auth::user()->only(['api_token'])
    )) !!};</script>
    @endauth
    <script type="text/javascript" src="{{ mix('/js/manifest.js') }}"></script>
    <script type="text/javascript" src="{{ mix('/js/vendor.js') }}"></script>
    <script type="text/javascript" src="{{ mix('/js/app.js') }}"></script>
    @stack('layout.footer.scripts')
</body>
</html>
