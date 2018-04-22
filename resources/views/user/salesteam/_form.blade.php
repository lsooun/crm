<div class="panel panel-primary">
    <div class="panel-body">
        @if (isset($salesteam))
            {!! Form::model($salesteam, ['url' => $type . '/' . $salesteam->id, 'method' => 'put', 'files'=> true, 'id'=>'sales_team']) !!}
        @else
            {!! Form::open(['url' => $type, 'method' => 'post', 'files'=> true, 'id'=>'sales_team']) !!}
        @endif
        <div class="row">
            <div class="col-md-12">
                <div class="form-group required {{ $errors->has('salesteam') ? 'has-error' : '' }}">
                    {!! Form::label('salesteam', trans('salesteam.salesteam'), ['class' => 'control-label required']) !!}
                    <div class="controls">
                        {!! Form::text('salesteam', null, ['class' => 'form-control', 'placeholder'=>trans('salesteam.team_title')]) !!}
                        <span class="help-block">{{ $errors->first('salesteam', ':message') }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group required {{ $errors->has('team_leader') ? 'has-error' : '' }}">
                    {!! Form::label('team_leader', trans('salesteam.team_leader'), ['class' => 'control-label required']) !!}
                    <div class="controls">
                        {!! Form::select('team_leader', $staffs, null, ['id'=>'team_leader', 'class' => 'form-control']) !!}
                        <span class="help-block">{{ $errors->first('team_leader', ':message') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group required {{ $errors->has('team_members') ? 'has-error' : '' }}">
                    {!! Form::label('team_members', trans('salesteam.staff_members'), ['class' => 'control-label required']) !!}
                    <div class="controls">
                        {!! Form::select('team_members[]', $staffs, isset($salesteam_stafs)?$salesteam_stafs:null, ['id'=>'team_members', 'multiple'=>'multiple', 'class' => 'form-control']) !!}
                        <span class="help-block">{{ $errors->first('team_members', ':message') }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group required {{ $errors->has('invoice_target') ? 'has-error' : '' }}">
            {!! Form::label('invoice_target', trans('salesteam.invoice_target'), ['class' => 'control-label required']) !!}
            <div class="controls">
                {!! Form::text('invoice_target', null, ['class' => 'form-control', 'placeholder'=>trans('salesteam.invoice_target')]) !!}
                <span class="help-block">{{ $errors->first('invoice_target', ':message') }}</span>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="form-group required {{ $errors->has('notes') ? 'has-error' : '' }}">
                    {!! Form::label('notes', trans('salesteam.notes'), ['class' => 'control-label']) !!}
                    <div class="controls">
                        {!! Form::textarea('notes', null, ['class' => 'form-control resize_vertical','placeholder'=>trans('salesteam.about_team')]) !!}
                        <span class="help-block">{{ $errors->first('notes', ':message') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <div class="controls">
                        <button type="submit" class="btn btn-success"><i
                                    class="fa fa-check-square-o"></i> {{trans('table.ok')}}
                        </button>
                        <a href="{{ route($type.'.index') }}" class="btn btn-warning"><i
                                    class="fa fa-arrow-left"></i> {{trans('table.back')}}</a>
                    </div>
                </div>
            </div>
        </div>
        <!-- ./ form actions -->

        {{--            {{ $newSales }}--}}

        {!! Form::close() !!}
    </div>
</div>

@section('scripts')
    <script>
      $(document).ready(function () {
        $('.icheck').iCheck({
          checkboxClass: 'icheckbox_minimal-blue',
          radioClass: 'iradio_minimal-blue'
        });

        function MainStaffChange() {
          $("#team_leader").select2({
            placeholder: "{{ trans('salesteam.main_staff') }}",
            theme: 'bootstrap'
          }).on("change", function () {
            var MainStaff = $(this).select2("val");
            var staffMembers = $("#team_members").find("option[value='" + MainStaff + "']").val();
            $("#team_members").find("option").prop('disabled', false);
            $("#team_members").find("option").attr('selected', false);
            $("#team_members").select2({
              placeholder: "{{ trans('salesteam.staff_members') }}",
              theme: 'bootstrap'
            });
            if (MainStaff = staffMembers) {
              $("#team_members").find("option[value='" + MainStaff + "']").prop('disabled', true);
            }
          });
        }

        MainStaffChange();
        $("#team_members").select2({
          placeholder: "{{ trans('salesteam.staff_members') }}",
          theme: 'bootstrap'
        }).find("option:first").attr({
          selected: false
        });
        var MainStaff = $("#team_leader").select2("val");
        var staffMembers = $("#team_members").find("option[value='" + MainStaff + "']").val();
        if (MainStaff = staffMembers) {
          $("#team_members").find("option[value='" + MainStaff + "']").prop('disabled', true);
        }

//            Form validations
        $("#sales_team").bootstrapValidator({
          fields: {
            salesteam: {
              validators: {
                notEmpty: {
                  message: 'The salesteam field is required.'
                },
                stringLength: {
                  min: 3,
                  message: 'The salesteam must be minimum 3 characters.'
                }
              }
            },
            team_leader: {
              validators: {
                notEmpty: {
                  message: 'The main staff field is required.'
                }
              }
            },
            "team_members[]": {
              validators: {
                notEmpty: {
                  message: 'The staff members field is required.'
                }
              }
            },
            invoice_target: {
              validators: {
                notEmpty: {
                  message: 'The invoice target field is required.'
                },
                regexp: {
                  regexp: /^\d{1,11}(\.\d{1,2})?$/,
                  message: 'Invoice target is not valid and contains numbers only.'
                }
              }
            }
          }
        })
      });
    </script>
@stop