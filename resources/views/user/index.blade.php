@extends('layouts.user')
@section('title')
    {{trans('dashboard.dashboard')}}
@stop
@section('styles')
    <link rel="stylesheet" href="{{ asset('css/jquery-jvectormap.css') }}">
    <link rel="stylesheet" href="{{ asset('css/c3.min.css') }}">
@stop
@section('content')
    <div class="row mar-20">

        <div class="col-md-4 col-xs-12 col-sm-6">
            <div class="box-dash">
                <div class="cnts ">
                    <div class="row">

                        <div class="col-md-6">
                            <i class="material-icons md-36 mar-top text-left  text-warning">layers</i>
                        </div>
                        <div class="col-md-6">

                            <div class="pull-right">
                                <div id="countno2"></div>
                                <p class=" nopadmar">{{trans('left_menu.products')}}</p>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-xs-12 col-sm-6">
            <div class="cnts">
                <div class="row">

                    <div class="col-md-6">
                        <i class="material-icons md-36 mar-top text-left text-danger">chrome_reader_mode</i>
                    </div>
                    <div class="col-md-6">
                        <div class="pull-right">
                            <div id="countno3"></div>
                            <p class="nopadmar">{{trans('left_menu.opportunities')}}</p>

                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-xs-12 col-sm-6">
            <div class="cnts">
                <div class="row">

                    <div class="col-md-6">
                        <i class="material-icons md-36 mar-top text-left text-info">flag</i>
                    </div>
                    <div class="col-md-6">
                        <div class="pull-right">
                            <div id="countno4"></div>
                            <p class=" nopadmar">{{trans('left_menu.companies')}}</p>

                        </div>
                    </div>


                </div>
            </div>
        </div>
    </div>
    <div class="row mar-20">
        <div class="col-lg-8">
            <div class="box1 opp-led">
                <h4>{{trans('dashboard.opportunities_leads')}}</h4>
                <div id='chart_opp_lead'></div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="box1 opport">
                <h4>{{trans('dashboard.opportunities')}}</h4>
                <div id="sales"></div>
            </div>

        </div>
    </div>
    <div class="row">
        <div class="col-md-12 col-lg-6">
            <div class="box1 map">
                <h4>{{trans('dashboard.companies_map')}}</h4>
                <div class="world"></div>
            </div>
        </div>
        <div class="col-md-12 col-lg-6">
            <meta name="_token" content="{{ csrf_token() }}">
            <div class="panel panel-success succ-mar">
                <div class="panel-heading">
                    <h4 class="panel-title">
                        <i class="livicon" data-name="inbox" data-size="18" data-color="white" data-hc="white"
                           data-l="true"></i>
                        我的任务
                    </h4>
                </div>
                <div class="panel-body task-body1">
                    <div class="row list_of_items">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <p></p>
@stop

@section('scripts')
    <script type="text/javascript" src="{{ asset('js/jquery-jvectormap-1.2.2.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/jquery-jvectormap-world-mill-en.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/d3.v3.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/d3.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/c3.min.js')}}"></script>
    <script type="text/javascript" src="{{ asset('js/countUp.min.js') }}"></script>
    <script src="{{ asset('js/jquery.slimscroll.js') }}"></script>
    <script src="{{ asset('js/todolist.js') }}"></script>
    <script>

        /*c3 line chart*/
        $(function () {

            var data_opp_lead = [
                ['{{trans("dashboard.opportunities")}}', '{{trans("dashboard.leads")}}'],
                    @foreach($opportunity_leads as $item)
                [{{$item['opportunity']}}, {{$item['leads']}}],
                @endforeach
            ];

//c3 customisation
            var chart_opp_lead = c3.generate({
                bindto: '#chart_opp_lead',
                data: {
                    rows: data_opp_lead,
                    type: 'area-spline'
                },
                color: {
                    pattern: ['#FD9883', '#4FC1E9']
                },
                axis: {
                    x: {
                        tick: {
                            format: function (d) {
                                return formatMonth(d);
                            }
                        }
                    }
                },
                legend: {
                    show: true,
                    position: 'bottom'
                },
                padding: {
                    top: 10
                }
            });

            function formatMonth(d) {

                @foreach($opportunity_leads as $id => $item)
                if ('{{$id}}' == d) {
                    return '{{$item['month']}}' + ' ' + '{{$item['year']}}'
                }
                @endforeach
            }

            setTimeout(function () {
                chart_opp_lead.resize();
            }, 2000);

            setTimeout(function () {
                chart_opp_lead.resize();
            }, 4000);

            setTimeout(function () {
                chart_opp_lead.resize();
            }, 6000);
            $("[data-toggle='offcanvas']").click(function (e) {
                chart_opp_lead.resize();
            });
            /*c3 line chart end*/

            /*c3 pie chart*/
            var chart = c3.generate({
                bindto: '#sales',
                data: {
                    columns: [
                        ['{{trans("dashboard.opportunity_new")}}', {{$opportunity_new}}],
                        ['{{trans("dashboard.opportunity_qualification")}}', {{$opportunity_qualification}}],
                        ['{{trans("dashboard.opportunity_proposition")}}', {{$opportunity_proposition}}],
                        ['{{trans("dashboard.opportunity_negotiation")}}', {{$opportunity_negotiation}}],
                        ['{{trans("dashboard.opportunity_won")}}', {{$opportunity_won}}],
                        ['{{trans("dashboard.opportunity_loss")}}', {{$opportunity_loss}}]
                    ],
                    type: 'pie',
                    colors: {
                        '{{trans("dashboard.opportunity_new")}}': '#4fc1e9',
                        '{{trans("dashboard.opportunity_qualification")}}': '#a0d468',
                        '{{trans("dashboard.opportunity_proposition")}}': '#37bc9b',
                        '{{trans("dashboard.opportunity_negotiation")}}': '#ffcc66',
                        '{{trans("dashboard.opportunity_won")}}': '#fd9883',
                        '{{trans("dashboard.opportunity_loss")}}': '#c2185b'
                    },
                    labels: true
                }
            });
            $(".sidebar-toggle").on("click",function () {
                setTimeout(function () {
                    chart.resize();
                },200)
            });
            /*c3 pie chart end*/
            // c3 chart end


            /*dashboard countup*/
            var useOnComplete = false,
                useEasing = false,
                useGrouping = false,
                options = {
                    useEasing: useEasing, // toggle easing
                    useGrouping: useGrouping, // 1,000,000 vs 1000000
                    separator: ',', // character to use as a separator
                    decimal: '.' // character to use as a decimal
                };

                    {{--var demo = new CountUp("countno1", 0, "{{$contracts}}", 0, 3, options);--}}
                    {{--demo.start();--}}
            var demo = new CountUp("countno2", 0, "{{$products}}", 0, 3, options);
            demo.start();
            var demo = new CountUp("countno3", 0, "{{$opportunities}}", 0, 3, options);
            demo.start();
            var demo = new CountUp("countno4", 0, "{{$customers}}", 0, 3, options);
            demo.start();

            /*countup end*/

            var world= $('.world').vectorMap(
                {
                    map: 'world_mill_en',
                    markers: [
                            @foreach($customers_world as $item)
                        {
                            latLng: [{{$item['latitude']}}, {{$item['longitude']}}], name: '{{$item['city']}}'
                        },
                        @endforeach
                    ],
                    normalizeFunction: 'polynomial',
                    backgroundColor: 'transparent',
                    regionsSelectable: true,
                    markersSelectable: true,
                    regionStyle: {
                        initial: {
                            fill: 'rgba(120,130,140,0.2)'
                        },
                        hover: {
                            fill: '#2283Bf',
                            stroke: '#fff'
                        }
                    },
                    markerStyle: {
                        initial: {
                            fill: '#A0D468',
                            stroke: '#fff',
                            r: 10
                        },
                        hover: {
                            fill: '#0cc2aa',
                            stroke: '#fff',
                            r: 15
                        }
                    }
                }
            );
            $(".sidebar-toggle").on("click",function () {
                setTimeout(function () {
                    world.resize();
                },200)
            });
            $('.task-body1').slimscroll({
                height: '363px',
                size: '5px',
                opacity: 0.2
            });


        });
    </script>

@stop