@extends('layouts.user')

{{-- Web site Title --}}
@section('title')
    {{ $title }}
@stop

{{-- Content --}}
@section('content')
    <div class="page-header clearfix">
        <div class="pull-right">
            <a href="{{ url('opportunity_converted_list') }}" class="btn btn-primary m-b-10">{{ trans('opportunity.converted_list') }}</a>
            <a href="{{ url('opportunity_delete_list') }}" class="btn btn-primary m-b-10">{{ trans('opportunity.delete_list') }}</a>
            <a href="{{ url('opportunity_archive') }}" class="btn btn-primary m-b-10">{{ trans('opportunity.archive') }}</a>
            @if($user_data->hasAccess(['opportunities.write']) || $user_data->inRole('admin'))
                <a href="{{ $type.'/create' }}" class="btn btn-primary m-b-10">
                    <i class="fa fa-plus-circle"></i> {{ trans('opportunity.create') }}</a>
            @endif
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4 class="panel-title">
                <i class="material-icons">event_seat</i>
                {{ $title }}
            </h4>
                                <span class="pull-right">
                                    <i class="fa fa-fw fa-chevron-up clickable"></i>
                                    <i class="fa fa-fw fa-times removepanel clickable"></i>
                                </span>
        </div>
        <div class="panel-body">
            <div class="table-responsive">
                <table id="data" class="table table-bordered">
                    <thead>
                    <tr>
                        <th>{{ trans('opportunity.opportunity_name') }}</th>
                        <th>{{ trans('company.company_name') }}</th>
                        <th>{{ trans('opportunity.next_action') }}</th>
                        <th>{{ trans('opportunity.stages') }}</th>
                        <th>{{ trans('opportunity.expected_revenue') }}</th>
                        <th>{{ trans('opportunity.probability') }}</th>
                        <th>{{ trans('salesteam.sales_team_id') }}</th>
                        <th>{{ trans('salesteam.main_staff') }}</th>
                        <th>{{ trans('table.actions') }}</th>
                        <th>{{ trans('opportunity.actions') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop

{{-- Scripts --}}
@section('scripts')

@stop