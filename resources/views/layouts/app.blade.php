<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script src="https://js.stripe.com/v3/"></script>

    <title>{{ config('app.name', 'Laravel') }}</title>

    <script src="{{ asset('js/tablesort.min.js') }}"></script>
 
    <!-- Include sort types you need -->
    <script src="{{ asset('js/tablesort.number.min.js') }}"></script>
    <script src="{{ asset('js/tablesort.date.min.js') }}"></script>

    <link rel="stylesheet" href="https://cdn.materialdesignicons.com/4.9.95/css/materialdesignicons.min.css">

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
    <div id="app">
        @yield( 'nav', View::make('layouts.nav') )
        <main>
            @yield('content')
        </main>
    </div>
</body>
</html>
