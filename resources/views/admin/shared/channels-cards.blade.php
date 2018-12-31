<div class="col-md-3 col-sm-6">
    <div class="card" style="width: 20rem;">
        <a href="{{ route('admin.channels.show', $channel->id) }}">
            <img class="card-img-top img-responsive" src="{{ asset("storage/$channel->cover") }}" alt="{{$channel->name}}" height="100px"  width="200px">
        </a>
        <div class="card-body">
            <h4 class="card-title">{{strtoupper($channel->name)}}</h4>
            <p class="card-text">{{$channel->description}}</p>
            <a href="{{ route('admin.channels.edit', $channel->id) }}" class="btn btn-primary">Edit</a>
        </div>
    </div>
</div>