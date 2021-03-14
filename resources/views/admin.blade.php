@php
    $not_boosted_members = $boosted_members = 0;
    $not_boosted_guests = $boosted_guests = 0;
    $boosted = $not_boosted = $all_groups = 0;
    $payments = $purchases = 0;
    $groups_use_requests = $requests_all = $requests_use = 0;
    $groups_use_guests = $guests_all = $guests_use = 0;
    $one_member_only = \App\Group::has('members', '=', 1)->count();
    $groups = \App\Group::has('members', '>=', 2)->get();
    $currencies = [];
    foreach ($groups as $group) {
        if ($group->boosted) {
            $boosted_members += $group->members()->count();
            $boosted++;
        } else {
            $not_boosted_members += $group->members()->count();
            $not_boosted++;
        }
        $all_groups++;
        $payments += $group->payments()->count();
        $purchases += $group->purchases()->count();
        $requests = $group->requests()->count();
        $requests_all += $requests;
        if ($requests) {
            $requests_use += $requests;
            $groups_use_requests++;
        }
        $guests = $group->guests()->count();
        $guests_all += $guests;
        if ($guests) {
            $guests_use += $guests;
            $groups_use_guests++;
        }
        if (isset($currencies[$group->currency])) {
            $currencies[$group->currency]++;
        } else {
            $currencies[$group->currency] = 1;
        }
    }
    asort($currencies);

    $zero_group = \App\User::has('groups', '=', 0)->where('password', '<>', null)->count();
    $users = \App\User::has('groups', '>=', 1)->where('password', '<>', null)->with('groups')->get();
    $all_users = $users->count();
    $guests = \App\User::where('password', null)->count();
    $groups = 0;
    $languages = [];
    $group_count = [];
    $colors_gradients_enabled = [];
    $colors_free = [];
    foreach ($users as $user) {
        $groups += $user->groups()->count();

        if (isset($group_count[$user->groups()->count()])) {
            $group_count[$user->groups()->count()]++;
        } else {
            $group_count[$user->groups()->count()] = 1;
        }

        if ($user->gradients_enabled) {
            if (isset($colors_gradients_enabled[$user->color_theme])) {
                $colors_gradients_enabled[$user->color_theme]++;
            } else {
                $colors_gradients_enabled[$user->color_theme] = 1;
            }
        } else {
            if (isset($colors_free[$user->color_theme])) {
                $colors_free[$user->color_theme]++;
            } else {
                $colors_free[$user->color_theme] = 1;
            }
        }

        if (isset($languages[$user->language])) {
            $languages[$user->language]++;
        } else {
            $languages[$user->language] = 1;
        }
    }
    asort($languages);
    arsort($group_count);

    //groups
    $boosted_members_avg = round($boosted_members / ($boosted ? $boosted : 1), 2);
    $not_boosted_members_avg = round($not_boosted_members / ($not_boosted ? $not_boosted : 1), 2);
    $boosted_guests_avg = round($boosted_guests / ($boosted ? $boosted : 1), 2);
    $not_boosted_guests_avg = round($not_boosted_guests / ($not_boosted ? $not_boosted : 1), 2);
    $payments_avg = round($payments / ($all_groups ? $all_groups : 1), 2);
    $purchases_avg = round($purchases / ($all_groups ? $all_groups : 1), 2);
    $requests_all_avg = round($requests_all / ($all_groups ? $all_groups : 1), 2);
    $groups_use_requests = $groups_use_requests;
    $requests_avg = round($requests_use / ($groups_use_requests ? $groups_use_requests : 1), 2);
    $guests_all_avg = round($guests_all / ($all_groups ? $all_groups : 1), 2);
    $guests_avg = round($guests_use / ($groups_use_guests ? $groups_use_guests : 1), 2);
    $groups_use_guests = $groups_use_guests;
    $currencies = $currencies;
    //users
    $zero_group = $zero_group;
    $group_count = $group_count;
    $all_users = $all_users;
    $guests = $guests;
    $group_avg = round($groups / ($all_users ? $all_users : 1), 2);
    $languages = $languages;
    $colors_gradients_enabled = $colors_gradients_enabled;
    $colors_free = $colors_free;
@endphp

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
        </div>


        <script>
        //groups
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
        </script>
    </div>
</body>
