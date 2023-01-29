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
    <link rel="shortcut icon" href="/dodo_szines.png" type="image/png" />

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
    <div class="flex-center position-ref main">
        <div class="top-right">
            <a href="https://github.com/machiato32/csocsort_app" target="_blank">
                <img src="/GitHub_Logo.png" alt="Available on GitHub" height="25px" style="filter: contrast(0%) brightness(100%)">
            </a>
        </div>
        <div class="content">
            <div style="height: 200px">
                <img src="/dodo_szines.png" style="height: 200px">
            </div>
            <div style="display: flex; flex-direction: column">
                <span class="title">Dodo</span>
                @if($group == null)
                <p class="large uppercase subtitle">Invalid or expired invitation</p>
                @else
                <span class="large uppercase subtitle">Privacy-focused bill splitting</span>            
                @endif
            </div>
        </div>
        @if($group != null)
            <a class="joinBtn" id="joining" href="lenderapp://lenderapp/join/{{ $group->invitation }}" target="_blank">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="38px"><path d="M11,7L9.6,8.4l2.6,2.6H2v2h10.2l-2.6,2.6L11,17l5-5L11,7z M20,19h-8v2h8c1.1,0,2-0.9,2-2V5c0-1.1-0.9-2-2-2h-8v2h8V19z"/></svg>
                <div style="display: flex; flex-direction:column; justify-content: center;">
                    <span class="join_group">Join group</span>
                    <span class="group_name">{{ $group->name }}</span>
                </div>
            </a>
        @endif
        @if($group != null)
            <div class="copy">
                <span>Invite code: <i>{{ $group->invitation }}</i></span>
                <a href="#" class="copyBtn" onclick="copyToken()">
                    <svg xmlns="http://www.w3.org/2000/svg" height="38px" viewBox="0 0 24 24" width="24px"><path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg>
                    <span class="group_name">Copy</span>
                </a>
            </div>
        @endif
        <div style="display: flex; flex-direction: column">
            <div style="display: flex; justify-content: center; flex-wrap: wrap; margin-bottom: 30px">
                <a class="joinBtn" href="https://play.google.com/store/apps/details?id=csocsort.hu.machiato32.csocsort_szamla&pcampaignid=pcampaignidMKT-Other-global-all-co-prtnr-py-PartBadge-Mar2515-1">
                    <img src="/google_play_logo.png" style="height: 31px">
                    <div style="display: flex; flex-direction:column; justify-content: center;">
                        <span class="join_group">Get it on</span>
                        <span class="group_name">Google Play</span>
                    </div>
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
                    <img src="/dodo_szines.png" height="35px;">
                    <div style="display: flex; flex-direction:column; justify-content: center;">
                        <span class="join_group">Use it</span>
                        <span class="group_name">Online</span>
                    </div>
                </a>
                <a class="joinBtn" href="ms-windows-store://pdp/?productid=9NVB4CZJDSQ7">
                    <img src="/microsoft_logo.png" height="31px">
                    <div style="display: flex; flex-direction:column; justify-content: center;">
                        <span class="join_group">Get it from</span>
                        <span class="group_name">Microsoft</span>
                    </div>
                </a>
            </div>
        </div>
    </div>
</body>

</html>
