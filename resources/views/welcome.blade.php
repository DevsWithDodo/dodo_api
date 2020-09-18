<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta property="og:url" content="{{ Request::url() }}" />
        <meta property="og:type" content="website" />
        <meta property="og:image" content="http://www.lenderapp.net/landscape_preview" />
        <meta property="og:image:width" content="160" />
        <meta property="og:image:height" content="63" />
        <meta property="og:title" content="{{ config('app.name') }}" />
        <meta property="og:description" content="Money and debt management app designed for groups" />

        <title>{{ config('app.name') }}</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;300;400&display=swap" rel="stylesheet">

        <!-- Styles -->
        <link href="{{ asset('style.css') }}" rel="stylesheet">

    </head>
    <body>
        <div class="flex-center position-ref full-height">
            <div class="top-right">
            <div class="links">
            <a href="https://github.com/kdmnk/csocsort_api">
                <img src="GitHub_Logo.png" alt="GitHub logo" height="25px">
            </a>
            </div>
            
            </div>
            <div class="content">
                <img src="logo_color.png" alt="Lender logo" height="200px"> 
                <div class="title">{{ config('app.name') }}</div>
                <p class="large uppercase">Money and debt management app designed for groups</p>
            </div>
            <div class="bottom">
                <a href='https://play.google.com/store/apps/details?id=csocsort.hu.machiato32.csocsort_szamla&pcampaignid=pcampaignidMKT-Other-global-all-co-prtnr-py-PartBadge-Mar2515-1'>
                    <img alt='Get it on Google Play' src='https://play.google.com/intl/en_us/badges/static/images/badges/en_badge_web_generic.png' height="80px"/>
                </a>
            </div>
        </div>
    </body>
</html>
