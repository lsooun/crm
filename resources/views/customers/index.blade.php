@extends('layouts.user')
@section('title')
    {{trans('dashboard.dashboard')}}
@stop

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/c3.min.css') }}">
@stop
@section('content')
    <div class="row">
        <div class="col-lg-6">
            <div class="box1">
                <h4>{{trans('dashboard.invoices_my_month')}}</h4>
                <hr>
                <div id="invoice1"></div>
            </div>
        </div>
        {{--<div class="col-lg-6">--}}
            {{--<div class="box1">--}}
                {{--<h4>{{trans('dashboard.contracts_number')}}</h4>--}}
                {{--<hr>--}}
                {{--<div id="invoice2"></div>--}}
            {{--</div>--}}
        {{--</div>--}}
        <div class="col-lg-6">
            <div class="box1">
                <h4>{{trans('dashboard.opportunities')}}</h4>
                <hr>
                <div id="quotation"></div>
            </div>
        </div>
        <div class="col-lg-6 m-t-20">
            <div class="box1">
                <h4>{{trans('dashboard.sales_progress')}}</h4>
                <hr>
                <div id="salesprgs"></div>
            </div>

        </div>
    </div>

@stop

@section('scripts')
    <script type="text/javascript" src="{{ asset('js/d3.v3.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/d3.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/c3.min.js')}}"></script>
    <script>
        $(function () {
            //c3 customisation
            var chart = c3.generate({
                bindto: '#quotation',
                data: {
                    columns: [
                        @foreach($stages as $item)
                            ['{{$item['title']}}', {{$item['opportunities']}}],
                        @endforeach
                    ],
                    type: 'donut',
                    colors: {
                        @foreach($stages as $item)
                        '{{$item['title']}}': '{{$item['color']}}',
                        @endforeach
                    }
                }
            });
            /*c3 donut chart end*/

            /*c3 invoice chart1*/
            var data = [
                ['No of contracts'],
                @foreach($data as $item)
                    [{{$item['contracts']}}],
                @endforeach
            ];
            var data1 = [
                ['Due by months'],
                @foreach($data as $item)
                    [{{$item['invoices_unpaid']}}],
                @endforeach
            ];

            var data2 = [
                ['Opportunity', 'Leads'],
                @foreach($data as $item)
                    [{{$item['opportunity']}},{{$item['leads']}}],
                @endforeach
            ];

            var chart = c3.generate({
                bindto: '#invoice1',
                data: {
                    rows: data1,
                    type: 'spline'
                },
                color: {
                    pattern: ['#4FC1E9']
                },
                axis: {
                    x: {
                        tick: {
                            format: function (d) {
                                return formatMonthData(d);
                            }
                        }
                    },
                    y: {
                        tick: {
                            format: d3.format("$,")
                            //format: function (d) { return "Custom Format: " + d; }
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

            function formatMonthData(d) {

                @foreach($data as $id => $item)
                if({{$id}}==d)
                {
                    return '{{$item['month']}}'+' '+{{$item['year']}}
                }
                @endforeach
            }

            setTimeout(function () {
                chart.resize();
            }, 2000);

            setTimeout(function () {
                chart.resize();
            }, 4000);

            setTimeout(function () {
                chart.resize();
            }, 6000);
            $("[data-toggle='offcanvas']").click(function (e) {
                chart.resize();
            });
            /*c3 invoice chart1 end*/


            /*c3 invoice chart2*/

            var chart = c3.generate({
                bindto: '#invoice2',
                data: {
                    rows: data,
                    type: 'bar'
                },
                color: {
                    pattern: ['#FD9883']
                },
                axis: {
                    x: {
                        tick: {
                            format: function (d) {
                                return formatMonthData(d);
                            }
                        }
                    },
                },
                legend: {
                    show: true,
                    position: 'bottom'
                },
                padding: {
                    top: 10
                }
            });
            setTimeout(function () {
                chart.resize();
            }, 2000);

            setTimeout(function () {
                chart.resize();
            }, 4000);

            setTimeout(function () {
                chart.resize();
            }, 6000);
            $("[data-toggle='offcanvas']").click(function (e) {
                chart.resize();
            });
            /*c3 invoice chart2 end*/

            $('#cust-dash').slimscroll({
                height: '385px',
                size: '5px',
                color: '#bbb',
                opacity: 1
            });
        /*sales progress*/

        //c3 customisation
            var chart = c3.generate({
                bindto: '#salesprgs',
                data: {
                    rows: data2,
                    type: 'bar'
                },
                color: {
                    pattern: ['#FD9883', '#4FC1E9']
                },
                axis: {
                    x: {
                        tick: {
                            format: function (d) {
                                return formatMonthData(d);
                            }
                        }
                    },
                },
                legend: {
                    show: true,
                    position: 'bottom'
                },
                padding: {
                    top: 10
                }
            });

            setTimeout(function () {
                chart.resize();
            }, 2000);

            setTimeout(function () {
                chart.resize();
            }, 4000);

            setTimeout(function () {
                chart.resize();
            }, 6000);
            $("[data-toggle='offcanvas']").click(function (e) {
                chart.resize();
            });
            /*c3 line chart end*/

        })
    </script>
@stop
