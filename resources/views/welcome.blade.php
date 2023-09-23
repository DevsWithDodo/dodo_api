<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta property="og:url" content="{{ Request::url() }}" />
    <meta property="og:type" content="website" />
    <meta property="og:image" content="https://www.dodoapp.net/preview" />
    <meta property="og:image:width" content="160" />
    <meta property="og:image:height" content="63" />
    <meta property="og:title" content="{{ config('app.name') }}" />
    <meta property="og:description" content="Privacy-focused bill splitting" />

    <title>{{ config('app.name') }}</title>
    <link rel="shortcut icon" href="/dodo_szines.png" type="image/png" />

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;300;400&display=swap" rel="stylesheet">

    <!-- Styles -->
    {{-- <link href="{{ asset('style.css') }}" rel="stylesheet"> --}}
    <link rel="stylesheet" type="text/css" href="{{ asset('cookie-consent.css') }}" async />
    <script src="{{ asset('cookie-consent.js') }}" async></script>
    @viteReactRefresh
    @vite("resources/js/index.tsx")
</head>

<body>
    <div id="app"></div>
</body>

</html>
