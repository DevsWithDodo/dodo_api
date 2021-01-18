<html lang="en">

<head>
    {{--<link href="https://unpkg.com/tabulator-tables@4.9.1/dist/css/tabulator.min.css" rel="stylesheet">--}}
    <link href="https://unpkg.com/tabulator-tables@4.9.1/dist/css/materialize/tabulator_materialize.min.css"
        rel="stylesheet">
    <script type="text/javascript" src="https://unpkg.com/tabulator-tables@4.9.1/dist/js/tabulator.min.js"></script>
</head>

<body>
    <div style="width:70%;margin-left: auto;margin-right: auto;">
        <h2>Groups</h2>
        <div id="grouptable"></div>
        <h2>Users</h2>
        <div id="usertable"></div>
        <form method="POST" action="/admin/send_notification">
            @csrf
            <h2>Send notification</h2>
            to <input id="id" name="id" type="number" min="1" placeholder="id" style="width:40px" />
            / <input type="checkbox" name="everyone"> everyone
            <textarea id="message" name="message" placeholder="Message" style="width:100%;margin-top:10;"></textarea>
            <button type="submit" style="float: right;margin-top:10;">Send</button>
        </form>
    </div>
    <script>
        //groups
    var groupdata = [
    @foreach (\App\Group::all() as $group)
        @php
        $balance = 0;
        foreach($group->members as $member){
            $balance = bcadd($balance, $member->member_data->balance);
        }
        @endphp
        {
            id:{{ $group->id }},
            @if(config('app.debug'))name:"{{ $group->name}}",@endif
            created_at:"{{ $group->created_at->format('Y/m/d') }}",
            members:{{ $group->members->count() }},
            guests:{{ $group->guests->count()}},
            member_limit:{{ $group->member_limit ?? 20}},
            purchases:{{ $group->purchases->count() }},
            payments:{{ $group->payments->count() }},
            requests:{{ $group->requests->count() }},
            balance:{{ $balance }},
            currency:"{{ $group->currency }}"
        },
    @endforeach
    ];
    var grouptable = new Tabulator("#grouptable", {
        data:groupdata,
        autoColumns:true,
        autoColumnsDefinitions: function(definitions){
            definitions.forEach((column) => {
                if (["members", "guests", "purchases", "payments", "requests"].includes(column.field))
                    column.bottomCalc = "sum";
            });
            return definitions;
        },
        layout:"fitDataFill",
        pagination:"local",
        paginationSize:10,

    });
    //users
    var userdata = [
    @foreach (\App\User::all() as $user)
        {id:{{ $user->id }},
        name:"{{ $user->username }}",
        registered_at:"{{ $user->created_at->format('Y/m/d') }}",
        is_guest:{{ $user->is_guest ? 1 : 0 }},
        groups:{{ $user->groups->count() }},
        default_currency:"{{ $user->default_currency }}",
        language:"{{ $user->language }}",
        },
    @endforeach
    ];
    var usertable = new Tabulator("#usertable", {
        data:userdata,
        autoColumns:true,
        layout:"fitDataFill",
        pagination:"local",
        paginationSize:10,
    });
    </script>
</body>
