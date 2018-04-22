<!DOCTYPE html>
<html>
<head>
    @include('layouts._meta')
    @include('layouts._assets')

    @yield('styles')
</head>
<body>
<div id="app">
    <header class="header">
        <nav class="navbar navbar-static-top" role="navigation">
            <a href="{{ url('/') }}" class="logo">
                <img src="{{ asset('uploads/site/'.Settings::get('site_logo')) }}"
                     alt="{{ Settings::get('site_name') }}" class="img-responsive img_logo">

            </a>

            <div>
                <a href="#" class="navbar-btn sidebar-toggle" data-toggle="offcanvas" role="button"> <i
                            class="fa fa-fw fa-navicon"></i>
                </a>
            </div>
            <div class="navbar-right">
                <ul class="nav navbar-nav">
                    @include("left_menu._header-right")
                </ul>
            </div>
        </nav>
    </header>
    <div class="wrapper row-offcanvas row-offcanvas-left">
        <!-- Left side column. contains the logo and sidebar -->
        <aside class="left-side sidebar-offcanvas">
            <!-- sidebar: style can be found in sidebar-->
            <section class="sidebar">
                <div id="menu" role="navigation">

                    <!-- / .navigation -->
                    @if(Sentinel::inRole('admin') || Sentinel::inRole('staff'))
                        @include('left_menu._user')
                    @elseif(Sentinel::inRole('customer'))
                        @include('left_menu._customer')
                    @endif
                </div>
                <!-- menu -->
            </section>
            <!-- /.sidebar -->
        </aside>
        <aside class="right-side right-padding">
            <div class="right-content">
                @if(isset($title))
                <section class="box-shadow">
                    <h1>{{ $title or '&nbsp;' }}</h1>
                </section>
                @endif

                <!-- Notifications -->

                <!-- Content -->
                <div class="right_cont">
                    @yield('content')
                </div>
                <!-- /.content -->
            </div>
        </aside>
        <!-- /.right-side -->
    </div>
    <!-- /.right-side -->
    <!-- ./wrapper -->
</div>
<!-- global js -->
@include('layouts._assets_footer')
<script>
  $(document).ready(function () {
    $('.date').datetimepicker({
      @if(config('app.locale') == 'zh')
      locale:'zh-cn',
      @endif
      format: '{{ isset($jquery_date)?$jquery_date:"MMMM D,GGGG" }}',
      useCurrent: false,
      icons: {
        time: "fa fa-clock-o",
        date: "fa fa-calendar",
        up: "fa fa-arrow-up",
        down: "fa fa-arrow-down"
      }
    });
    $('.datetime').datetimepicker({
      @if(config('app.locale') == 'zh')
      locale:'zh-cn',
      @endif
      format: '{{ isset($jquery_date_time)?$jquery_date_time:"MMMM D,GGGG H:mm" }}',
      useCurrent: false,
      icons: {
        time: "fa fa-clock-o",
        date: "fa fa-calendar",
        up: "fa fa-arrow-up",
        down: "fa fa-arrow-down"
      }
    });

  })
</script>
@yield('scripts')
</body>
</html>