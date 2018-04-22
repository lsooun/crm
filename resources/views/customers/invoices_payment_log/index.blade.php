@extends('layouts.user')

{{-- Web site Title --}}
@section('title')
    {{ $title }}
@stop

{{-- Content --}}
@section('content')
    <div class="page-header clearfix">
        <div class="pull-right">
            @if($user_data->hasAccess(['invoices.write']) || $user_data->inRole('admin'))
            <a href="{{ 'invoices_payment_log/create' }}" class="btn btn-primary">
                <i class="fa fa-plus-circle"></i> {{ trans('invoices_payment_log.create_invoice_payment') }}</a>
                @endif
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4 class="panel-title">
                <i class="material-icons">archive</i>
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
                        <th>{{ trans('invoices_payment_log.payment_number') }}</th>
                        <th>{{ trans('invoices_payment_log.amount') }}</th>
                        <th>{{ trans('invoices_payment_log.invoice_number') }}</th>
                        <th>{{ trans('invoices_payment_log.payment_method') }}</th>
                        <th>{{ trans('invoices_payment_log.payment_date') }}</th>
                        <th>{{ trans('invoices_payment_log.agent_name') }}</th>
                        <th>{{ trans('invoices_payment_log.main_staff') }}</th>
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

@stop