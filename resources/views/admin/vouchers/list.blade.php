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
                        <td class="col-md-2">Start Date</td>
                        <td class="col-md-2">Expiry Date</td>
                        <td class="col-md-2">Scope Type</td>
                        <td class="col-md-1">Status</td>
                        <td class="col-md-1">Channel</td>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($vouchers as $voucher)
                    <tr class='clickable-row' data-href="{{ route('admin.vouchers.edit', $voucher->id) }}">
                        <td>{{ $voucher->amount }}</td>
                        <td>{{ $voucher->amount_type }}</td>
                        <td>{{ $voucher->start_date }}</td>
                        <td>{{ $voucher->expiry_date }}</td>
                        <td>{{ $voucher->scope_type }}</td>
                        <td>@include('layouts.status', ['status' => $voucher->status])</td>
                        <td>{{ $voucher->channel_name }}</td>
                        
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
