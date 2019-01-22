
<!-- Main content -->
<section class="content">
    @include('layouts.errors-and-messages')
    <div class="box">
        <form id="VoucherCodeForm" action="{{ route('admin.voucher-codes.store') }}" method="post" class="form" enctype="multipart/form-data">
            <div class="box-body">
                {{ csrf_field() }}
                <input type="hidden" id="voucher_id" name="voucher_id" value="{{ $voucher_id }}"
                       <div class="form-group">
                    <label for="alias">Use Count<span class="text-danger">*</span></label>
                    <input type="text" name="use_count" id="use_count" placeholder="Use Count" class="form-control" value="{{ old('use_count') }}">
                </div>
                <div class="form-group">
                    <label for="status">Status </label>
                    <select name="status" id="status" class="form-control">
                        <option value="0">Disable</option>
                        <option value="1">Enable</option>
                    </select>
                </div>
            </div>
            
        </form>
    </div>
    <!-- /.box -->

</section>