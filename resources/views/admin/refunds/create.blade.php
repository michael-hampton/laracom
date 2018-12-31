@extends('layouts.admin.app')

@section('content')
<!-- Main content -->
<section class="content">
    @include('layouts.errors-and-messages')
    <div class="box">
        <form action="{{ route('admin.refunds.store') }}" method="post" class="form" enctype="multipart/form-data">
            <div class="box-body">
                {{ csrf_field() }}

                <input type="hidden" name="order_id" id="order_id"  value="1">
                <input type="hidden" name="status" id="status"  value="1">

                <div class="form-group">
                    <label for="alias">Quantity <span class="text-danger">*</span></label>
                    <input type="text" name="quantity" id="quantity" placeholder="Quantity" class="form-control" value="{{ old('quantity') }}">
                </div>
                <div class="form-group">
                    <label for="address_1">Amount <span class="text-danger">*</span></label>
                    <input type="text" name="amount" id="amount" placeholder="Amount" class="form-control" value="{{ old('amount') }}">
                </div>
                <div class="form-group">
                    <label for="address_2">Date Refunded </label>
                    <input type="text" name="date_refunded" id="date_refunded" placeholder="Date Refunded" class="form-control" value="{{ old('date_refunded') }}">
                </div>

            </div>
            <!-- /.box-body -->
            <div class="box-footer">
                <div class="btn-group">
                    <a href="{{ route('admin.refunds.index') }}" class="btn btn-default">Back</a>
                    <button type="submit" class="btn btn-primary">Create</button>
                </div>
            </div>
        </form>
    </div>
    <!-- /.box -->

</section>
<!-- /.content -->
@endsection
