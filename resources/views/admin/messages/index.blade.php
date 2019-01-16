@section('content')
    @include('admin.messages.partials.flash')

    @each('admin.messages.partials.thread', $threads, 'thread', 'messenger.partials.no-threads')
@stop
