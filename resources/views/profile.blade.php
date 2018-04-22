@extends('layouts.user')
@section('content')
    <div class="row">
        <div class="col-sm-4 col-md-2">
            <a class="thumbnail">
            @if(isset($user_data->user_avatar))
                <img src="{{ url('uploads/avatar/'.$user_data->user_avatar) }}" alt="User Image" class="img-rounded">
            @else
                <img src="{{ url('uploads/avatar/user.png') }}" alt="User Image" class="img-rounded">
            @endif
            </a>
        </div>
        <div class="col-sm-7 col-md-9 col-sm-offset-1">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <tbody>
                    <tr>
                        <td><b>{{trans('profile.first_name')}}</b></td>
                        <td><a href="#"> {{$user_data->first_name}}</a></td>
                    </tr>
                    <tr>
                        <td><b>{{trans('profile.last_name')}}</b></td>
                        <td><a href="#"> {{$user_data->last_name}}</a></td>
                    </tr>
                    <tr>
                        <td><b>{{trans('profile.email')}}</b></td>
                        <td><a href="#">{{$user_data->email}}</a></td>
                    </tr>
                    <tr>
                        <td><b>{{trans('profile.phone_number')}}</b></td>
                        <td><a href="#"> {{$user_data->phone_number}}</a></td>
                    </tr>
                    </tbody>
                </table>
                <a href="{{url('account')}}" class="btn btn-success change-prof">
                    <i class="fa fa-pencil-square-o"></i> {{trans('profile.change_profile')}}</a>
            </div>
        </div>
    </div>

@stop