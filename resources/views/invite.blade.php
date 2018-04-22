@extends('layouts.auth')
@section('content')
    <div class="box-color">
        <h4 class="text-center">{{trans('auth.create_account')}}</h4>
        <br>
        {!! Form::open(array('url' => url('invite/'.$inviteUser->code), 'method' => 'post')) !!}

        <div class="form-group has-feedback {{ $errors->has('first_name') ? 'has-error' : '' }}">
            {!! Form::label(trans('auth.first_name')) !!} :
            <span class="help-block">{{ $errors->first('first_name', ':message') }}</span>
            {!! Form::text('first_name', null, array('class' => 'form-control', 'required'=>'required')) !!}
        </div>
        <div class="form-group has-feedback {{ $errors->has('last_name') ? 'has-error' : '' }}">
            {!! Form::label(trans('auth.last_name')) !!} :
            <span class="help-block">{{ $errors->first('last_name', ':message') }}</span>
            {!! Form::text('last_name', null, array('class' => 'form-control', 'required'=>'required')) !!}
        </div>
        <div class="form-group has-feedback">
            {!! Form::label(trans('auth.email')) !!} :
            {!! $inviteUser->email !!}
        </div>
        <div class="form-group has-feedback {{ $errors->has('password') ? 'has-error' : '' }}">
            {!! Form::label(trans('auth.password')) !!} :
            <span class="help-block">{{ $errors->first('password', ':message') }}</span>
            {!! Form::password('password', array('class' => 'form-control', 'required'=>'required')) !!}
        </div>
        <div class="form-group has-feedback {{ $errors->has('password_confirmation') ? 'has-error' : '' }}">
            {!! Form::label(trans('auth.password_confirmation')) !!} :
            <span class="help-block">{{ $errors->first('password_confirmation', ':message') }}</span>
            {!! Form::password('password_confirmation', array('class' => 'form-control', 'required'=>'required')) !!}
        </div>
        <div class="form-group has-feedback {{ $errors->has('phone_number') ? 'has-error' : '' }}">
            {!! Form::label(trans('staff.phone_number')) !!} :
            <span class="help-block">{{ $errors->first('phone_number', ':message') }}</span>
            {!! Form::text('phone_number', null, array('class' => 'form-control', 'required'=>'required')) !!}
        </div>
        <button type="submit" class="btn btn-primary btn-block">{{trans('auth.register')}}</button>
        {!! Form::close() !!}
    </div>

    <h5 class="text-center text-default"><a href="{{url('signin')}}" class="text-primary _600">{{trans('auth.login')}}?</a>
    </h5>
@stop