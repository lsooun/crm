<div class="panel panel-primary">
    <div class="panel-body">
        <div class="row">
            <div class="col-xs-12 col-sm-6 col-lg-4">
                <div class="form-group">
                    {!! Form::label('invoice_number', trans('sales_order.sale_number'), ['class' => 'control-label']) !!}
                    <div class="controls">
                        {{ $saleorder->sale_number }}
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6 col-lg-4">
                <div class="form-group">
                    {!! Form::label('customer', trans('quotation.agent_name'), ['class' => 'control-label']) !!}
                    <div class="controls">
                        {{ is_null($saleorder->customer)?"":$saleorder->customer->full_name }}
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6 col-lg-4">
                <div class="form-group">
                    {!! Form::label('sales_team_id', trans('quotation.sales_team_id'), ['class' => 'control-label']) !!}
                    <div class="controls">
                        {{ is_null($saleorder->salesTeam)?"":$saleorder->salesTeam->salesteam }}
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6 col-lg-4">
                <div class="form-group">
                    {!! Form::label('sales_person_id', trans('salesteam.main_staff'), ['class' => 'control-label']) !!}
                    <div class="controls">
                        {{ is_null($saleorder->salesPerson)?"":$saleorder->salesPerson->full_name }}
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6 col-lg-4">
                <div class="form-group">
                    <label class="control-label" for="title">{{trans('quotation.date')}}</label>
                    <div class="controls">
                        {{ $saleorder->date }}
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6 col-lg-4">
                <div class="form-group">
                    <label class="control-label" for="title">{{trans('quotation.exp_date')}}</label>
                    <div class="controls">
                        {{ $saleorder->exp_date }}
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6 col-lg-4">
                <div class="form-group">
                    {!! Form::label('payment_term', trans('quotation.payment_term'), ['class' => 'control-label']) !!}
                    <div class="controls">
                        {{ $saleorder->payment_term }}
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <label class="control-label">{{trans('quotation.products')}}</label>
                <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                    <tr class="detailes-tr">
                        <th>{{trans('quotation.product')}}</th>
                        <th>{{trans('quotation.description')}}</th>
                        <th>{{trans('quotation.quantity')}}</th>
                        <th>{{trans('quotation.unit_price')}}</th>
                        <th>{{trans('quotation.taxes')}}</th>
                        <th>{{trans('quotation.subtotal')}}</th>
                    </tr>
                    </thead>
                    <tbody id="InputsWrapper">
                    @if(isset($saleorder) && $saleorder->products->count()>0)
                        @foreach($saleorder->products as $index => $variants)
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
                    {!! Form::label('total', trans('sales_order.total'), ['class' => 'control-label']) !!}
                    <div class="controls">
                        {{ $saleorder->total}}
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6 col-lg-4">
                <div class="form-group">
                    {!! Form::label('total', trans('quotation.taxes'), ['class' => 'control-label']) !!}
                    <div class="controls">
                        {{ $saleorder->tax_amount}}
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6 col-lg-4">
                <div class="form-group">
                    {!! Form::label('total', trans('quotation.grand_total'), ['class' => 'control-label']) !!}
                    <div class="controls">
                        {{ $saleorder->grand_total}}
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6 col-lg-4">
                <div class="form-group">
                    {!! Form::label('total', trans('quotation.discount').' (%)', ['class' => 'control-label']) !!}
                    <div class="controls">
                        {{ $saleorder->discount}}
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6 col-lg-4">
                <div class="form-group">
                    {!! Form::label('total', trans('quotation.final_price'), ['class' => 'control-label']) !!}
                    <div class="controls">
                        {{ $saleorder->final_price}}
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="controls">
                @if (@$action == 'show')
                    <a href="{{ url()->previous() }}" class="btn btn-warning"><i
                                class="fa fa-arrow-left"></i> {{trans('table.back')}}</a>
                @else
                    <button type="submit" class="btn btn-danger"><i
                                class="fa fa-trash"></i> {{trans('table.delete')}}</button>
                    <a href="{{ url()->previous() }}" class="btn btn-warning"><i
                                class="fa fa-arrow-left"></i> {{trans('table.back')}}</a>
                @endif
            </div>
        </div>
    </div>
</div>