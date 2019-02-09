<div class="modal-dialog">
    <div class="modal-content animated bounceInRight">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
            <h4 class="modal-title">Add New Voucher Code</h4>
        </div>

        <div class="modal-body">
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

        <div class="modal-footer">
            <button type="button" class="btn btn-white" data-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary saveCode">Save changes</button>
        </div>
    </div>
</div>

