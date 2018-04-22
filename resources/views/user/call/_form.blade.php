<div class="panel panel-primary">
    <div class="panel-body">
        @if (isset($call))
            {!! Form::model($call, ['url' => $type . '/' . $call->id, 'id'=>'call', 'method' => 'put', 'files'=> true]) !!}
        @else
            {!! Form::open(['url' => $type, 'id'=>'call', 'method' => 'post', 'files'=> true]) !!}
        @endif
        <div class="row">
            <div class="col-md-12">
                @if(Request::is('call/create'))
                    <div class="form-group {{ $errors->has('company_id') ? 'has-error' : '' }}">
                        {!! Form::label('company_id', trans('call.company'), ['class' => 'control-label required']) !!}
                        <div class="controls">
                            {!! Form::select('company_id', $companies, null, ['id'=>'company_name', 'class' => 'form-control select2']) !!}
                            <span class="help-block">{{ $errors->first('company_id', ':message') }}</span>
                        </div>
                    </div>
                @endif
                @if (isset($call))
                    @if(is_int($call->company_id) && $call->company_id>0)
                        <div class="form-group {{ $errors->has('company_id') ? 'has-error' : '' }}">
                            {!! Form::label('company_id', trans('call.company'), ['class' => 'control-label required']) !!}
                            <div class="controls">
                                {!! Form::select('company_id', $companies, null, ['id'=>'company_name', 'class' => 'form-control select2']) !!}
                                <span class="help-block">{{ $errors->first('company_id', ':message') }}</span>
                            </div>
                        </div>
                    @else
                        <div class="form-group {{ $errors->has('company_name') ? 'has-error' : '' }}">
                            {!! Form::label('company_name', trans('call.company'), ['class' => 'control-label']) !!}
                            <div class="controls">
                                {!! Form::text('company_name', $call->company_name, ['class' => 'form-control', 'readonly'=>'readonly']) !!}
                                <span class="help-block">{{ $errors->first('company_name', ':message') }}</span>
                            </div>
                        </div>
                    @endif
                @endif
            </div>
        </div>
       <div class="row">
           <div class="col-md-6">
               <div class="form-group {{ $errors->has('date') ? 'has-error' : '' }}">
                   {!! Form::label('date', trans('call.date'), ['class' => 'control-label required']) !!}
                   <div class="controls">
                       {!! Form::text('date', isset($call) ? null : date('d.m.Y.', strtotime("now")), ['class' => 'form-control date']) !!}
                       <span class="help-block">{{ $errors->first('date', ':message') }}</span>
                   </div>
               </div>
           </div>
           <div class="col-md-6">
               <div class="form-group {{ $errors->has('duration') ? 'has-error' : '' }}">
                   {!! Form::label('duration', trans('call.duration'), ['class' => 'control-label required']) !!}
                   <div class="controls">
                       {!! Form::input('number','duration', null, ['class' => 'form-control', 'min'=>'1']) !!}
                       <span class="help-block">{{ $errors->first('duration', ':message') }}</span>
                   </div>
               </div>
           </div>
       </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group {{ $errors->has('call_summary') ? 'has-error' : '' }}">
                    {!! Form::label('call_summary', trans('call.summary'), ['class' => 'control-label required']) !!}
                    <div class="controls">
                        {!! Form::text('call_summary', null, ['class' => 'form-control']) !!}
                        <span class="help-block">{{ $errors->first('call_summary', ':message') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group {{ $errors->has('resp_staff_id') ? 'has-error' : '' }}">
                    {!! Form::label('resp_staff_id', trans('salesteam.main_staff'), ['class' => 'control-label required']) !!}
                    <div class="controls">
                        {!! Form::select('resp_staff_id', $staffs, null, ['id'=>'resp_staff_id', 'class' => 'form-control select2']) !!}
                        <span class="help-block">{{ $errors->first('resp_staff_id', ':message') }}</span>
                    </div>
                </div>
            </div>
        </div>
        <!-- Form Actions -->
        <div class="form-group">
            <div class="controls">
                <button type="submit" class="btn btn-success"><i class="fa fa-check-square-o"></i> {{trans('table.ok')}}
                </button>
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
        $(document).ready(function(){
            $("#company_name").select2({
                theme:"bootstrap",
                placeholder:"{{ trans('call.company') }}"
            });
            $("#resp_staff_id").select2({
                theme:"bootstrap",
                placeholder:"{{ trans('salesteam.main_staff') }}"
            });
            @if(config('app.locale') == 'zh')
            $("#call").bootstrapValidator({
                fields: {
                    company_id: {
                        validators: {
                            notEmpty: {
                                message: '公司名称必须填写'
                            }
                        }
                    },
                    date: {
                        validators: {
                            notEmpty: {
                                message: '日期必须填写'
                            }
                        }
                    },
                    duration: {
                        validators: {
                            notEmpty: {
                                message: '时长必须填写'
                            }
                        }
                    },
                    call_summary: {
                        validators: {
                            notEmpty: {
                                message: '通话总结必须填写'
                            }
                        }
                    },
                    resp_staff_id: {
                        validators: {
                            notEmpty: {
                                message: '主要员工必须填写'
                            }
                        }
                    }
                }
            });
            @else
            $("#call").bootstrapValidator({
              fields: {
                company_id: {
                  validators: {
                    notEmpty: {
                      message: 'The company field is required.'
                    }
                  }
                },
                date: {
                  validators: {
                    notEmpty: {
                      message: 'The date field is required.'
                    }
                  }
                },
                duration: {
                  validators: {
                    notEmpty: {
                      message: 'The duration field is required.'
                    }
                  }
                },
                call_summary: {
                  validators: {
                    notEmpty: {
                      message: 'The call summary field is required.'
                    }
                  }
                },
                resp_staff_id: {
                  validators: {
                    notEmpty: {
                      message: 'The main staff field is required.'
                    }
                  }
                }
              }
            });
            @endif
            $("#date").on("dp.change",function(){
                $('#call').bootstrapValidator('revalidateField', 'date');
            })
        });
    </script>
    @endsection