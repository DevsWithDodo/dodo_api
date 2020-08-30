<!DOCTYPE html>
<html prefix="og: https://ogp.me/ns#">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta property="og:url" content="{{ Request::url() }}" />
        <meta property="og:type" content="website" />
        <meta property="og:image" content="http://www.lenderapp.net/landscape_preview" />
        @if($invitation == null)
        <meta property="og:title" content="Lender" />
        <meta property="og:description" content="Money and debt management app designed for groups. Invalid invitation token." />
        @else
        <meta property="og:title" content="Lender - Join group {{ $invitation->group->name }}!" />
        <meta property="og:description" content="Money and debt management app designed for groups. Invitation token: {{ $invitation->token }}" />
        @endif


        <title>Lender</title>

         <!-- Fonts -->
         <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;300;400&display=swap" rel="stylesheet">

         <!-- Styles -->
         <link href="{{ asset('style.css') }}" rel="stylesheet">

        <script>
        function copyToken() {
            var token = document.getElementById("token");
            token.focus();
            token.select(); 
            token.setSelectionRange(0, token.value.length); /*For mobile devices*/
            document.execCommand("copy");
        }
        @if($invitation != null)
        window.onload = function() {
            //Deep link URL for users with app already installed on their device
            window.location = 'lenderapp://lenderapp/join/{{ $invitation->token }}';
        }
        @endif
        
        </script>
    </head>
    <body>
        @if($invitation != null)
        <input type="text" style="position: absolute; left: -999px;" value="{{ $invitation->token }}" id="token">
        @endif
        <div class="flex-center position-ref full-height">
            <div class="top-right">
                <a href="https://github.com/kdmnk/csocsort_api" target="_blank">
                    <img src="/GitHub_Logo.png" alt="GitHub logo" height="25px">
                </a>
            </div>           
            <div class="content">
                <img src="/logo_color.png" alt="Lender logo" height="200px"> 
                <div class="title">Lender</div>
                @if($invitation == null)
                <p class="large uppercase">Invalid or expired invitation</p>
                @else
                <span class="uppercase" style="font-size: 11px;font-weight:400">Money and debt management app designed for groups</span>
                @endif
            </div>
            <div class="bottom">
                @if($invitation != null)
                <a class="joinBtn" id="joining" href="lenderapp://lenderapp/join/{{ $invitation->token }}" target="_blank">
                    <table>
                        <tr>
                            <td>
                                <img src="/login_icon.png" alt="login icon" height="38px">
                            </td>
                            <td>
                                <span class="join_group">Join group</span><br>
                                <span class="group_name">{{ $invitation->group->name }}</span><br>
                            </td>
                        </tr>
                    </table>
                </a>
                @endif
                <a href='https://play.google.com/store/apps/details?id=csocsort.hu.machiato32.csocsort_szamla&pcampaignid=pcampaignidMKT-Other-global-all-co-prtnr-py-PartBadge-Mar2515-1'>
                    <img alt='Get it on Google Play' src='https://play.google.com/intl/en_us/badges/static/images/badges/en_badge_web_generic.png' height="80px"/>
                </a>
                @if($invitation != null)
                <br>
                <span class="uppercase">Invitation:</span> <i>{{ $invitation->token }}</i>
                <a href="#" class="copyBtn" onclick="copyToken()">Copy</a>
                @endif
            </div>
        </div>
    </body>
</html>