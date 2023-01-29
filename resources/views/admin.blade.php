

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
    <div class="content">
        @if(!$hasValidSignature)
        <a href="{{route('admin.send-access-mail')}}"><button type="button" style="margin-top: 20px">Send access emails</button></a>
        @else
        <h1>Groups</h1>
        <div class="admin-card">
            With one member only: {{$one_member_only}}
            (not included in the statistics below)<br>
            All: {{$all_groups}}<br>
            Boosted: {{$boosted}}<br>
            Active in the last 30/60/90 days: {{\App\Group::activeGroupQuery()->count()}}/{{\App\Group::activeGroupQuery(60)->count()}}/{{\App\Group::activeGroupQuery(90)->count()}}<br>
            Avarage number of members (including guests): {{$boosted_members_avg}} (boosted),
            {{$not_boosted_members_avg}} (not boosted)<br>
            Avarage number of guests: {{$guests_all_avg}} ({{$guests_avg}} among 
            {{$groups_use_guests}} groups which use them)<br>
            Avarage number of purchases: {{$purchases_avg}}<br>
            Avarage number of payments: {{$payments_avg}}<br>
            Avarage number of requests: {{$requests_all_avg}} ({{$requests_avg}} among 
            {{$groups_use_requests}} groups which use them)<br>
            Currencies:
            @foreach($currencies as $currency => $count)
            {{$currency}} ({{round($count / $all_groups * 100)}}% / {{$count}})@if (!$loop->last), @endif
            @endforeach
            <br><br>
            @if(!config('app.debug'))
            Groups with broken balance:
            @endif
            <div id="grouptable"></div>
        </div>
        <h1>Users</h1>
        <div class="admin-card">
            <span>Number of users without groups: {{$zero_group}} (not included in the statistics below)</span>
            <span>All: {{$all_users}}</span>
            <span>Guests: {{$guests}}</span>

            <span> in the last day/week/month/year/ever: 
            {{$activeLastDay}}/{{$activeLast7}}/{{$activeLast30}}/{{$activeLast365}}/{{$activeEver}}</span>

            <span>Number of groups per user:
            @foreach($group_count as $group => $count)
            {{$group}} ({{round($count)}} users),
            @endforeach
            </span>
            <span>Average number of groups per user: {{$group_avg}}</span>
            <span>Languages:
            @foreach($languages as $language => $count)
            {{$language}} ({{round($count / $all_users * 100)}}% / {{$count}})@if (!$loop->last), @endif
            @endforeach
            </span>
            <br>
            <span>These stats only include users who have ever been active.</span>
            <span>Light theme vs. dark theme: {{$lightTheme}} / {{$darkTheme}} ({{round($lightTheme / $activeEver * 100)}}% / {{round($darkTheme / $activeEver * 100)}}%)</span>
            <span>Colors used by users which have gradients enabled ({{$users_gradient}}):
            @foreach($colors_gradients_enabled as $color => $count)
            {{$color}} ({{round($count / $users_gradient * 100)}}% / {{$count}})@if (!$loop->last), @endif
            @endforeach
            </span>
            <span>Colors used by free users ({{$users_no_gradient}}):
            @foreach($colors_free as $color => $count)
            @if(!str_contains($color, 'Gradient'))
                {{$color}} ({{round($count / $users_no_gradient * 100)}}% / {{$count}})@if (!$loop->last), @endif
            @endif
            @endforeach
            </span>
            <span>The difference between the free users and the sum of the themes comes from users whose last used theme still was in the free
            trial stage.</span>
        </div>
        @endif
    </div>

    @if($hasValidSignature)
    <script>
    //groups
    var groupdata = [
    @php
    $i = 0;
    @endphp
    @foreach (\App\Group::has('members', '>', '1')->get() as $group)
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
            @endif
            created_at:"{{ $group->created_at->format('Y/m/d') }}",
            members:{{ $group->members->count() }},
            guests:{{ $group->guests->count()}},
            boosted:{{ $group->boosted}},
            purchases:{{ $group->purchases->count() }},
            payments:{{ $group->payments->count() }},
            requests:{{ $group->requests->count() }},
            currency:"{{ $group->currency }}",
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
    </script>
    @endif
</body>
