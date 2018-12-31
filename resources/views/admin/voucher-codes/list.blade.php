@extends('layouts.admin.app')

@section('content')
<!-- Main content -->
<section class="content">
    @include('layouts.errors-and-messages')
    <!-- Default box -->
    @if($voucherCodes)
    <div class="box">
        <div class="box-body">
            <h2>Voucher Codes</h2>
            @include('layouts.search', ['route' => route('admin.voucher-codes.index')])
            <table class="table">
                <thead>
                    <tr>
                        <td class="col-md-2">Use Count</td>
                        <td class="col-md-1">Voucher Code</td>
                        <td class="col-md-1">Status</td>
                        <td class="col-md-3">Actions</td>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($voucherCodes as $voucherCode)
                    <tr>
                        <td>{{ $voucherCode->use_count }}</td>
                        <td>{{ $voucherCode->voucher_code }}</td>
                        <td>@include('layouts.status', ['status' => $voucherCode->status])</td>
                        <td>
                            <form action="{{ route('admin.voucher-codes.destroy', $voucherCode->id) }}" method="post" class="form-horizontal">
                                {{ csrf_field() }}
                                <input type="hidden" name="_method" value="delete">
                                <div class="btn-group">
                                    <a href="{{ route('admin.voucher-codes.edit', $voucherCode->id) }}" class="btn btn-primary btn-sm"><i class="fa fa-edit"></i> Edit</a>
                                    <button onclick="return confirm('Are you sure?')" type="submit" class="btn btn-danger btn-sm"><i class="fa fa-times"></i> Delete</button>
                                </div>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @if($voucherCodes instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
            <div class="row">
                <div class="col-md-12">
                    <div class="pull-left">{{ $voucherCodes->links() }}</div>
                </div>
            </div>
            @endif
        </div>
        <!-- /.box-body -->
    </div>
    <!-- /.box -->
    @else
    <div class="box">
        <div class="box-body"><p class="alert alert-warning">No voucher codes found.</p></div>
    </div>
    @endif
</section>
<!-- /.content -->
@endsection