@extends('layouts.user')

{{-- Web site Title --}}
@section('title')
    {{ $title }}
@stop

{{-- Content --}}
@section('content')
    <div class="text-right">
        <a href="{{ url('sales_order') }}" class="btn btn-warning"><i
                    class="fa fa-arrow-left"></i> {{trans('table.back')}}</a>
    </div>
    <div class="panel panel-default m-t-30">
        <div class="panel-heading">
            <h4 class="panel-title">
                <i class="material-icons">attach_money</i>
                {{ $title }}
            </h4>
            <span class="pull-right">
                                    <i class="fa fa-fw fa-chevron-up clickable"></i>
                                    <i class="fa fa-fw fa-times removepanel clickable"></i>
                                </span>
        </div>
        <div class="panel-body">
            <div class="table-responsive">
                <table id="data" class="table  table-bordered">
                    <thead>
                    <tr>
                        <th>{{ trans('sales_order.sale_number') }}</th>
                        <th>{{ trans('sales_order.agent_name') }}</th>
{{--                        <th>{{ trans('sales_order.main_staff') }}</th>--}}
                        <th>{{ trans('sales_order.total') }}</th>
                        <th>{{ trans('sales_order.date') }}</th>
                        <th>{{ trans('sales_order.exp_date') }}</th>
                        <th>{{ trans('sales_order.payment_term') }}</th>
                        <th>{{ trans('sales_order.status') }}</th>
                        <th>{{ trans('sales_order.expired') }}</th>
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
            window.oTable.ajax.url('{!! url($type.'/draft_salesorder_list') !!}');
            window.oTable.ajax.reload();
        });
    </script>
@stop