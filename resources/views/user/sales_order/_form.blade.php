<div class="panel panel-primary">
    <div class="panel-body">
        @if (isset($saleorder))
            {!! Form::model($saleorder, ['url' => $type . '/' . $saleorder->id, 'method' => 'put', 'files'=> true, 'id'=>'sales_order']) !!}
            <div id="sendby_ajax"></div>
        @else
            {!! Form::open(['url' => $type, 'method' => 'post', 'files'=> true, 'id'=>'sales_order']) !!}
        @endif
        <div class="row">
            <div class="col-md-12">
                <div class="form-group required {{ $errors->has('customer_id') ? 'has-error' : '' }}">
                    {!! Form::label('customer_id', trans('quotation.agent_name'), ['class' => 'control-label required']) !!}
                    <div class="controls">
                        {!! Form::select('customer_id', $customers, (isset($saleorder->customer_id)?$saleorder->customer_id:null), ['id'=>'customer_id','class' => 'form-control']) !!}
                        <span class="help-block">{{ $errors->first('customer_id', ':message') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6">
                <div class="form-group required {{ $errors->has('sales_team_id') ? 'has-error' : '' }}">
                    {!! Form::label('sales_team_id', trans('quotation.sales_team_id'), ['class' => 'control-label required']) !!}
                    <div class="controls">
                        {!! Form::select('sales_team_id', $salesteams, (isset($saleorder)?$saleorder->sales_team_id:null), ['id'=>'sales_team_id','class' => 'form-control']) !!}
                        <span class="help-block">{{ $errors->first('sales_team_id', ':message') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6">
                <div class="form-group required {{ $errors->has('sales_person_id') ? 'has-error' : '' }}">
                    {!! Form::label('sales_person_id', trans('salesteam.main_staff'), ['class' => 'control-label required']) !!}
                    <div class="controls">
                        {!! Form::select('sales_person_id', $staffs, (isset($saleorder)?$saleorder->sales_person_id:null), ['id'=>'sales_person_id','class' => 'form-control']) !!}
                        <span class="help-block">{{ $errors->first('sales_person_id', ':message') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group required {{ $errors->has('qtemplate_id') ? 'has-error' : '' }}">
                    {!! Form::label('qtemplate_id', trans('quotation.quotation_template'), ['class' => 'control-label']) !!}
                    <div class="controls">
                        {!! Form::select('qtemplate_id', $qtemplates, (isset($saleorder)?$saleorder->qtemplate_id:null), ['id'=>'qtemplate_id','class' => 'form-control']) !!}
                        <span class="help-block">{{ $errors->first('qtemplate_id', ':message') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6">
                <div class="form-group required {{ $errors->has('date') ? 'has-error' : '' }}">
                    {!! Form::label('date', trans('quotation.date'), ['class' => 'control-label required']) !!}
                    <div class="controls">
                        {!! Form::text('date', null, ['class' => 'form-control date']) !!}
                        <span class="help-block">{{ $errors->first('date', ':message') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6">
                <div class="form-group required {{ $errors->has('exp_date') ? 'has-error' : '' }}">
                    {!! Form::label('exp_date', trans('quotation.exp_date'), ['class' => 'control-label required']) !!}
                    <div class="controls">
                        {!! Form::text('exp_date', null, ['class' => 'form-control date']) !!}
                        <span class="help-block">{{ $errors->first('exp_date', ':message') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6">
                <div class="form-group required {{ $errors->has('payment_term') ? 'has-error' : '' }}">
                    {!! Form::label('payment_term', trans('quotation.payment_term'), ['class' => 'control-label required']) !!}
                    <div class="controls">
                        <select name="payment_term" id="payment_term" class="form-control">
                            <option value=""></option>
                            @if(Settings::get('payment_term1')!='0')
                                <option value="{{Settings::get('payment_term1')}} {{trans('quotation.days')}}"
                                        @if(isset($saleorder) && Settings::get('payment_term1') ." Days" == $saleorder->payment_term) selected @endif>{{Settings::get('payment_term1')}} {{trans('quotation.days')}}</option>
                            @endif
                            @if(Settings::get('payment_term2')!='0')
                                <option value="{{Settings::get('payment_term2')}} {{trans('quotation.days')}}"
                                        @if(isset($saleorder) && Settings::get('payment_term2') ." Days" == $saleorder->payment_term) selected @endif>{{Settings::get('payment_term2')}} {{trans('quotation.days')}}</option>
                            @endif
                            @if(Settings::get('payment_term3')!='0')
                                <option value="{{Settings::get('payment_term3')}} {{trans('quotation.days')}}"
                                        @if(isset($saleorder) && Settings::get('payment_term3') ." Days" == $saleorder->payment_term) selected @endif>{{Settings::get('payment_term3')}} {{trans('quotation.days')}}</option>
                            @endif
                            <option value="0 {{trans('quotation.days')}}"
                                    @if(isset($saleorder) && $saleorder->payment_term==0) selected @endif>{{trans('quotation.immediate_payment')}}</option>
                        </select>
                        <span class="help-block">{{ $errors->first('payment_term', ':message') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6">
                <div class="form-group required {{ $errors->has('status') ? 'has-error' : '' }}">
                    {!! Form::label('status', trans('quotation.status'), ['class' => 'control-label required']) !!}
                    <div class="controls">
                        <div class="input-group">
                            <label>
                                <input type="radio" name="status" value="{{trans('sales_order.draft_salesorder')}}"
                                       class='icheckblue'
                                       @if(isset($saleorder) && $saleorder->status == 'Draft sales order') checked @endif>
                                {{trans('sales_order.draft_salesorder')}}
                            </label>
                            <label>
                                <input type="radio" name="status" value="{{trans('sales_order.send_salesorder')}}"
                                       class='icheckblue'
                                       @if(isset($saleorder) && $saleorder->status == 'Sales order sent') checked @endif>
                                {{trans('sales_order.send_salesorder')}}
                            </label>
                        </div>

                        <span class="help-block">{{ $errors->first('status', ':message') }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <label class="control-label required">{{trans('quotation.products')}}
                    <span>{!! $errors->first('products') !!}</span></label>
                <div class="{{ $errors->has('product_id.*') ? 'has-error' : '' }}">
                    <span class="help-block">{{ $errors->first('product_id.*', ':message') }}</span>
                </div>
                <div class="{{ $errors->has('product_id') ? 'has-error' : '' }}">
                    <span class="help-block">{{ $errors->first('product_id', ':message') }}</span>
                </div>
                <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                    <tr class="detailes-tr">
                        <th>{{trans('quotation.product')}}</th>
                        <th>{{trans('quotation.description')}}</th>
                        <th>{{trans('quotation.quantity')}}</th>
                        <th>{{trans('quotation.unit_price')}}</th>
                        <th>{{trans('quotation.subtotal')}}</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody id="InputsWrapper">

                    @if(isset($saleorder) && $saleorder->products->count()>0)
                        @foreach($saleorder->products as $index => $variants)
                            <tr class="remove_tr">
                                <td>
                                    <input type="hidden" name="product_id[]" id="product_id{{$index}}"
                                           value="{{$variants->product_id}}"
                                           readOnly>
                                    <input type="hidden" name="product_name[]" id="product_name{{$index}}"
                                           value="{{$variants->product_name}}"
                                           readOnly>
                                    <select name="product_list" id="product_list{{$index}}" class="form-control product_list"
                                            data-search="true" onchange="product_value({{$index}});">
                                        <option value=""></option>
                                        @foreach( $products as $product)
                                            <option value="{{ $product->id . '_' . $product->product_name . '_' . $product->sale_price . '_' . $product->description}}"
                                                    @if($product->id == $variants->product_id) selected="selected" @endif>{{ $product->product_name}}</option>
                                        @endforeach
                                    </select>
                                <td><textarea name=description[]" id="description{{$index}}" rows="2"
                                              class="form-control resize_vertical" readOnly>{{$variants->description}}</textarea>
                                </td>
                                <td><input type="text" name="quantity[]" id="quantity{{$index}}"
                                           value="{{$variants->quantity}}"
                                           class="form-control number"
                                           onkeyup="product_price_changes('quantity{{$index}}','price{{$index}}','sub_total{{$index}}');">
                                </td>
                                <td>{{$variants->price}}<input type="hidden" name="price[]" id="price{{$index}}"
                                                               value="{{$variants->price}}"
                                                               class="form-control"></td>
                                <input type="hidden" name="taxes[]" id="taxes{{$index}}"
                                       value="{{ floatval(Settings::get('sales_tax')) }}" class="form-control"></td>
                                <td><input type="text" name="sub_total[]" id="sub_total{{$index}}"
                                           value="{{$variants->sub_total}}"
                                           class="form-control" readOnly></td>
                                <td><a href="javascript:void(0)" class="delete removeclass"><i
                                                class="fa fa-fw fa-trash fa-lg text-danger"></i></a></td>
                            </tr>
                        @endforeach
                    @endif
                    </tbody>
                </table>
            </div>
                <button type="button" id="AddMoreFile"
                        class="btn btn-sm btn-primary"><i class="fa fa-plus"></i> {{trans('quotation.add_product')}}
                </button>
                <div class="row">&nbsp;</div>
            </div>
        </div>
            <div class="row">
                <div class="col-xs-12 col-sm-6">
                    <div class="form-group required {{ $errors->has('total') ? 'has-error' : '' }}">
                        {!! Form::label('total', trans('quotation.total'), ['class' => 'control-label']) !!}
                        <div class="controls">
                            {!! Form::text('total', null, ['class' => 'form-control','readonly']) !!}
                            <span class="help-block">{{ $errors->first('total', ':message') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-6">
                    <div class="form-group required {{ $errors->has('tax_amount') ? 'has-error' : '' }}">
                        {!! Form::label('tax_amount', trans('quotation.tax_amount').' ('.floatval(Settings::get('sales_tax')).'%)', ['class' => 'control-label']) !!}
                        <div class="controls">
                            {!! Form::text('tax_amount', null, ['class' => 'form-control','readonly']) !!}
                            <span class="help-block">{{ $errors->first('tax_amount', ':message') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-6">
                    <div class="form-group required {{ $errors->has('grand_total') ? 'has-error' : '' }}">
                        {!! Form::label('grand_total', trans('quotation.grand_total'), ['class' => 'control-label']) !!}
                        <div class="controls">
                            {!! Form::text('grand_total', null, ['class' => 'form-control','readonly']) !!}
                            <span class="help-block">{{ $errors->first('grand_total', ':message') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-6">
                    <div class="form-group required {{ $errors->has('discount') ? 'has-error' : '' }}">
                        {!! Form::label('discount', trans('quotation.discount').' (%)', ['class' => 'control-label']) !!}
                        <div class="controls">
                            <input type="text" name="discount" id="discount"
                                   value="{{(isset($saleorder)?$saleorder->discount:"0.00")}}"
                                   class="form-control number"
                                   onkeyup="update_total_price();">
                            <span class="help-block">{{ $errors->first('discount', ':message') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group required {{ $errors->has('final_price') ? 'has-error' : '' }}">
                        {!! Form::label('final_price', trans('quotation.final_price'), ['class' => 'control-label']) !!}
                        <div class="controls">
                            {!! Form::text('final_price', null, ['class' => 'form-control','readonly']) !!}
                            <span class="help-block">{{ $errors->first('final_price', ':message') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group required {{ $errors->has('terms_and_conditions') ? 'has-error' : '' }}">
                        {!! Form::label('quotation_duration', trans('qtemplate.terms_and_conditions'), ['class' => 'control-label']) !!}
                        <div class="controls">
                            {!! Form::textarea('terms_and_conditions', null, ['class' => 'form-control resize_vertical']) !!}
                            <span class="help-block">{{ $errors->first('terms_and_conditions', ':message') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        <!-- Form Actions -->
        <div class="form-group">
            <div class="controls">
                <button type="submit" class="btn btn-success"><i
                            class="fa fa-check-square-o"></i> {{trans('table.ok')}}</button>
                <a href="{{ url()->previous() }}" class="btn btn-warning"><i
                            class="fa fa-arrow-left"></i> {{trans('table.back')}}</a>
            </div>
        </div>
        <!-- ./ form actions -->
        {!! Form::close() !!}
    </div>
</div>

@section('scripts')
    <script>
        $(document).ready(function() {
            $("#customer_id").select2({
                theme: "bootstrap",
                placeholder: "{{ trans('quotation.agent_name') }}"
            });
            $("#sales_person_id").select2({
                theme: "bootstrap",
                placeholder: "{{ trans('salesteam.main_staff') }}"
            });
            $("#sales_team_id").select2({
                theme: "bootstrap",
                placeholder: "{{ trans('quotation.sales_team_id') }}"
            });
            $("#qtemplate_id").select2({
                theme: "bootstrap",
                placeholder: "{{ trans('quotation.quotation_template') }}"
            });
            $("#payment_term").select2({
                theme: "bootstrap",
                placeholder: "{{ trans('quotation.payment_term') }}"
            });
            $(".product_list").select2({
                theme:"bootstrap",
                placeholder:"Product"
            });
            $("#recipients").select2({
                placeholder:"{{ trans('quotation.recipients') }}",
                theme: 'bootstrap'
            });

            $("#sales_order").bootstrapValidator({
                fields: {
                    customer_id: {
                        validators: {
                            notEmpty: {
                                message: 'The agent name field is required.'
                            }
                        }
                    },
                    sales_team_id: {
                        validators: {
                            notEmpty: {
                                message: 'The sales team field is required.'
                            }
                        }
                    },
                    sales_person_id: {
                        validators: {
                            notEmpty: {
                                message: 'The main staff field is required.'
                            }
                        }
                    },
                    date: {
                        validators: {
                            notEmpty: {
                                message: 'The start date field is required.'
                            }
                        }
                    },
                    exp_date: {
                        validators: {
                            notEmpty: {
                                message: 'The expiration date field is required.'
                            }
                        }
                    },
                    payment_term: {
                        validators: {
                            notEmpty: {
                                message: 'The payment term field is required.'
                            }
                        }
                    },
                    status: {
                        validators: {
                            notEmpty: {
                                message: 'The quotation status field is required.'
                            }
                        }
                    },
                    product_list: {
                        validators: {
                            notEmpty: {
                                message: 'The products field is required.'
                            }
                        }
                    }
                }
            });
        });
        $(function () {
            update_total_price();
            $('#qtemplate_id').change(function () {
                if ($(this).val() > 0) {
                    $.ajax({
                        type: "GET",
                        url: '{{url("quotation/ajax_qtemplates_products")}}/' + $(this).val(),
                        success: function (data) {
                            content_data = '';
                            $.each(data, function (i, item) {
                                content_data += makeContent(FieldCount, item);
                                FieldCount++;
                            });
                            $("#InputsWrapper").html(content_data);
                            update_total_price();
                        }
                    });
                }
                setTimeout(function(){
                    $(".product_list").select2({
                        theme:"bootstrap",
                        placeholder:"Product"
                    })
                },100);
            });
        });
        function product_value(FieldCount) {
            var all_Val = $("#product_list" + FieldCount).val();
            var res = all_Val.split("_");
            $('#product_id' + FieldCount).val(res[0]);
            $('#product_name' + FieldCount).val(res[1]);
            $('#quantity' + FieldCount).val(res[4]);
            $('#price' + FieldCount).val(res[2]);
            $('#description' + FieldCount).val(res[3]);
            var quantity=$('#quantity'+FieldCount).val();
            var price=$('#price'+FieldCount).val();
            $('#sub_total' + FieldCount).val(price*quantity);
            update_total_price();
        }
        function product_price_changes(quantity, product_price, sub_total_id) {
            var no_quantity = $("#" + quantity).val();
            if(no_quantity.length < 1) {
                no_quantity = 0;
            }
            var no_product_price = $("#" + product_price).val();
            if(no_product_price.length < 1) {
                no_product_price = 0;
            }

            var sub_total = parseFloat(no_quantity * no_product_price);

            var tax_amount = 0;
            tax_amount = (sub_total * {{ floatval(Settings::get('sales_tax')) }}) / 100;
            $('#taxes').val(tax_amount.toFixed(2));

            $('#' + sub_total_id).val(sub_total);
            update_total_price();

        }

        function update_total_price() {
            var sub_total = 0;
            $('#total').val(0);
            $('#tax_amount').val(0);
            $('#grand_total').val(0);
            $('#final_price').val(0);
            var sub = 0;
            $('input[name^="sub_total"]').each(function () {
                sub = $(this).val();
                if(sub.length < 1) {
                    sub = 0;
                }
                sub_total += parseFloat(sub);
                $('#total').val(sub_total.toFixed(2));

                var tax_per = {{ floatval(Settings::get('sales_tax')) }};
                var tax_amount = 0;

                tax_amount = (sub_total * tax_per) / 100;
                $('#tax_amount').val(tax_amount.toFixed(2));
                var grand_total = 0;
                grand_total = sub_total + tax_amount;
                $('#grand_total').val(grand_total.toFixed(2));
                var discount = $("#discount").val();
                discount_amount = (grand_total * discount) / 100;
                final_price = grand_total - discount_amount;
                $('#final_price').val(final_price.toFixed(2));
            });
        }

        function makeContent(number, item) {
            item = item || '';

            var content = '<tr class="remove_tr"><td>';
            content += '<input type="hidden" name="product_id[]" id="product_id' + number + '" value="' + ((typeof item.product_id == 'undefined') ? '' : item.product_id) + '" readOnly>';
            content += '<input type="hidden" name="product_name[]" id="product_name' + number + '" value="' + ((typeof item.product_name == 'undefined') ? '' : item.product_name) + '" readOnly>';
            content += '<select name="product_list" id="product_list' + number + '" class="form-control product_list" data-search="true" onchange="product_value(' + number + ');">' +
                '<option value=""></option>';
            @foreach( $products as $product)
                content += '<option value="{{ $product->id . '_' . $product->product_name . '_' . $product->sale_price . '_' . $product->description.'_'.$product->quantity_on_hand}}"';
            if (item.product_id =={{$product->id}}) {
                content += 'selected';
            }
            content += '>' +
                '{{ $product->product_name}}</option>';
            @endforeach

                content += '</select>' +
                '<td><textarea name=description[]" id="description' + number + '" rows="2" class="form-control resize_vertical" readOnly>' + ((typeof item.description == 'undefined') ? '' : item.description) + '</textarea>' +
                '</td>' +
                '<td><input type="text" name="quantity[]" id="quantity' + number + '" value="' + ((typeof item.quantity == 'undefined') ? '' : item.quantity) + '" class="form-control number" onkeyup="product_price_changes(\'quantity' + number + '\',\'price' + number + '\',\'sub_total' + number + '\');"></td>' +
                '<td><input type="text" name="price[]" id="price' + number + '" value="' + ((typeof item.price == 'undefined') ? '' : item.price) + '" class="form-control" readOnly>' +
                '<input type="hidden" name="taxes[]" id="taxes' + number + '" value="{{ floatval(Settings::get('sales_tax')) }}" class="form-control" readOnly></td>' +
                '<td><input type="text" name="sub_total[]" id="sub_total' + number + '" value="' + ((typeof item.sub_total == 'undefined') ? '' : item.sub_total) + '" class="form-control" readOnly></td>' +
                '<td><a href="javascript:void(0)" class="delete removeclass" title="{{ trans('table.delete') }}"><i class="fa fa-fw fa-trash fa-lg text-danger"></i></a></td>' +
                '</tr>';
            return content;
        }

        var FieldCount = 1; //to keep track of text box added
        var MaxInputs = 50; //maximum input boxes allowed
        var InputsWrapper = $("#InputsWrapper"); //Input boxes wrapper ID
        var AddButton = $("#AddMoreFile"); //Add button ID

        var x = InputsWrapper.length; //initlal text box count


        $("#total").val("0");

        $(AddButton).click(function (e)  //on add input button click
        {

            setTimeout(function(){
                $(".product_list").select2({
                    theme:"bootstrap",
                    placeholder:"Product"
                });
            });
            if (x <= MaxInputs) //max input box allowed
            {
                FieldCount++; //text box added increment
                content = makeContent(FieldCount);
                $(InputsWrapper).append(content);
                x++; //text box increment

                $('.number').keypress(function (event) {
                    if (event.which < 46
                        || event.which > 59) {
                        event.preventDefault();
                    } // prevent if not number/dot

                    if (event.which == 46
                        && $(this).val().indexOf('.') != -1) {
                        event.preventDefault();
                    } // prevent if already dot
                });
            }
            //            $('#surveyForm').formValidation('addField', $option);

            return false;
        });

        $(InputsWrapper).on("click", ".removeclass", function (e) { //user click on remove text
            $(this).closest(".remove_tr").remove();
            update_total_price();
            return false;
        });

        function create_pdf(saleorder_id) {
            $.ajax({
                type: "GET",
                url: "{{url('sales_order' )}}/" + saleorder_id + "/ajax_create_pdf",
                data: {'_token': '{{csrf_token()}}'},
                success: function (msg) {
                    if (msg != '') {
                        $("#pdf_url").attr("href", msg);
                        var index = msg.lastIndexOf("/") + 1;
                        var filename = msg.substr(index);
                        $("#pdf_url").html(filename);
                        $("#saleorder_pdf").val(filename);
                    }
                }
            });
        }



        $('#form').on('keyup keypress', function (e) {
            var code = e.keyCode || e.which;
            if (code == 13) {
                e.preventDefault();
                return false;
            }
        });

        @if(isset($saleorder))
        $('#date').datetimepicker({
            format: '{{ isset($jquery_date)?$jquery_date:"MMMM D,GGGG" }}',
            minDate:'{{ $saleorder->updated_at->toDateString() }}',
            useCurrent: false,
            icons: {
                time: "fa fa-clock-o",
                date: "fa fa-calendar",
                up: "fa fa-arrow-up",
                down: "fa fa-arrow-down"
            }
        }).on("dp.change", function (e) {
            $('#exp_date').data("DateTimePicker").minDate(e.date);
            var nextActionVal = moment($("#date").val(),"{{$jquery_date}}");
            var expectedClosingVal= moment($("#exp_date").val(),"{{$jquery_date}}");
            var difference = expectedClosingVal.diff(nextActionVal);
            var days = moment.duration(difference, "ms")._data.days;
            if(days<0){
                $("#exp_date").val('');
                $('#sales_order').bootstrapValidator('revalidateField', 'exp_date');
            }
            $('#sales_order').bootstrapValidator('revalidateField', 'date');
        });
        $('#exp_date').datetimepicker({
            format: '{{ isset($jquery_date)?$jquery_date:"MMMM D,GGGG" }}',
            minDate:'{{ $saleorder->updated_at->toDateString() }}',
            useCurrent: false,
            icons: {
                time: "fa fa-clock-o",
                date: "fa fa-calendar",
                up: "fa fa-arrow-up",
                down: "fa fa-arrow-down"
            }
        }).on("dp.change", function (e) {
            $('#date').data("DateTimePicker").maxDate(e.date);
            $('#sales_order').bootstrapValidator('revalidateField', 'exp_date');
        });
        @else
        $('#date').datetimepicker({
            format: '{{ isset($jquery_date)?$jquery_date:"MMMM D,GGGG" }}',
            useCurrent: false,
            minDate:moment().format('{{$jquery_date}}'),
            icons: {
                time: "fa fa-clock-o",
                date: "fa fa-calendar",
                up: "fa fa-arrow-up",
                down: "fa fa-arrow-down"
            }
        }).on("dp.change", function (e) {
            $('#exp_date').data("DateTimePicker").minDate(e.date);
            $('#sales_order').bootstrapValidator('revalidateField', 'date');
        });
        $('#exp_date').datetimepicker({
            format: '{{ isset($jquery_date)?$jquery_date:"MMMM D,GGGG" }}',
            useCurrent: false,
            minDate:moment().format('{{$jquery_date}}'),
            icons: {
                time: "fa fa-clock-o",
                date: "fa fa-calendar",
                up: "fa fa-arrow-up",
                down: "fa fa-arrow-down"
            }
        }).on("dp.change", function (e) {
            $('#date').data("DateTimePicker").maxDate(e.date);
            $('#sales_order').bootstrapValidator('revalidateField', 'exp_date');
        });
        @endif
        @if(old('payment_term'))
        $("#payment_term").find("option[value='"+'{{old("payment_term")}}'+"']").attr('selected',true);
        @endif
        $("#sales_team_id").change(function(){
            ajaxMainStaffList($(this).val());
        });
        @if(old('sales_person_id'))
        ajaxMainStaffList({{old('sales_team_id')}});
        @endif
        function ajaxMainStaffList(id){
            $.ajax({
                type: "GET",
                url: '{{ url('opportunity/ajax_main_staff_list')}}',
                data: {'id': id, _token: '{{ csrf_token() }}' },
                success: function (data) {
                    $("#sales_person_id").empty();
                    var teamLeader;
                    $.each(data.main_staff, function (val, text) {
                        teamLeader =data.team_leader;
                        $('#sales_person_id').append($('<option></option>').val(val).html(text));
                    });
                    $("#sales_person_id").find("option[value='"+teamLeader+"']").attr('selected',true);
                    $("#sales_person_id").find("option[value!='"+teamLeader+"']").attr('selected',false);
                    $("#sales_person_id").select2({
                        theme:'bootstrap',
                        placeholder:"{{ trans('salesteam.main_staff') }}"
                    });
                    $('#sales_order').bootstrapValidator('revalidateField', 'sales_person_id');
                }
            });
        }
        $("#customer_id").change(function(){
            ajaxSalesTeamList($(this).val());
        });
        @if(old('sales_team_id'))
        ajaxSalesTeamList({{old('customer_id')}});
        @endif
        @if(!isset($saleorder))
        $("#sales_team_id").empty();
        $("#sales_person_id").empty();
        @endif
        function ajaxSalesTeamList(id){
            $.ajax({
                type: "GET",
                url: '{{ url('quotation/ajax_sales_team_list')}}',
                data: {'id': id, _token: '{{ csrf_token() }}' },
                success: function (data) {
                    $("#sales_team_id").empty();
                    $.each(data.sales_team, function (val, text) {
                        $('#sales_team_id').append($('<option></option>').val(val).html(text));
                    });
                    $("#sales_team_id").find("option[value='"+data.agent_name+"']").attr('selected',true);
                    $("#sales_team_id").find("option[value!='"+data.agent_name+"']").attr('selected',false);
                    $("#sales_team_id").select2({
                        theme:'bootstrap',
                        placeholder:"{{ trans('quotation.sales_team_id') }}"
                    });
                    ajaxMainStaffList(data.agent_name);
                    $('#sales_order').bootstrapValidator('revalidateField', 'sales_team_id');
                }
            });
        }

        $("#send_saleorder").bootstrapValidator({
            fields: {
                'recipients[]': {
                    validators: {
                        notEmpty: {
                            message: 'The recipients field is required'
                        }
                    }
                },
                message_body:{
                    validators: {
                        notEmpty: {
                            message: 'The message field is required'
                        }
                    }
                }
            }
        }).on('success.form.bv', function(e) {
            var formData = new FormData($(this)[0]);
            $.ajax({
                url: "{{url('sales_order/send_saleorder')}}",
                type: "POST",
                data: formData,
                async: false,
                success: function (msg) {
                    $('body,html').animate({scrollTop: 0}, 200);
                    $("#sendby_ajax").html(msg);
                    setTimeout(function(){
                        $("#sendby_ajax").hide();
                    },5000);
                    $("#modal-send_by_email").modal('hide');
                },
                cache: false,
                contentType: false,
                processData: false
            });
            e.preventDefault();
        });
        $("#modal-send_by_email").on('hide.bs.modal', function () {
            $("#recipients").find("option").attr('selected',false);
            $("#recipients").select2({
                placeholder:"{{ trans('quotation.recipients') }}",
                theme: 'bootstrap'
            });
            $("#send_saleorder").data('bootstrapValidator').resetForm();
        });
        $('.icheckblue').iCheck({
            checkboxClass: 'icheckbox_minimal-blue',
            radioClass: 'iradio_minimal-blue'
        });
        $('.icheckblue').on('ifChecked',function(){
            $("#sales_order").bootstrapValidator('revalidateField', 'status');
        });

    </script>
@endsection