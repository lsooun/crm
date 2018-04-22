<div class="panel panel-primary">
    <div class="panel-body">
        @if (isset($invoice))
            {!! Form::open(['url' => $type . '/' . $invoice->id, 'method' => 'delete', 'class' => 'bf']) !!}
        @endif
        <div class="row">
            <div class="col-xs-12 col-sm-6 col-lg-4">
                <div class="form-group">
                    {!! Form::label('invoice_number', trans('invoice.invoice_number'), array('class' => 'control-label')) !!}
                    <div class="controls">
                        {{ $invoice->invoice_number }}
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6 col-lg-4">
                <div class="form-group">
                    {!! Form::label('customer', trans('invoice.agent_name'), array('class' => 'control-label')) !!}
                    <div class="controls">
                        {{ is_null($invoice->customer)?"":$invoice->customer->full_name }}
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6 col-lg-4">
                <div class="form-group">
                    {!! Form::label('sales_team_id', trans('invoice.sales_team_id'), array('class' => 'control-label')) !!}
                    <div class="controls">
                        {{ is_null($invoice->salesTeam)?"":$invoice->salesTeam->salesteam }}
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6 col-lg-4">
                <div class="form-group">
                    {!! Form::label('sales_person_id', trans('salesteam.main_staff'), array('class' => 'control-label')) !!}
                    <div class="controls">
                        {{ is_null($invoice->salesPerson)?"":$invoice->salesPerson->full_name }}
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6 col-lg-4">
                <div class="form-group">
                    {!! Form::label('invoice_date', trans('invoice.invoice_date'), array('class' => 'control-label')) !!}
                    <div class="controls">
                        {{ $invoice->invoice_date }}
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6 col-lg-4">
                <div class="form-group">
                    {!! Form::label('due_date', trans('invoice.due_date'), array('class' => 'control-label')) !!}
                    <div class="controls">
                        {{ $invoice->due_date }}
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6 col-lg-4">
                <div class="form-group">
                    {!! Form::label('payment_term', trans('invoice.payment_term'), array('class' => 'control-label')) !!}
                    <div class="controls">
                        {{ $invoice->payment_term.' '.trans('invoice.days') }}
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6 col-lg-4">
                <div class="form-group">
                    {!! Form::label('status', trans('invoice.status'), array('class' => 'control-label')) !!}
                    <div class="controls">
                        {{ is_null($invoice->status)?"":$invoice->status }}
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <label class="control-label">{{trans('invoice.products')}}</label>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                        <tr class="detailes-tr">
                            <th>{{trans('invoice.product')}}</th>
                            <th>{{trans('invoice.description')}}</th>
                            <th>{{trans('invoice.quantity')}}</th>
                            <th>{{trans('invoice.unit_price')}}</th>
                            <th>{{trans('invoice.taxes')}}</th>
                            <th>{{trans('invoice.subtotal')}}</th>
                        </tr>
                        </thead>
                        <tbody id="InputsWrapper">
                        @if(isset($invoice) && $invoice->products->count()>0)
                            @foreach($invoice->products as $index => $variants)
                                <tr class="remove_tr">
                                    <td>
                                    {{$variants->product_name}}
                                    <td>
                                        {{$variants->description}}
                                    </td>
                                    <td>
                                        {{$variants->quantity}}
                                    </td>
                                    <td>
                                        {{$variants->price}}
                                    </td>
                                    <td>
                                        {{number_format($variants->quantity * $variants->price * floatval(Settings::get('sales_tax')) / 100, 2,
                            '.', '')}}</td>
                                    <td>
                                        {{$variants->sub_total}}
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12 col-sm-6 col-lg-4">
                <div class="form-group">
                    {!! Form::label('total', trans('invoice.untaxed_amount'), array('class' => 'control-label')) !!}
                    <div class="controls">
                        {{ $invoice->total}}
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6 col-lg-4">
                <div class="form-group">
                    {!! Form::label('total', trans('invoice.taxes'), array('class' => 'control-label')) !!}
                    <div class="controls">
                        {{ $invoice->tax_amount}}
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6 col-lg-4">
                <div class="form-group">
                    {!! Form::label('total', trans('invoice.total'), array('class' => 'control-label')) !!}
                    <div class="controls">
                        {{ $invoice->grand_total}}
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6 col-lg-4">
                <div class="form-group">
                    {!! Form::label('total', trans('invoice.discount').' (%)', array('class' => 'control-label')) !!}
                    <div class="controls">
                        {{ $invoice->discount}}
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6 col-lg-4">
                <div class="form-group">
                    {!! Form::label('total', trans('invoice.final_price'), array('class' => 'control-label')) !!}
                    <div class="controls">
                        {{ $invoice->final_price}}
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6 col-lg-4">
                <div class="form-group">
                    {!! Form::label('total', trans('invoice.unpaid_amount'), array('class' => 'control-label')) !!}
                    <div class="controls">
                        {{ $invoice->unpaid_amount}}
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="controls">
                @if (@$action == 'show')
                    <a href="{{ url($type) }}" class="btn btn-warning"><i
                                class="fa fa-arrow-left"></i> {{trans('table.back')}}</a>
                @else
                    <button type="submit" class="btn btn-success"><i class="fa fa-undo"></i> {{trans('table.restore')}}
                    </button>
                    <a href="{{ url($type) }}" class="btn btn-warning"><i
                                class="fa fa-arrow-left"></i> {{trans('table.back')}}</a>
                @endif
            </div>
        </div>
            {!! Form::close() !!}
    </div>
</div>
