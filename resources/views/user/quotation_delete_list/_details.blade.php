<div class="panel panel-primary">
    <div class="panel-body">
        @if (isset($quotation))
            {!! Form::open(['url' => $type . '/' . $quotation->id, 'method' => 'delete', 'class' => 'bf']) !!}
        @endif
            <div class="row">
                <div class="col-xs-12 col-sm-6 col-lg-4">
                    <div class="form-group">
                        {!! Form::label('customer', trans('quotation.agent_name'), ['class' => 'control-label']) !!}
                        <div class="controls">
                            {{ is_null($quotation->customer)?"":$quotation->customer->full_name }}
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-6 col-lg-4">
                    <div class="form-group">
                        {!! Form::label('sales_team_id', trans('quotation.sales_team_id'), ['class' => 'control-label']) !!}
                        <div class="controls">
                            {{ is_null($quotation->salesTeam)?"":$quotation->salesTeam->salesteam }}
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-6 col-lg-4">
                    <div class="form-group">
                        {!! Form::label('sales_person_id', trans('salesteam.main_staff'), ['class' => 'control-label']) !!}
                        <div class="controls">
                            {{ is_null($quotation->salesPerson)?"":$quotation->salesPerson->full_name }}
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-6 col-lg-4">
                    <div class="form-group">
                        <label class="control-label" for="title">{{trans('quotation.date')}}</label>
                        <div class="controls">
                            {{ $quotation->date }}
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-6 col-lg-4">
                    <div class="form-group">
                        <label class="control-label" for="title">{{trans('quotation.exp_date')}}</label>
                        <div class="controls">
                            {{ $quotation->exp_date }}
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-6 col-lg-4">
                    <div class="form-group">
                        {!! Form::label('payment_term', trans('quotation.payment_term'), ['class' => 'control-label']) !!}
                        <div class="controls">
                            {{ $quotation->payment_term.' '.trans('quotation.days') }}
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-6 col-lg-4">
                    <div class="form-group">
                        {!! Form::label('invoice_number', trans('quotation.status'), ['class' => 'control-label']) !!}
                        <div class="controls">
                            {{ $quotation->status }}
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
                                <th>{{trans('quotation.subtotal')}}</th>
                            </tr>
                            </thead>
                            <tbody id="InputsWrapper">
                            @if(isset($quotation) && $quotation->products->count()>0)
                                @foreach($quotation->products as $index => $variants)
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
                        {!! Form::label('total', trans('quotation.total'), ['class' => 'control-label']) !!}
                        <div class="controls">
                            {{ $quotation->total}}
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-6 col-lg-4">
                    <div class="form-group">
                        {!! Form::label('total', trans('quotation.taxes'), ['class' => 'control-label']) !!}
                        <div class="controls">
                            {{ $quotation->tax_amount}}
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-6 col-lg-4">
                    <div class="form-group">
                        {!! Form::label('total', trans('quotation.grand_total'), ['class' => 'control-label']) !!}
                        <div class="controls">
                            {{ $quotation->grand_total}}
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-6 col-lg-4">
                    <div class="form-group">
                        {!! Form::label('total', trans('quotation.discount').' (%)', ['class' => 'control-label']) !!}
                        <div class="controls">
                            {{ $quotation->discount}}
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-6 col-lg-4">
                    <div class="form-group">
                        {!! Form::label('total', trans('quotation.final_price'), ['class' => 'control-label']) !!}
                        <div class="controls">
                            {{ $quotation->final_price}}
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        {!! Form::label('quotation_duration', trans('qtemplate.terms_and_conditions'), ['class' => 'control-label']) !!}
                        <div class="controls">
                            {{ $quotation->terms_and_conditions }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
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
                </div>
            </div>
            {!! Form::close() !!}
    </div>
</div>
@section('scripts')
    <script>
        $(document).ready(function(){
           $("#lost_reason").select2({
               theme:"bootstrap",
               placeholder:"{{ trans('opportunity.lost_reason') }}"
           });
        });
    </script>
    @endsection