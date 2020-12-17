<html>
<head>
    <title>Password Protected</title>
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

    <!-- Optional theme -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

    <!-- Latest compiled and minified JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
    <style>
        #password{
            margin: 0 auto;
            margin-left: auto;
            margin-right: auto;
            text-align: center;
            width: 50vw;
            height: 7vh;
            font-size: 3vh;
        }
        #form div{
            margin: 0 auto;
            margin-left: auto;
            margin-right: auto;
            margin-bottom: 10px;
        }
        #errors{
            margin: 0 auto;
            margin-left: auto;
            margin-right: auto;
            text-align: center;
            width: 50vw;
        }
    </style>
</head>
<body>
<div class="container text-center">

    <div class="jumbotron">
        <div class="container">
            <h2>To access "{{$protectedRoute}}" enter the password below </h2>
            <blockquote>
                <p><em>If you dont know the password, try asking the person in charge..</em></p>
            </blockquote>
        </div>
    </div>

    {{--Only the form below is needed you can get rid od the other html and make this fit your project--}}
    {{--Varibles Passed--}}
    {{--$protectedRoute--}}
    <form id="form" method="POST" action="/passwordprotect" >
        {{--/\this route handles the password validation--}}
        {{csrf_field()}}

        <div class="form-group">
            <input type="password" class="form-control" id="password" name="password">
        </div>

        {{--I suggest also using recaptha to prevent brute force attacks--}}
        @if(config('passwordprotect.use_greggilbert_recaptcha'))
            {!! Recaptcha::render() !!}
        @endif

        <button type="submit" class="btn btn-primary btn-lg">Submit</button>
    </form>

    @if (count($errors))
        <div id="errors" class="alert alert-danger">
            <ol>
                @foreach ($errors->all() as $error)
                    <p>{{$error}}</p>
                @endforeach
            </ol>
        </div>
    @endif
</div>
</body>
</html>
