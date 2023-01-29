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
    <link href="{{ asset('style.css') }}" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{ asset('cookie-consent.css') }}" async />
    <script src="{{ asset('cookie-consent.js') }}" async></script>

</head>

<body>
    <div class="flex-center position-ref full-height main">
        <div class="top-right">
            <a href="https://github.com/orgs/DevsWithDodo/repositories" target="_blank">
                <img src="/GitHub_Logo.png" alt="Available on GitHub" height="25px" style="filter: contrast(0%) brightness(100%)">
            </a>
        </div>
        <div class="content">
            <div style="height: 200px">
                <img src="dodo_szines.png" style="height: 200px">
            </div>
            <div style="display: flex; flex-direction: column">
                <span class="title">Dodo</span>
                <span class="large uppercase subtitle">Privacy-focused bill splitting</span>
            </div>
        </div>
        <div style="display: flex; justify-content: center; flex-wrap: wrap">
            <a class="joinBtn" href="https://play.google.com/store/apps/details?id=csocsort.hu.machiato32.csocsort_szamla&pcampaignid=pcampaignidMKT-Other-global-all-co-prtnr-py-PartBadge-Mar2515-1">
                <img src="/google_play_logo.png" style="height: 31px">
                <div style="display: flex; flex-direction:column; justify-content: center;">
                    <span class="join_group">Get it on</span>
                    <span class="group_name">Google Play</span>
                </div>
            </a>
            <a class="joinBtn" href="https://apps.apple.com/us/app/lender-finances-for-groups/id1558223634">
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
            </a>
            <a class="joinBtn" href="ms-windows-store://pdp/?productid=9NVB4CZJDSQ7">
                <img src="/microsoft_logo.png" height="31px">
                <div style="display: flex; flex-direction:column; justify-content: center;">
                    <span class="join_group">Get it from</span>
                    <span class="group_name">Microsoft</span>
                </div>
            </a>
            <a class="joinBtn" href="https://app.dodoapp.net">
                <img src="/dodo_szines.png" height="35px;">
                <div style="display: flex; flex-direction:column; justify-content: center;">
                    <span class="join_group">Use it</span>
                    <span class="group_name">Online</span>
                </div>
            </a>
        </div>
    </div>
</body>

</html>
