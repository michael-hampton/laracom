<div class="form-group">
    <label for="stores">Channels to Assign</label>
    <select class="form-control form-control-sm" name="channelsWithoutEmployee[]" id="channels" multiple>
      @foreach($channelsWithoutEmployee as $channel)
          <option id="channel{{ $channel->id }}" value="{{ $channel->id }}">{{$channel->name}}</option>
      @endforeach
    </select>
</div>