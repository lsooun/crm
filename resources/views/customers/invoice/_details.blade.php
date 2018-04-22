<br>
<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label class="control-label" for="title">{{trans('invoice.invoice_date')}}</label>
            <div class="controls">
                @if (isset($invoice))
                    {{ $invoice->invoice_date }}
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label class="control-label" for="title">{{trans('invoice.due_date')}}</label>
            <div class="controls">
                @if (isset($invoice))
                    {{ $invoice->due_date }}
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label class="control-label" for="title">{{trans('invoice.payment_term')}}</label>
            <div class="controls">
                @if (isset($invoice))
                    {{ $invoice->payment_term }}
                @endif
            </div>
        </div>
    </div>
</div>
<div class="form-group">
    <div class="panel-content">
        <label class="control-label">{{trans('invoice.order')}}</label>
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
                @foreach( $invoice->products as $product)
                    <tr>
                        <td>{{ $product->product_name }}</td>
                        <td>{{ $product->description }}</td>
                        <td>{{ $product->quantity }}</td>
                        <td>{{ $product->price }}</td>
                        <td>{{ number_format($product->quantity * $product->price * floatval(Settings::get('sales_tax')) / 100, 2, '.', ' ') }}</td>
                        <td>{{ $product->sub_total }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-sm-8">
        <div class="row">

        </div>
    </div>
    <div class="col-sm-4">
        <div class="row">
            <div class="col-sm-12">
                <div class="form-group">
                    <label class="col-sm-6 control-label">{{trans('invoice.untaxed_amount')}} </label>

                    <div class="col-sm-6 append-icon">
                        {{ $invoice->total }}
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="form-group">
                    <label class="col-sm-6 control-label">{{trans('invoice.taxes')}} </label>

                    <div class="col-sm-6 append-icon">
                        {{ $invoice->tax_amount }}
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="form-group">
                    <label class="col-sm-6 control-label">
                        <b>{{trans('invoice.total')}}</b>
                    </label>
                    <div class="col-sm-6 append-icon">
                        <b>{{ (Settings::get('currency_position')=='left')?
                        Settings::get('currency').$invoice->grand_total:
                        $invoice->grand_total.' '.Settings::get('currency') }}</b>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="form-group">
                    <label class="col-sm-6 control-label">
                        <i><b>{{trans('invoice.discount')}}</b></i>
                    </label>
                    <div class="col-sm-6 append-icon">
                        <i><b>{{ $invoice->discount.' %' }}</b></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="form-group">
                    <label class="col-sm-6 control-label">
                        <i><b>{{trans('invoice.final_price')}}</b></i>
                    </label>
                    <div class="col-sm-6 append-icon">
                        <i><b>{{ (Settings::get('currency_position')=='left')?
                        Settings::get('currency').$invoice->final_price:
                        $invoice->final_price.' '.Settings::get('currency') }}</b></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="form-group">
                    <label class="col-sm-6 control-label">
                        <i><b>{{trans('invoice.unpaid_amount')}}</b></i>
                    </label>
                    <div class="col-sm-6 append-icon">
                        <i><b>{{ (Settings::get('currency_position')=='left')?
                        Settings::get('currency').$invoice->unpaid_amount:
                        $invoice->unpaid_amount.' '.Settings::get('currency') }}</b></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="form-group">
    <div class="controls">
        <a href="{{ url($type) }}" class="btn btn-warning"><i
                    class="fa fa-arrow-left"></i> {{trans('table.back')}}</a>
    </div>
</div>