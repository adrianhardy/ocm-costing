<script type="text/javascript">
    window.__flashes = [];
    @foreach (['danger', 'warning', 'success', 'info'] as $type)
    @if(($msg = Session::get('alert-' . $type)) != null)
    window.__flashes.push({
        type: {!! json_encode($type)  !!},
        @if (is_array($msg))
            title: {!! json_encode($msg[1]) !!},
            message: {!! json_encode($msg[0]) !!}
        @else
            message: {!! json_encode($msg) !!}
        @endif
    });
    @endif
    @endforeach
</script>
