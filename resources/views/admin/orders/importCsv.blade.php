<div class="modal-dialog">
        <div class="modal-content animated bounceInRight">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title">Order Import</h4>
            </div>

            <div class="modal-body">
                <form id="importForm" action="/admin/orders/saveImport" method="post"enctype="multipart/form-data">
                    {{ csrf_field() }}
                    <input type="file" id="csv_file" name="csv_file">
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-white" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary SaveImport">Import</button>
            </div>
        </div>
    </div>




<script>
    $('.SaveImport').off();
    $('.SaveImport').on('click', function () {

        var href = $('#importForm').attr('action');
        var formdata = new FormData($('#importForm')[0]);
        $('.saveImport').prop('disabled', true);
        $('.modal-body .alert-danger').remove();
        $('.modal-body .alert-success').remove();

        $.ajax({
            type: "POST",
            url: href,
            data: formdata,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.http_code == 400) {

                    $('.modal-body').prepend("<div class='alert alert-danger'></div>");

                    $.each(response.arrErrors, function (lineId, arrLine) {

                        $.each(arrLine, function (field, message) {

                            $('.modal-body .alert-danger').append("<p> Line Id: " + lineId + " message: " + message + "</p>");
                        });
                    });
                } else {
                    $('.modal-body').prepend("<div class='alert alert-success'>Import was successful</div>");
                    $('#csv_file').replaceWith($('#csv_file').val('').clone(true));
                }

                $('.saveImport').prop('disabled', false);
            }
        });
    });
</script>

