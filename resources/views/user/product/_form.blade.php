<div class="panel panel-primary">
    <div class="panel-body">
        @if (isset($product))
            {!! Form::model($product, ['url' => $type . '/' . $product->id, 'method' => 'put', 'files'=> true, 'id'=> 'product']) !!}
        @else
            {!! Form::open(['url' => $type, 'method' => 'post', 'files'=> true, 'id'=> 'product']) !!}
        @endif
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group required {{ $errors->has('product_image_file') ? 'has-error' : '' }}">
                        {!! Form::label('product_image_file', trans('product.product_image'), ['class' => 'control-label']) !!}
                        <div class="controls row" v-image-preview>
                            <div class="col-sm-12">
                                <div class="fileinput fileinput-new" data-provides="fileinput">
                                    <div class="fileinput-preview thumbnail form_Blade" data-trigger="fileinput">
                                        <img id="image-preview" width="300">
                                        @if(isset($product->product_image) && $product->product_image!="")
                                            <img src="{{ url('uploads/products/thumb_'.$product->product_image) }}"
                                                 alt="Image">
                                        @endif
                                    </div>
                                    <div>
                                        <span class="btn btn-default btn-file">
                                            <span class="fileinput-new">{{trans('dashboard.select_image')}}</span>
                                            <span class="fileinput-exists">{{trans('dashboard.change')}}</span>
                                            <input type="file" name="product_image_file">
                                        </span>
                                        <a href="#" class="btn btn-default fileinput-exists"
                                           data-dismiss="fileinput">{{trans('dashboard.remove')}}</a>
                                    </div>
                                    <div>
                                        <span class="help-block">{{ $errors->first('product_image_file', ':message') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group required {{ $errors->has('product_name') ? 'has-error' : '' }}">
                        {!! Form::label('product_name', trans('product.product_name'), ['class' => 'control-label required']) !!}
                        <div class="controls">
                            {!! Form::text('product_name', null, ['class' => 'form-control','placeholder' => 'Product name']) !!}
                            <span class="help-block">{{ $errors->first('product_name', ':message') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group required {{ $errors->has('category_id') ? 'has-error' : '' }}">
                        {!! Form::label('category_id', trans('product.category_id'), ['class' => 'control-label required']) !!}
                        <div class="controls">
                            {!! Form::select('category_id', $categories, null, ['id'=>'category_id','class' => 'form-control']) !!}
                            <span class="help-block">{{ $errors->first('category_id', ':message') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group required {{ $errors->has('product_type') ? 'has-error' : '' }}">
                        {!! Form::label('product_type', trans('product.product_type'), ['class' => 'control-label required']) !!}
                        <div class="controls">
                            {!! Form::select('product_type', $product_types, (isset($product)?$product->product_type:null), ['id'=>'product_type','class' => 'form-control']) !!}
                            <span class="help-block">{{ $errors->first('product_type', ':message') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group required {{ $errors->has('status') ? 'has-error' : '' }}">
                        {!! Form::label('status', trans('product.status'), ['class' => 'control-label required']) !!}
                        <div class="controls">
                            {!! Form::select('status', $statuses, (isset($product)?$product->status:null), ['id'=>'status','class' => 'form-control']) !!}
                            <span class="help-block">{{ $errors->first('status', ':message') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group required {{ $errors->has('quantity_on_hand') ? 'has-error' : '' }}">
                        {!! Form::label('quantity_on_hand', trans('product.quantity_on_hand'), ['class' => 'control-label required']) !!}
                        <div class="controls">
                            {!! Form::input('number','quantity_on_hand', null, ['class' => 'form-control', 'min'=>0]) !!}
                            <span class="help-block">{{ $errors->first('quantity_on_hand', ':message') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group required {{ $errors->has('quantity_available') ? 'has-error' : '' }}">
                        {!! Form::label('quantity_available', trans('product.quantity_available'), ['class' => 'control-label required']) !!}
                        <div class="controls">
                            {!! Form::input('number','quantity_available', null, ['class' => 'form-control', 'min'=>0]) !!}
                            <span class="help-block">{{ $errors->first('quantity_available', ':message') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group required {{ $errors->has('description') ? 'has-error' : '' }}">
                        {!! Form::label('description', trans('product.description'), ['class' => 'control-label']) !!}
                        <div class="controls">
                            {!! Form::textarea('description', null, ['class' => 'form-control resize_vertical', 'placeholder' => 'Product Information']) !!}
                            <span class="help-block">{{ $errors->first('description', ':message') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group required {{ $errors->has('sale_price') ? 'has-error' : '' }}">
                        {!! Form::label('sale_price', trans('product.sale_price'), ['class' => 'control-label required']) !!}
                        <div class="controls">
                            {!! Form::text('sale_price', null, ['class' => 'form-control']) !!}
                            <span class="help-block">{{ $errors->first('sale_price', ':message') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Form Actions -->
            <div class="form-group">
                <div class="controls">
                    <button type="submit" class="btn btn-success"><i
                                class="fa fa-check-square-o"></i> {{trans('table.ok')}}</button>
                    <a href="{{ route($type.'.index') }}" class="btn btn-warning"><i
                                class="fa fa-arrow-left"></i> {{trans('table.back')}}</a>
                </div>
            </div>
        <!-- ./ form actions -->

        {!! Form::close() !!}
    </div>
</div>


@section('scripts')
    <script>
        $(document).ready(function () {

            var MaxInputs = 50; //maximum input boxes allowed
            var InputsWrapper = $("#InputsWrapper"); //Input boxes wrapper ID
            var AddButton = $("#AddMoreFileBox"); //Add button ID

            var x = InputsWrapper.length; //initlal text box count
            var FieldCount = 1; //to keep track of text box added

            $(AddButton).click(function (e)  //on add input button click
            {
                if (x <= MaxInputs) //max input box allowed
                {
                    FieldCount++; //text box added increment
                    //add input box
                    $(InputsWrapper).append('<tr><td><input type="text" name="attribute_name[]" value="" class="form-control"></td><td><input type="text" name="product_attribute_value[]" value="" class="form-control"></td><td><a href="javascript:void(0)" class="delete removeclass" data-toggle="modal" data-target="#modal-basic"><i class="fa fa-fw fa-times text-danger"></i></a></td></tr>');
                    x++; //text box increment
                }
                return false;
            });

            $("#InputsWrapper").on("click", ".removeclass", function (e) { //user click on remove text
                @if(!isset($product))
                if (x > 1) {
                    $(this).parent().parent().remove(); //remove text box
                    x--; //decrement textbox
                }
                @else
                    $(this).parent().parent().remove(); //remove text box
                x--; //decrement textbox
                @endif
                        return false;
            });
            $("#category_id").select2({
                theme: 'bootstrap',
                placeholder:'Select Category'
            });
            $("#product_type").select2({
                theme: 'bootstrap',
                placeholder:'Select Product type'
            });
            $("#status").select2({
                theme: 'bootstrap',
                placeholder:'Select Status'
            });
//            form validation
            $("#product").bootstrapValidator({
                fields: {
                    product_image_file: {
                        validators:{
                            file: {
                                extension: 'jpeg,jpg,png',
                                type: 'image/jpeg,image/png',
                                maxSize: 1000000,
                                message: 'The logo format must be in jpeg, jpg or png and size less than 1MB'
                            }
                        }
                    },
                    product_name: {
                        validators: {
                            notEmpty: {
                                message: 'The product name field is required.'
                            },
                            stringLength: {
                                min: 3,
                                message: 'The product name must be minimum 3 characters.'
                            }
                        }
                    },
                    category_id: {
                        validators: {
                            notEmpty: {
                                message: 'The category field is required.'
                            }
                        }
                    },
                    product_type: {
                        validators: {
                            notEmpty: {
                                message: 'The product type field is required.'
                            }
                        }
                    },
                    status: {
                        validators: {
                            notEmpty: {
                                message: 'The status field is required.'
                            }
                        }
                    },
                    quantity_on_hand: {
                        validators: {
                            notEmpty: {
                                message: 'The quantity on hand field is required.'
                            }
                        }
                    },
                    quantity_available: {
                        validators: {
                            notEmpty: {
                                message: 'The quantity available field is required.'
                            }
                        }
                    },
                    sale_price: {
                        validators: {
                            notEmpty: {
                                message: 'The sale price field is required.'
                            },
                            regexp: {
                                regexp: /^\d{1,6}(\.\d{1,2})?$/,
                                message: 'Sale price contains digits only.'
                            }
                        }
                    }
                }
            });
        });
    </script>
@endsection
