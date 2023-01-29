<!DOCTYPE html>
<html prefix="og: https://ogp.me/ns#">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta property="og:url" content="{{ Request::url() }}" />
    <meta property="og:type" content="website" />
    <meta property="og:image" content="https://www.dodoapp.net/preview" />
    <meta property="og:description" content="Privacy-focused bill splitting" />
    @if ($group == null)
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
        @if ($group != null)
            window.onload = function() {
                //Deep link URL for users with app already installed on their device
                window.location = 'lenderapp://lenderapp/join/{{ $group->invitation }}';
            }
        @endif
    </script>
</head>

<body>
    @if ($group != null)
        <input type="text" style="position: absolute; left: -999px;" value="{{ $group->invitation }}" id="token">
    @endif
    <div class="flex-center position-ref full-height main">
        @include('components.github')
        <div class="content">
            @include('components.logo')
            @if ($group == null)
                <p class="uppercase">Invalid or expired invitation</p>
            @else
                <a class="joinBtn" id="joining" href="lenderapp://lenderapp/join/{{ $group->invitation }}"
                    target="_blank">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="38px">
                        <path d="M11,7L9.6,8.4l2.6,2.6H2v2h10.2l-2.6,2.6L11,17l5-5L11,7z M20,19h-8v2h8c1.1,0,2-0.9,2-2V5c0-1.1-0.9-2-2-2h-8v2h8V19z" />
                    </svg>
                    <div style="display: flex; flex-direction:column; justify-content: center;">
                        <span class="join_group">Join group</span>
                        <span class="group_name">{{ $group->name }}</span>
                    </div>
                </a>
                <div class="copy">
                    <span>Invite code: <i>{{ $group->invitation }}</i></span>
                    <a href="#" class="copyBtn" onclick="copyToken()">
                        <svg xmlns="http://www.w3.org/2000/svg" height="38px" viewBox="0 0 24 24" width="24px">
                            <path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z" />
                        </svg>
                        <span class="group_name">Copy</span>
                    </a>
                </div>
            @endif
        </div>
        <div class="flex-center position-ref full-height main">
            @include('components.links')
        </div>
    </div>
</body>

</html>
