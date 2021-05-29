<!DOCTYPE html>
<html prefix="og: https://ogp.me/ns#">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta property="og:url" content="{{ Request::url() }}" />
    <meta property="og:type" content="website" />
    <meta property="og:image" content="https://www.lenderapp.net/landscape_preview" />
    <meta property="og:description" content="Money and debt management app designed for groups." />
    @if($group == null)
    <meta property="og:title" content="{{ config('app.name') }}" />
    @else
    <meta property="og:title" content="{{ config('app.name') }} - Join group {{ $group->name }}!" />
    @endif


    <title>{{ config('app.name') }}</title>
    <link rel="shortcut icon" href="/logo_color.png" type="image/png" />

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;300;400&display=swap" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('style.css') }}" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{ asset('cookie-consent.css') }}" async />
    <script src="{{ asset('cookie-consent.js') }}" async></script>

    <script>
        function copyToken() {
            var token = document.getElementById("token");
            token.focus();
            token.select();
            token.setSelectionRange(0, token.value.length); /*For mobile devices*/
            document.execCommand("copy");
        }
        @if($group != null)
        window.onload = function() {
            //Deep link URL for users with app already installed on their device
            window.location = 'lenderapp://lenderapp/join/{{ $group->invitation }}';
        }
        @endif

    </script>
</head>

<body>
    @if($group != null)
    <input type="text" style="position: absolute; left: -999px;" value="{{ $group->invitation }}" id="token">
    @endif
    <div class="flex-center position-ref full-height">
        <div class="top-right">
            <a href="https://github.com/machiato32/csocsort_app" target="_blank">
                <img src="/GitHub_Logo.png" alt="GitHub logo" height="25px">
            </a>
        </div>
        <div class="content">
            <img src="/logo_color.png" alt="{{ config('app.name') }} logo" height="200px">
            <div class="title">{{ config('app.name') }}</div>
            @if($group == null)
            <p class="large uppercase">Invalid or expired invitation</p>
            @else
            <span class="uppercase" style="font-size: 11px;font-weight:400">Money and debt management app designed for
                groups</span><br><br>
            <a class="joinBtn" id="joining" href="lenderapp://lenderapp/join/{{ $group->invitation }}" target="_blank">
                <table>
                    <tr>
                        <td>
                            <img src="/login_icon.png" alt="login icon" height="38px">
                        </td>
                        <td>
                            <span class="join_group">Join group</span><br>
                            <span class="group_name">{{ $group->name }}</span><br>
                        </td>
                    </tr>
                </table>
            </a>
            <br>
            @endif
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
            @if($group != null)
            <br>
            <span class="uppercase">Invitation:</span> <i>{{ $group->invitation }}</i>
            <a href="#" class="copyBtn" onclick="copyToken()">Copy</a>
            @endif
        </div>
    </div>
</body>

</html>
