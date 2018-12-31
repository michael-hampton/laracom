@extends('layouts.admin.app')

@section('content')
<!-- Main content -->
<section class="content">
    @include('layouts.errors-and-messages')
    <!-- Default box -->
    @if($vouchers)
    <div class="box">
        <div class="box-body">
            <h2>Vouchers</h2>
            @include('layouts.search', ['route' => route('admin.vouchers.index')])
            <table class="table">
                <thead>
                    <tr>
                        <td class="col-md-2">Amount</td>
                        <td class="col-md-1">Amount Type</td>
                        <td class="col-md-2">Expiry Date</td>
                        <td class="col-md-1">Status</td>
                         <td class="col-md-1">Channel</td>
                        <td class="col-md-1">View Codes</td>
                        <td class="col-md-3">Actions</td>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($vouchers as $voucher)
                    <tr>
                        <td>{{ $voucher->amount }}</td>
                        <td>{{ $voucher->amount_type }}</td>
                        <td>{{ $voucher->expiry_date }}</td>
                        <td>@include('layouts.status', ['status' => $voucher->status])</td>
                        <td>{{ $voucher->channel }}</td>
                        <td>
                            <form action="{{ route('admin.vouchers.destroy', $voucher->id) }}" method="post" class="form-horizontal">
                                {{ csrf_field() }}
                                <input type="hidden" name="_method" value="delete">
                                <div class="btn-group">
                                    <a href="{{ route('admin.vouchers.edit', $voucher->id) }}" class="btn btn-primary btn-sm"><i class="fa fa-edit"></i> Edit</a>
                                    <button onclick="return confirm('Are you sure?')" type="submit" class="btn btn-danger btn-sm"><i class="fa fa-times"></i> Delete</button>
                                </div>
                            </form>
                        </td>
                        <td> 
                            <a href="{{ route('admin.voucher-codes.batch', $voucher->id) }}" class="btn btn-default btn-sm">Show Codes</a>
                            <a href="{{ route('admin.voucher-codes.add', $voucher->id) }}" class="btn btn-default btn-sm">Add Codes</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @if($vouchers instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
            <div class="row">
                <div class="col-md-12">
                    <div class="pull-left">{{ $vouchers->links() }}</div>
                </div>
            </div>
            @endif
        </div>
        <!-- /.box-body -->
    </div>
    <!-- /.box -->
    @else
    <div class="box">
        <div class="box-body"><p class="alert alert-warning">No vouchers found.</p></div>
    </div>
    @endif
</section>
<!-- /.content -->
@endsection