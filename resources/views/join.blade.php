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
        <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">

        <!-- Styles -->
        <style>
            html, body {
                background-color: #fff;
                color: #636b6f;
                font-family: 'Nunito', sans-serif;
                font-weight: 200;
                height: 100vh;
                margin: 0;
            }

            .full-height {
                height: 100vh;
            }

            .flex-center {
                align-items: center;
                display: flex;
                justify-content: center;
            }

            .position-ref {
                position: relative;
            }

            .top-right {
                position: absolute;
                right: 10px;
                top: 18px;
            }

            .content {
                text-align: center;
            }

            .title {
                font-size: 70px;
                margin-bottom: 30px;
            }

            p {
                color: #636b6f;
                padding: 0 25px;
                font-size: 13px;
                font-weight: 600;
                letter-spacing: .1rem;
                text-decoration: none;
            }
            .large {
                font-size: 18px;
            }
            .uppercase {
                text-transform: uppercase;
            }
        </style>

        <script>
        function copyToken() {
            var token = document.getElementById("token");
            token.focus();
            token.select(); 
            token.setSelectionRange(0, token.value.length); /*For mobile devices*/
            document.execCommand("copy");
        }
        </script>
    </head>
    <body>
        <div class="flex-center position-ref full-height">            
            <div class="content">
                <img src="/logo_color.png" alt="Lender logo" height="200px"> 
                <div class="title">Lender</div>
                @if($invitation == null)
                <p class="large">Invalid invitation token</p>
                @else
                <!-- <p><img src="https://miro.medium.com/max/441/1*9EBHIOzhE1XfMYoKz1JcsQ.gif" alt="loading animation" height="100px"></p>  -->
                <p class="large" id="joining">Joining group <span class="uppercase">{{ $invitation->group->name }}</span></p>
                
                Invitation token (if needed): <br>
                <input type="text" style="position: absolute; left: -999px;" value="{{ $invitation->token }}" id="token">
                <i> {{ $invitation->token }}</i> <button onclick="copyToken()">Copy</button>
                @endif
                <div style="margin:100px"></div>
                <p class="uppercase">Money and debt management app designed for groups</p>
                <a href='https://play.google.com/store/apps/details?id=csocsort.hu.machiato32.csocsort_szamla&pcampaignid=pcampaignidMKT-Other-global-all-co-prtnr-py-PartBadge-Mar2515-1'>
                    <img alt='Get it on Google Play' src='https://play.google.com/intl/en_us/badges/static/images/badges/en_badge_web_generic.png' height="80px"/>
                </a>
            </div>
        </div>
    </body>
</html>
