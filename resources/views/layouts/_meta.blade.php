<meta charset="UTF-8">
<title>
    {{$title or '登录'}} | {{ Settings::get('site_name') }}
</title>
<meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>

<meta id="token" name="token" value="{{ csrf_token() }}">
@if(Sentinel::check())
    <meta id="pusherKey" name="pusherKey" value="{{ Settings::get('pusher_key') }}">
    <meta id="userId" name="userId" value="{{ $user_data->id }}">
@endif