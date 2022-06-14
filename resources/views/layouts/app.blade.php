<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Scripts -->
        <script src="{{ asset('js/jquery-3.5.1.slim.min.js')}}"></script>
        <script src="{{ asset('js/jquery-mask-plugin.js')}}"></script>
        <script src="{{ asset('js/popper.js') }}"></script>
        <script defer src="{{ asset('js/app.js') }}"></script>
        <script defer src="{{ asset('js/alpine.js') }}"></script>

        <!-- CKEditor -->
        <script src="{{asset('ckeditor/ckeditor.js')}}"></script>

        <!-- Fonts -->
        <link rel="dns-prefetch" href="//fonts.gstatic.com">
        <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

        <script src='{{asset('fullcalendar-5.3.2/lib/main.js')}}'></script>
        <script src='{{asset('fullcalendar-5.3.2/lib/locales-all.js')}}'></script>

        <!-- Styles -->
        <link href="{{ asset('css/app.css') }}" rel="stylesheet">
        <link href="{{ asset('css/style.css') }}" rel="stylesheet">
        <link href="{{ asset('css/styleIndex.css') }}" rel="stylesheet">
        <link href="{{ asset('css/dark-mode.css') }}" rel="stylesheet">
        <link href='{{asset('fullcalendar-5.3.2/lib/main.css')}}' rel='stylesheet' />
        <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css">
        <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    </head>
    <body>
        @include('navbar')

        @hasSection('sidebar')
            @yield('sidebar')
        @endif

        <main>
            @yield('content')
        </main>

        @hasSection('sidebar')
        @else
            @include('componentes.footer')
        @endif

        @hasSection('javascript')
            @yield('javascript')
        @endif
        <script defer src="{{ asset('js/dark-mode.js') }}"></script>
        <script src="{{ asset('js/submit.js') }}"></script>
    </body>
</html>
