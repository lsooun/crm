<!-- Scripts -->
@if(isset($type))
<script type="text/javascript">
        var oTable;
        $(document).ready(function () {
		oTable = $('#data').DataTable({
		@if(config('app.locale') == 'zh')
		"language": {"url": "/zh.json"},
		@endif
                "processing": true,
                "serverSide": true,
                "order": [],
                "ajax": "{{ url($type) }}" + ((typeof $('#data').attr('data-id') != "undefined") ? "/" + $('#id').val() + "/" + $('#data').attr('data-id') : "/data")
            });
            $('div.dataTables_length select').select2({
                theme:"bootstrap"
            });
        });
    </script>
@endif
