<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta property="og:url" content="{{ Request::url() }}" />
    <meta property="og:type" content="website" />
    <meta property="og:image" content="https://www.lenderapp.net/landscape_preview" />
    <meta property="og:image:width" content="160" />
    <meta property="og:image:height" content="63" />
    <meta property="og:title" content="{{ config('app.name') }}" />
    <meta property="og:description" content="Money and debt management app designed for groups" />

    <title>{{ config('app.name') }}</title>
    <link rel="shortcut icon" href="/logo_color.png" type="image/png" />

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;300;400&display=swap" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('style.css') }}" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{ asset('cookie-consent.css') }}" async />
    <script src="{{ asset('cookie-consent.js') }}" async></script>

</head>

<body>
    <div class="flex-center position-ref full-height">
        <div class="top-right">
            <a href="https://github.com/machiato32/csocsort_app" target="_blank">
                <img src="/GitHub_Logo.png" alt="Available on GitHub" height="25px">
            </a>
        </div>
        <div class="content">
            <img src="logo_color.png" height="200px">
            <h1><span class="title">Lender</span></h1>
            <h2>
                <p class="large uppercase">Money and debt management app designed for groups</p>
            </h2>
        </div>
        <div class="bottom">
            <a
                href='https://play.google.com/store/apps/details?id=csocsort.hu.machiato32.csocsort_szamla&pcampaignid=pcampaignidMKT-Other-global-all-co-prtnr-py-PartBadge-Mar2515-1'>
                <img alt='Get it on Google Play'
                    src='https://play.google.com/intl/en_us/badges/static/images/badges/en_badge_web_generic.png'
                    height="80px" />
            </a>
            <a class="joinBtn" style="cursor:not-allowed">
                <table>
                    <tr>
                        <td>
                            <img src="/apple_logo.png" height="38px">
                        </td>
                        <td>
                            <span class="join_group">Coming Soon to the</span><br>
                            <span class="group_name">App Store</span><br>
                        </td>
                    </tr>
                </table>
            </a>
        </div>
    </div>
</body>

</html>
