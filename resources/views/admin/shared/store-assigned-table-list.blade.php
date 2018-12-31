<div class="form-group">
    <div class="table-responsive">
        <label for="tableChannelsAssigned">Channel Assigned:</label>
        <table class="table">
            <thead>
                <tr>
                    <th>Cover</th>
                    <th>Name</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>

                @foreach($employeeChannels as $channel)
                <tr>
                    <td>
                        @if(isset($channel->cover))
                        <img src="{{ asset("storage/$channel->cover") }}" alt="" class="img-thumbnail" height="100" width="200">
                        @else
                        -
                        @endif
                    </td>
                    <td>{{ $channel->name }}</td>
                    <td>@include('layouts.status', ['status' => $channel->status])</td>
                    <td>
                        <div class="btn-group">
                            <a href="{{ route('admin.channels.edit', $channel->id) }}" class="btn btn-primary btn-sm"><i class="fa fa-edit"></i> Edit</a>
                            <a href="{{ route('admin.employee.profile.detachchannel', [$employee->id, $channel->id]) }}" class="btn btn-warning btn-sm"
                               onclick="return confirm('Are you sure you want to remove this channel?)">
                                <i class="fa fa-chain-broken"></i> Remove</a>
                        </div
                    </td>

                </tr>
                @endforeach

            </tbody>
        </table>
    </div>
</div>