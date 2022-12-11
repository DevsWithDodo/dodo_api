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
            <a class="joinBtn" href="https://play.google.com/store/apps/details?id=csocsort.hu.machiato32.csocsort_szamla&pcampaignid=pcampaignidMKT-Other-global-all-co-prtnr-py-PartBadge-Mar2515-1">
                <table>
                    <tr>
                        <td>
                            <img src="/google_play_logo.png" height="31px" style="padding: 4px">
                        </td>
                        <td>
                            <span class="join_group">GET IT ON</span><br>
                            <span class="group_name">Google Play</span><br>
                        </td>
                    </tr>
                </table>
            </a>
            <!--<a class="joinBtn" href="https://apps.apple.com/us/app/lender-finances-for-groups/id1558223634">
                <table>
                    <tr>
                        <td>
                            <img src="/apple_logo.png" height="38px">
                        </td>
                        <td>
                            <span class="join_group">Download on the</span><br>
                            <span class="group_name">App Store</span><br>
                        </td>
                    </tr>
                </table>
            </a>-->
	    <a class="joinBtn" href="https://app.lenderapp.net">
                <table>
                    <tr>
                        <td>
                            <img src="/logo_color.png" height="38px">
                        </td>
                        <td>
                            <span class="join_group">Use it</span><br>
                            <span class="group_name">Online</span><br>
                        </td>
                    </tr>
                </table>
            </a>
	    <a class="joinBtn" href="https://apps.microsoft.com/store/detail/lender-finances-for-groups/9NVB4CZJDSQ7?hl=en-us&gl=us">
                <table>
                    <tr>
                        <td>
                            <img src="/microsoft_logo.png" height="38px">
                        </td>
                        <td>
                            <span class="join_group">Get it from</span><br>
                            <span class="group_name">Microsoft</span><br>
                        </td>
                    </tr>
                </table>
            </a>
        </div>
    </div>
</body>

</html>
