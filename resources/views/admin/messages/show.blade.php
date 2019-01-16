@section('content')
    <div class="col-md-6">
        <h1>{{ $thread->subject }}</h1>
        @each('admin.messages.partials.messages', $thread->messages, 'message')

        @include('admin.messages.partials.form-message')
    </div>
@stop
