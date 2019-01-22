<div class="col-md-3 col-sm-6">
    <div class="card" style="width: 20rem;">
        <a href="{{ route('admin.channels.edit', $channel->id) }}">
            <img class="card-img-top img-responsive" src="{{ asset("storage/$channel->cover") }}" alt="{{$channel->name}}" height="100px"  width="200px">
        </a>
        <div class="card-body">
            <h4 class="card-title">{{strtoupper($channel->name)}}</h4>
            <p class="card-text">{{$channel->description}}</p>
            <a href="{{ route('admin.channels.edit', $channel->id) }}" class="btn btn-primary">Edit</a>
            <a href="{{ route('admin.channel-prices.index', $channel->name) }}">Products</a> | 
            <a href="{{ route('admin.vouchers.getByChannel', $channel->name) }}">Vouchers</a>
        </div>
    </div>
</div>