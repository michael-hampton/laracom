@extends('layouts.admin.app')

@section('content')
<!-- Main content -->
<section class="content">
    @include('layouts.errors-and-messages')
    <div class="box">
        <form action="{{ route('admin.voucher-codes.update', $voucherCode->id) }}" method="post" class="form" enctype="multipart/form-data">
            <div class="box-body">
                {{ csrf_field() }}
                <input type="hidden" name="_method" value="put">
                <input type="hidden" name="voucher_id" value="{{ $voucherCode->voucher_id  }}">
                
                <div class="form-group">
                    <label for="use_count">Use Count <span class="text-danger">*</span></label>
                    <input type="text" name="use_count" id="use_count" placeholder="Use Count" class="form-control" value="{{ $voucherCode->use_count ?: old('use_count') }}">
                </div>

                <div class="form-group">
                    <label for="amount">Voucher Code </label>
                    <input type="text" name="voucher_code" id="voucher_code" placeholder="VoucherCode" class="form-control" value="{{ $voucherCode->voucher_code ?: old('voucher_code') }}">
                </div>

               
                <div class="form-group">
                    @include('admin.shared.status-select', ['status' => $voucherCode->status])
                </div>
            </div>
            <!-- /.box-body -->
            <div class="box-footer">
                <div class="btn-group">
                    <a href="{{ route('admin.voucher-codes.index') }}" class="btn btn-default">Back</a>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </div>
        </form>
    </div>
    <!-- /.box -->

</section>
<!-- /.content -->
@endsection