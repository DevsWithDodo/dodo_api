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
         <style>
             @font-face {
                font-family: 'Product Sans';
                font-style: normal;
                font-weight: 400;
                src: local('Open Sans'), local('OpenSans'), url(https://fonts.gstatic.com/s/productsans/v5/HYvgU2fE2nRJvZ5JFAumwegdm0LZdjqr5-oayXSOefg.woff2) format('woff2');
            }

             html, body {
                 color: white;
                 font-family: 'Roboto', sans-serif;
                 font-weight: 200;
                 height: 90vh;
                 margin: 0;
                 background-image: url('/lender_landscape.png');
                 background-repeat: no-repeat;
                 background-attachment: fixed;
                 background-size: cover;
                 background-position: center;
             }
 
             .full-height {
                 height: 90vh;
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

             .bottom {
                 position: absolute;
                 bottom: 0px;
                 text-align: center;
             }
 
             .content {
                 text-align: center;
             }
 
             .title {
                 font-size: 84px;
                /*  margin-bottom: 30px; */
                 text-transform: uppercase;
                 font-weight: 200;
             }
 
             p {
                 padding: 0 25px;
                 font-size: 13px;
                 font-weight: 300;
                 letter-spacing: .1rem;
                 text-decoration: none;
                 /* text-transform: uppercase; */
             }
             
             a {
                 color: white;
             }

            .large {
                font-size: 22px;
            }

            .uppercase {
                text-transform: uppercase;
            }

            .copyBtn {
                background-color:transparent;
                border-radius:6px;
                color:white;
                border:1px solid white;
                display:inline-block;
                cursor:pointer;
                padding:5px;
                margin:5px;
                text-decoration:none;
            }

            .joinBtn {
                background-color:black;
                border-radius:6px;
                color:white;
                border:1px solid #a6a6a6;
                /* border:1px solid #70C5EB; */
                display:inline-block;
                cursor:pointer;
                padding:2px;
                margin:5px;
                text-decoration:none;
                position:relative;
                bottom:8px;
            }
            td {
                vertical-align: bottom;
                text-align: left;
                line-height: 1;
                font-family: Product Sans, 'Roboto';
                padding-right: 5px;
            }
            .join_group {
                font-size:11px;
                font-weight:400;
                text-transform: uppercase;
                position:relative;
                bottom:5px;
            }
            .group_name {
                font-weight:550;
                font-size:21px;
                position:relative;
                bottom:5px;
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