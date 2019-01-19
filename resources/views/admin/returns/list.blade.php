
@extends('layouts.admin.app')

@section('content')
    <!-- Main content -->
    <section class="content">
    @include('layouts.errors-and-messages')
    <!-- Default box -->
        @if($returns)
            <div class="box">
                <div class="box-body">
                    <h2>Returns</h2>
                    @include('layouts.search', ['route' => route('admin.returns.index')])
                    <table class="table">
                        <thead>
                            <tr>
                                <td class="col-md-1">Order Id</td>
                                <td class="col-md-1">Date</td>
                                <td class="col-md-2">Item Condition</td>
                                <td class="col-md-1">Resolution</td>
                                <td class="col-md-3">Actions</td>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($returns as $return)
                            <tr>
                                <td>{{ $return->order_id }}</td>
                                <td>{{ $return->created_at }}</td>
                                <td>{{ $return->item_condition }}</td>
                                <td>{{ $return->resolution }}</td>
                                <td>
                                    <form action="{{ route('admin.returns.destroy', $return->id) }}" method="post" class="form-horizontal">
                                        {{ csrf_field() }}
                                        <input type="hidden" name="_method" value="delete">
                                        <div class="btn-group">
                                            <a href="{{ route('admin.returns.edit', $return->id) }}" class="btn btn-primary btn-sm"><i class="fa fa-edit"></i> Edit</a>
                                            <button onclick="return confirm('Are you sure?')" type="submit" class="btn btn-danger btn-sm"><i class="fa fa-times"></i> Delete</button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    @if($returns instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
                        <div class="row">
                            <div class="col-md-12">
                                <div class="pull-left">{{ $returns->links() }}</div>
                            </div>
                        </div>
                    @endif
                </div>
                <!-- /.box-body -->
            </div>
            <!-- /.box -->
        @else
            <div class="box">
                <div class="box-body"><p class="alert alert-warning">No returns found.</p></div>
            </div>
        @endif
    </section>
    <!-- /.content -->
@endsection
