@extends('layouts.user')

@section('title')
    {{ $title }}
@stop

@section('content')
    <div class="calendar">
        <div class="calendar_box">
            <div id="calendar"></div>
            <div id="fullCalModal" class="modal fade">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span> <span class="sr-only">close</span></button>
                            <h4 id="modalTitle" class="modal-title"></h4>
                        </div>

                        <div id="modalBody" class="modal-body"></div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" data-dismiss="modal">{{trans('table.close')}}</button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    </div>
@stop
@section('scripts')
    <script>
        $(document).ready(function() {
            var date = new Date();
            var d = date.getDate(),
                m = date.getMonth(),
                y = date.getFullYear();
	    $('#calendar').fullCalendar({
                "header": {
                    "left": "prev,next today",
                    "center": "title",
                    "right": "month,agendaWeek,agendaDay"
                },

                "eventLimit": true,
                "firstDay": 1,
                "eventClick": function(event){
                    $('#modalTitle').html(event.title);
                    $('#modalBody').html(event.description);
                    $('#fullCalModal').modal();
                },
                "eventSources": [
                    {
                        url:"{{url('calendar/events')}}",
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        error: function() {
                            alert('there was an error while fetching events!');
                        }
                    }
                ]

		});
	    @if(config('app.locale') == 'zh')
		    $('#calendar').fullCalendar('option', 'locale', 'zh-cn');
	    @endif
        });
    </script>
@stop
