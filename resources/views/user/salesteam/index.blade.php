@extends('layouts.user') {{-- Web site Title --}} @section('title') {{ $title }} @stop {{-- Content --}} @section('content')
<div class="page-header clearfix">
    @if($user_data->hasAccess(['sales_team.write']) || $user_data->inRole('admin'))
    <div class="pull-right">
        <a href="{{ url($type.'/create') }}" class="btn btn-primary">
            <i class="fa fa-plus-circle"></i> {{ trans('salesteam.create_salesteam') }}</a>
        <a href="{{ request()->url() }}/import"
                   class="btn btn-primary">
                    <i class="fa fa-download"></i> {{ trans('table.import') }}
                </a>
    </div>
    @endif
</div>
<div class="panel panel-default">
    <div class="panel-heading">
        <h4 class="panel-title">
                <i class="material-icons">groups</i>
                {{ $title }}
            </h4>
        <span class="pull-right">
                                    <i class="fa fa-fw fa-chevron-up clickable"></i>
                                    <i class="fa fa-fw fa-times removepanel clickable"></i>
                                </span>
    </div>
    <div class="panel-body">
        <div class="table-responsive">
            <table id="data" class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>{{ trans('salesteam.salesteam') }}</th>
                        <th>{{ trans('salesteam.invoice_target') }}</th>
{{--                        <th>{{ trans('salesteam.invoice_forecast') }}</th>--}}
                        <th>{{ trans('salesteam.actual_invoice') }}</th>
                        <th>{{ trans('table.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>
@stop {{-- Scripts --}} @section('scripts') @stop
