@extends('layouts.user')

{{-- Web site Title --}}
@section('title')
    {{ $title }}
@stop

{{-- Content --}}
@section('content')
    <div class="text-right">
        <a href="{{ url('quotation') }}" class="btn btn-warning"><i
                    class="fa fa-arrow-left"></i> {{trans('table.back')}}</a>
    </div>
    <div class="panel panel-default m-t-30">
        <div class="panel-heading">
            <h4 class="panel-title">
                <i class="material-icons">receipt</i>
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
                        <th>{{ trans('quotation.quotations_number') }}</th>
                        <th>{{ trans('quotation.agent_name') }}</th>
{{--                        <th>{{ trans('salesteam.salesteam') }}</th>--}}
{{--                        <th>{{ trans('salesteam.main_staff') }}</th>--}}
                        <th>{{ trans('quotation.total') }}</th>
                        <th>{{ trans('quotation.date') }}</th>
                        <th>{{ trans('quotation.exp_date') }}</th>
                        <th>{{ trans('quotation.payment_term') }}</th>
                        <th>{{ trans('quotation.status') }}</th>
                        <th>{{ trans('quotation.expired') }}</th>
                        <th>{{ trans('table.actions') }}</th>
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
    <script>
        $(document).ready(function(){
            window.oTable.ajax.url('{!! url($type.'/draft_quotations_list') !!}');
            window.oTable.ajax.reload();
        });
    </script>
@stop