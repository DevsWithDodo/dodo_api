<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{$group->name}} summary</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    
    {{-- <link href="{{ asset('css/app.css') }}" rel="stylesheet" type="text/css" /> --}}
    <style>
        @media print { @page { size: auto; background-color: #EEEEEE } }
        
        body {
            font-family: Roboto, sans-serif;
        }
        table{
            width: 100%;
        }

    </style>
</head>

<body>
    <div>
        <div class="card" style="border-radius: 50px; padding: 30px; border: transparent">
            <h2 class="text-center mb-3" style="color: #66BB6A">{{$group->name}}</h2>
            <h4 class="text-center mb-3" style="color: #616161">Created: {{$group->created_at->format("Y-m-d")}}</h5>
            
            <h5 class="text-center mb-3" style="color: #66BB6A; margin-top: 30px">Purchases</h5>
            <table class="table" style="">
                <tbody>
                    @foreach($purchases as $data)
                    <tr>
                        <td style="color: #616161; font-weight: 500">{{ $data->name }}</td>
                        <td style="color: #616161; font-weight: 500">{{ \App\Group::nicknameOf($group->id, $data->buyer_id) }}</td>
                        
                        <td> 
                            <table class="table table-borderless">
                                <tr>
                                    <td style="padding: 0 0 20px 0; color: green; font-weight: bold">+ {{  $data->amount . ' ' . $group->currency}}</td>
                                </tr>
                            @foreach ($data->receivers as $receiver)
                                @php
                                    $nickname = \App\Group::nicknameOf($group->id, $receiver->receiver_id);
                                    $amount = $receiver->amount;
                                @endphp
                                <tr>
                                    <td style="padding-top: 0; color: #616161; font-weight: 500">{{ $nickname }}</td>
                                    <td style=" color:red; font-weight: bold; padding-top: 0; text-align: right">{{ '-' . round(floatval($amount),2) . ' ' . $group->currency}}</td>
                                </tr>
                            @endforeach
                            </table>
                        </td>
                        <td style="text-align: center; color: #616161; font-weight: 500">{{ $data->updated_at->format("Y-m-d H:i") }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <h5 class="text-center mb-3" style="color: #66BB6A; margin-top: 30px">Payments</h5>
            <table class="table">
                <tbody>
                    @foreach($payments as $data)
                    <tr>
                        <td style="color: #616161; font-weight: 500">{{ $data->note }}</td>
                        
                        <td> 
                            @php
                                    $nickname = \App\Group::nicknameOf($group->id, $data->taker_id);
                                    $amount = $data->amount;
                                @endphp
                            <table class="table table-borderless">
                                <tr>
                                    <td style="color: #616161; font-weight: 500; text-align: right">{{ \App\Group::nicknameOf($group->id, $data->payer_id) }}</td>
                                    <td style=" color: #616161; font-weight: 500; text-align: left">{{ $nickname }}</td>
                                </tr>
                                
                                <tr>
                                    <td style="color: green; font-weight: bold; text-align: right">+ {{  $data->amount . ' ' . $group->currency}}</td>
                                    <td style=" color:red; font-weight: bold; text-align: left">{{ '-' . round(floatval($amount),2) . ' ' . $group->currency}}</td>
                                </tr>
                            </table>
                        </td>
                        <td style="text-align: center; color: #616161; font-weight: 500">{{ $data->updated_at->format("Y-m-d H:i") }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    {{-- <script src="{{ asset('js/app.js') }}" type="text/js"></script> --}}
</body>

</html>