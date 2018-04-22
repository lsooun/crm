
<!DOCTYPE html>
<html lang="en">
<head>
    @include('layouts._assets')

    @yield('styles')
</head>
<body>
<div class="app" id="app">

    <!-- ############ LAYOUT START-->

    <!-- aside -->
    <div id="aside" class="app-aside modal fade folded md show-text nav-expand">
        <div class="left navside black dk" layout="column">
            <div class="navbar no-radius">
                <a class="navbar-brand">
                    <img src="{{ asset('uploads/site/'.Settings::get('site_logo')) }}" alt=".">
                    <span class="hidden-folded inline">{{ Settings::get('site_name') }}</span>
                </a>
            </div>
            <div flex class="hide-scroll">
                <nav class="scroll nav-active-primary">
                    <div id="menu" role="navigation">
                        @if(Sentinel::inRole('admin') || Sentinel::inRole('staff'))
                            @include('left_menu._user')
                        @elseif(Sentinel::inRole('customer'))
                            @include('left_menu._customer')
                        @endif
                    </div>
                </nav>
            </div>
        </div>
    </div>
    <!-- / aside -->

    <!-- content -->
    <div id="content" class="app-content box-shadow-z3" role="main">
        <div ui-view class="app-body" id="view">
            @include('layouts._errors')

            @yield('content')
        </div>
    </div>
    <!-- / -->

    <!-- ############ LAYOUT END-->

</div>
@include('layouts._assets_footer')
<!-- Scripts -->
@if(isset($type))
    <script type="text/javascript">
        var oTable;
        $(document).ready(function () {
            oTable = $('#data').DataTable({
                "processing": true,
                "serverSide": true,
                "order": [],
                "ajax": "{{ url($type) }}" + ((typeof $('#data').attr('data-id') != "undefined") ? "/" + $('#id').val() + "/" + $('#data').attr('data-id') : "/data"),
            });
        });

        //for validationr
        $('form').formValidation({
            framework: 'bootstrap',
            icon: {
                valid: 'glyphicon glyphicon-ok',
                invalid: 'glyphicon glyphicon-remove',
                validating: 'glyphicon glyphicon-refresh'
            }
        });
    </script>
@endif
<script>
    $(document).ready(function () {
        $('.date').datetimepicker({format: '{{ isset($jquery_date)?$jquery_date:"MMMM D,GGGG" }}',
            icons: {
                time: "fa fa-clock-o",
                date: "fa fa-calendar",
                up: "fa fa-arrow-up",
                down: "fa fa-arrow-down"
            }});
        $('.datetime').datetimepicker({format: '{{ isset($jquery_date_time)?$jquery_date_time:"MMMM D,GGGG H:mm" }}',
            icons: {
                time: "fa fa-clock-o",
                date: "fa fa-calendar",
                up: "fa fa-arrow-up",
                down: "fa fa-arrow-down"
            }});
    })
</script>
@yield('scripts')
</body>
</html>
