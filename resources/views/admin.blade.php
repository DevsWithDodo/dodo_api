<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <title>Lender - Statistics</title>
    <link rel="shortcut icon" href="/logo_color.png" type="image/png" />

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;300;400&display=swap" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('style.css') }}" rel="stylesheet">
    <link href="https://unpkg.com/tabulator-tables@4.9.1/dist/css/materialize/tabulator_materialize.min.css"
        rel="stylesheet">
    <script type="text/javascript" src="https://unpkg.com/tabulator-tables@4.9.1/dist/js/tabulator.min.js"></script>
</head>

<body>
    <div class="flex-center position-ref">
        <div class="content">
            <h1>Groups</h1>
            <div
                style="background-color: white; color:black; padding:40px;border-radius: 10px;opacity: 0.9;text-align:left; max-width:80%;margin:0 auto;font-size:15px;font-weight:400;text-align:justify">
                With one member only: {{$one_member_only}}
                (do not count in the statistics below)<br>
                All: {{$all_groups}}<br>
                Boosted: {{$boosted}}<br>
                Avarage member number: {{$boosted_members_avg}} (boosted), {{$not_boosted_members_avg}} (not
                boosted) (guests included)<br>
                Avarage guests: {{$guests_all_avg}} ({{$guests_avg}} among {{$groups_use_guests}} groups which
                use them)<br>
                Avarage purchases: {{$purchases_avg}}<br>
                Avarage payments: {{$payments_avg}}<br>
                Avarage requests: {{$requests_all_avg}} ({{$requests_avg}} among {{$groups_use_requests}} groups which
                use them)<br>
                Currencies:
                @foreach($currencies as $currency => $count)
                {{$currency}} ({{round($count / $all_groups * 100)}}%) @if (!$loop->last), @endif
                @endforeach
                <br><br>
                @if(!config('app.debug'))
                Groups with broken balance:
                @endif
                <div id="grouptable"></div>
            </div>
            <h1>Users</h1>
            <div
                style="background-color: white; color:black; padding:40px;border-radius: 10px;opacity: 0.9;text-align:left; max-width:80%;margin:0 auto;font-size:15px;font-weight:400;text-align:justify">
                Without groups: {{$zero_group}} (do not count in the statistics below)<br>
                All: {{$all_users}}<br>
                Guests: {{$guests}}<br>

                Group numbers:
                @foreach($group_count as $group => $count)
                {{$group}} ({{round($count)}}),
                @endforeach
                avarage group number: {{$group_avg}}<br>
                Languages:
                @foreach($languages as $language => $count)
                {{$language}} ({{round($count / $all_users * 100)}}%) @if (!$loop->last), @endif
                @endforeach
                <br>Colors used by users which have gradients enabled:
                @foreach($colors_gradients_enabled as $color => $count)
                {{$color}} ({{round($count / $all_users * 100)}}%) @if (!$loop->last), @endif
                @endforeach
                <br>Colors used by free users:
                @foreach($colors_free as $color => $count)
                {{$color}} ({{round($count / $all_users * 100)}}%) @if (!$loop->last), @endif
                @endforeach
            </div>
            <form method="POST" action="/admin/send_notification">
                @csrf
                <h2>Send notification</h2>
                <div
                    style="background-color: white; color:black; padding:40px;border-radius: 10px;opacity: 0.9;text-align:left; max-width:80%;margin:0 auto;font-size:15px;font-weight:400;">

                    to <input id="id" name="id" type="number" min="1" placeholder="id" style="width:40px" />
                    / <input type="checkbox" name="everyone"> everyone
                    <textarea id="message" name="message" placeholder="Message"
                        style="width:100%;margin-top:10;"></textarea>
                    <button type="submit" style="float: right;margin-top:10;">Send</button>
                </div>
            </form>
        </div>


        <script>
            //groups
        var recalculateGroupFormatter = function(cell, formatterParams, onRendered) {
            console.log(cell.getRow().getData()['id']);
            return `<form method='POST' action='/admin/recalculate'>@csrf
                <input type='hidden' name='group' value=` + cell.getRow().getData()['id'] + `>
                <input type='submit'></input>`;
        }
        var groupdata = [
        @php
        $i = 0;
        @endphp
        @foreach (\App\Group::all() as $group)
            @php
            $balance = 0;

            foreach($group->members as $member){
                $balance = bcadd($balance, $member->member_data->balance);
            }
            @endphp
            @if($balance != 0 || config('app.debug'))
            @php
            $i++;
            @endphp
            {
                id:{{ $group->id }},
                @if(config('app.debug'))
                name:"{{ $group->name}}",
                created_at:"{{ $group->created_at->format('Y/m/d') }}",
                members:{{ $group->members->count() }},
                guests:{{ $group->guests->count()}},
                boosted:{{ $group->boosted}},
                purchases:{{ $group->purchases->count() }},
                payments:{{ $group->payments->count() }},
                requests:{{ $group->requests->count() }},
                currency:"{{ $group->currency }}",
                @endif
                balance:{{ $balance }}
            },
            @endif
        @endforeach
        ];
        var grouptable = new Tabulator("#grouptable", {
            data:groupdata,
            autoColumns:true,
            layout:"fitDataFill",
            pagination:"local",
            paginationSize:10,

        });
        @if($i)
        grouptable.addColumn({title:"Recalculate balance",formatter:recalculateGroupFormatter});
        @endif
        </script>
    </div>
</body>
